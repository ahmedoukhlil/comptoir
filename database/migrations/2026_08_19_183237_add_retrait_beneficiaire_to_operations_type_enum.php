<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Retrait bénéficiaire : transfert d'argent effectué pour une personne
     * qui n'a pas de compte dans l'application (mandat) — vient s'ajouter
     * à depot/retrait, et impacte le solde de caisse exactement comme un
     * retrait classique.
     *
     * MySQL utilise un vrai type ENUM modifiable par ALTER ; SQLite (tests)
     * n'a pas de type ENUM natif — Laravel le simule avec une contrainte
     * CHECK, donc rien à migrer côté structure sur ce driver.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE operations MODIFY COLUMN type ENUM('depot', 'retrait', 'retrait_beneficiaire') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE operations MODIFY COLUMN type ENUM('depot', 'retrait') NOT NULL");
        }
    }
};
