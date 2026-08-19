<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cloture_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cloture_id')->constrained()->cascadeOnDelete();
            $table->foreignId('operateur_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('solde_theorique');
            $table->unsignedBigInteger('solde_compte');
            $table->bigInteger('ecart');
            $table->timestamps();

            $table->unique(['cloture_id', 'operateur_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloture_details');
    }
};
