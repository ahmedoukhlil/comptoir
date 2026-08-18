<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alimentations', function (Blueprint $table) {
            $table->foreignId('operateur_id')->nullable()->after('point_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('alimentations', function (Blueprint $table) {
            $table->dropForeign(['operateur_id']);
            $table->dropColumn('operateur_id');
        });
    }
};
