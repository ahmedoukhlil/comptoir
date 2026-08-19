<?php

use App\Models\Operateur;
use App\Models\Operation;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('operateurs', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->boolean('actif')->default(true)->after('est_cash');
        });

        // Duplique chaque opérateur global existant pour chaque tenant, en
        // réattachant les opérations déjà enregistrées vers la copie du bon
        // tenant, pour ne pas casser l'historique existant.
        $operateursGlobaux = Operateur::whereNull('tenant_id')->get();

        Tenant::all()->each(function (Tenant $tenant) use ($operateursGlobaux) {
            foreach ($operateursGlobaux as $global) {
                $copie = Operateur::create([
                    'tenant_id' => $tenant->id,
                    'nom' => $global->nom,
                    'bareme_commission' => $global->bareme_commission,
                    'est_cash' => $global->est_cash,
                    'actif' => true,
                ]);

                Operation::where('operateur_id', $global->id)
                    ->whereHas('point', fn ($q) => $q->where('tenant_id', $tenant->id))
                    ->update(['operateur_id' => $copie->id]);

                DB::table('alimentations')
                    ->where('operateur_id', $global->id)
                    ->whereIn('point_id', DB::table('points')->where('tenant_id', $tenant->id)->pluck('id'))
                    ->update(['operateur_id' => $copie->id]);

                DB::table('transferts')
                    ->where(fn ($q) => $q->where('operateur_source_id', $global->id)->orWhere('operateur_destination_id', $global->id))
                    ->whereIn('point_id', DB::table('points')->where('tenant_id', $tenant->id)->pluck('id'))
                    ->update([
                        'operateur_source_id' => DB::raw("CASE WHEN operateur_source_id = {$global->id} THEN {$copie->id} ELSE operateur_source_id END"),
                        'operateur_destination_id' => DB::raw("CASE WHEN operateur_destination_id = {$global->id} THEN {$copie->id} ELSE operateur_destination_id END"),
                    ]);

                DB::table('cloture_details')
                    ->where('operateur_id', $global->id)
                    ->whereIn('cloture_id', DB::table('clotures')
                        ->whereIn('point_id', DB::table('points')->where('tenant_id', $tenant->id)->pluck('id'))
                        ->pluck('id'))
                    ->update(['operateur_id' => $copie->id]);
            }
        });

        $operateursGlobaux->each->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operateurs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn('actif');
        });
    }
};
