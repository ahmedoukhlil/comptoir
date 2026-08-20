<?php

use App\Models\Operateur;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Le tarif d'un opérateur varie selon le type d'opération (le dépôt est
     * souvent gratuit, le retrait client et le retrait bénéficiaire suivent
     * des grilles distinctes) — un seul bareme_commission ne suffit plus.
     */
    public function up(): void
    {
        Schema::table('operateurs', function (Blueprint $table) {
            $table->json('bareme_depot')->nullable()->after('bareme_commission');
            $table->json('bareme_retrait_client')->nullable()->after('bareme_depot');
            $table->json('bareme_retrait_beneficiaire')->nullable()->after('bareme_retrait_client');
        });

        // Migration des données existantes : l'ancien bareme_commission
        // unique devient le barème du retrait client (celui qui portait
        // jusqu'ici la vraie tarification), dépôt et retrait bénéficiaire
        // démarrent vides (0 frais) en attendant d'être renseignés.
        Operateur::whereNotNull('bareme_commission')->each(function (Operateur $operateur) {
            $operateur->update([
                'bareme_retrait_client' => $operateur->bareme_commission,
                'bareme_depot' => ['tranches' => []],
                'bareme_retrait_beneficiaire' => ['tranches' => []],
            ]);
        });

        Schema::table('operateurs', function (Blueprint $table) {
            $table->dropColumn('bareme_commission');
        });
    }

    public function down(): void
    {
        Schema::table('operateurs', function (Blueprint $table) {
            $table->json('bareme_commission')->nullable();
        });

        Operateur::whereNotNull('bareme_retrait_client')->each(function (Operateur $operateur) {
            $operateur->update(['bareme_commission' => $operateur->bareme_retrait_client]);
        });

        Schema::table('operateurs', function (Blueprint $table) {
            $table->dropColumn(['bareme_depot', 'bareme_retrait_client', 'bareme_retrait_beneficiaire']);
        });
    }
};
