<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->string('numero_piece')->unique();
            $table->foreignId('point_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('operateur_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['depot', 'retrait']);
            $table->unsignedBigInteger('montant');
            $table->unsignedBigInteger('commission_calculee')->default(0);
            $table->string('client_nom')->nullable();
            $table->string('client_telephone');
            $table->text('client_nni')->nullable();
            $table->text('observation')->nullable();
            $table->boolean('synced')->default(true);
            $table->unsignedBigInteger('cloture_id')->nullable();
            $table->timestamps();

            $table->index(['point_id', 'created_at']);
            $table->index('client_telephone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};
