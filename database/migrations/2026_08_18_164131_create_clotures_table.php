<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clotures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('point_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedBigInteger('solde_theorique');
            $table->unsignedBigInteger('solde_compte');
            $table->bigInteger('ecart');
            $table->timestamps();

            $table->unique(['point_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clotures');
    }
};
