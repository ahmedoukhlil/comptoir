<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Passe d'un Operateur par tenant à un Operateur global partagé par
     * tous les tenants. Fusionne les doublons existants (même nom), en
     * gardant la copie la plus ancienne (id le plus petit) comme survivante
     * et en réattachant toutes les références (operations, alimentations,
     * cloture_details, transferts) vers cette copie. Les divergences de
     * barème/pourcentage entre copies fusionnées sont journalisées, jamais
     * silencieusement écrasées.
     */
    public function up(): void
    {
        Schema::table('operateurs', function (Blueprint $table) {
            $table->float('pourcentage_partage_point_depot')->nullable()->after('pourcentage_partage_point');
            $table->float('pourcentage_partage_point_retrait_client')->nullable()->after('pourcentage_partage_point_depot');
            $table->float('pourcentage_partage_point_retrait_beneficiaire')->nullable()->after('pourcentage_partage_point_retrait_client');
        });

        DB::table('operateurs')->update([
            'pourcentage_partage_point_depot' => DB::raw('pourcentage_partage_point'),
            'pourcentage_partage_point_retrait_client' => DB::raw('pourcentage_partage_point'),
            'pourcentage_partage_point_retrait_beneficiaire' => DB::raw('pourcentage_partage_point'),
        ]);

        $groupes = DB::table('operateurs')->orderBy('id')->get()->groupBy('nom');

        foreach ($groupes as $nom => $copies) {
            $survivant = $copies->first();

            DB::table('tenant_operateur')->insert(
                $copies->map(fn ($copie) => [
                    'tenant_id' => $copie->tenant_id,
                    'operateur_id' => $survivant->id,
                    'actif' => $copie->actif,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all()
            );

            $doublons = $copies->skip(1);

            foreach ($doublons as $doublon) {
                if ($doublon->bareme_retrait_client !== $survivant->bareme_retrait_client
                    || $doublon->pourcentage_partage_point !== $survivant->pourcentage_partage_point) {
                    Log::warning("Fusion Operateur '{$nom}' : divergence entre copie tenant {$doublon->tenant_id} (id {$doublon->id}) et copie survivante tenant {$survivant->tenant_id} (id {$survivant->id}) — la copie survivante a été conservée.", [
                        'survivant' => (array) $survivant,
                        'doublon' => (array) $doublon,
                    ]);
                }

                foreach (['operations', 'alimentations', 'cloture_details'] as $table) {
                    DB::table($table)->where('operateur_id', $doublon->id)->update(['operateur_id' => $survivant->id]);
                }

                DB::table('transferts')->where('operateur_source_id', $doublon->id)->update(['operateur_source_id' => $survivant->id]);
                DB::table('transferts')->where('operateur_destination_id', $doublon->id)->update(['operateur_destination_id' => $survivant->id]);

                DB::table('operateurs')->where('id', $doublon->id)->delete();
            }
        }

        Schema::table('operateurs', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'pourcentage_partage_point']);
        });
    }

    public function down(): void
    {
        Schema::table('operateurs', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->float('pourcentage_partage_point')->nullable()->after('commission_versee_dans_solde');
        });

        DB::table('operateurs')->update([
            'pourcentage_partage_point' => DB::raw('pourcentage_partage_point_retrait_client'),
        ]);

        foreach (DB::table('operateurs')->get() as $operateur) {
            $pivot = DB::table('tenant_operateur')->where('operateur_id', $operateur->id)->first();

            if ($pivot) {
                DB::table('operateurs')->where('id', $operateur->id)->update(['tenant_id' => $pivot->tenant_id]);
            }
        }

        Schema::table('operateurs', function (Blueprint $table) {
            $table->dropColumn([
                'pourcentage_partage_point_depot',
                'pourcentage_partage_point_retrait_client',
                'pourcentage_partage_point_retrait_beneficiaire',
            ]);
        });
    }
};
