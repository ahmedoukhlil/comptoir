<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite émule les colonnes ENUM avec une contrainte CHECK ; la
            // manière portable de l'étendre est de recréer la colonne.
            Schema::table('users', function ($table) {
                $table->string('role_tmp')->default('agent');
            });
            DB::statement('UPDATE users SET role_tmp = role');
            Schema::table('users', function ($table) {
                $table->dropColumn('role');
            });
            Schema::table('users', function ($table) {
                $table->renameColumn('role_tmp', 'role');
            });

            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('agent', 'proprietaire', 'super_admin') DEFAULT 'agent'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('agent', 'proprietaire') DEFAULT 'agent'");
    }
};
