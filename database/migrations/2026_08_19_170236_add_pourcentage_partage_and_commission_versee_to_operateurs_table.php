<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operateurs', function (Blueprint $table) {
            // Part du frais fixe de chaque tranche qui revient au point de
            // vente (le reste revient à la banque/opérateur). Configurable
            // par opérateur, jamais codée en dur dans le calcul.
            $table->decimal('pourcentage_partage_point', 5, 2)->default(50)->after('bareme_commission');

            // Certains opérateurs reversent automatiquement la part point
            // de vente sur le solde de caisse dans l'app ; d'autres la
            // paient hors application (l'agent doit alors compter cet
            // argent lui-même, sans impact sur le solde affiché).
            $table->boolean('commission_versee_dans_solde')->default(false)->after('pourcentage_partage_point');
        });
    }

    public function down(): void
    {
        Schema::table('operateurs', function (Blueprint $table) {
            $table->dropColumn(['pourcentage_partage_point', 'commission_versee_dans_solde']);
        });
    }
};
