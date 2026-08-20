<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_operateur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('operateur_id')->constrained()->cascadeOnDelete();
            $table->boolean('actif')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'operateur_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_operateur');
    }
};
