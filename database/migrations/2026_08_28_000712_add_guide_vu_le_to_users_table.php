<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('guide_vu_le')->nullable()->after('role');
        });

        // Les comptes qui existent déjà à ce jour ont déjà appris à utiliser
        // l'application par l'usage : on les marque comme ayant vu le guide
        // pour ne pas leur imposer un tutoriel à leur prochaine connexion.
        // Seuls les comptes créés après cette migration verront le guide.
        DB::table('users')->update(['guide_vu_le' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('guide_vu_le');
        });
    }
};
