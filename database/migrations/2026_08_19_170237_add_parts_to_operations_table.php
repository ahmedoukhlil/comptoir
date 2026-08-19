<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            // commission_calculee reste le frais fixe total de la tranche
            // (déjà en place). On ajoute la répartition explicite pour ne
            // plus jamais traiter le frais total comme un bénéfice brut.
            $table->unsignedBigInteger('commission_part_point')->default(0)->after('commission_calculee');
            $table->unsignedBigInteger('commission_part_banque')->default(0)->after('commission_part_point');
        });

        // Les opérations déjà enregistrées avaient un modèle "1 tranche =
        // pourcentage du montant, entièrement bénéfice agent" : on les
        // considère à 100% part point de vente pour ne pas perdre de
        // bénéfice déjà comptabilisé rétroactivement.
        DB::table('operations')->update([
            'commission_part_point' => DB::raw('commission_calculee'),
        ]);
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropColumn(['commission_part_point', 'commission_part_banque']);
        });
    }
};
