<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('plan', ['solo', 'reseau', 'entreprise'])->default('solo');
            $table->enum('statut', ['essai', 'actif', 'lecture_seule', 'suspendu'])->default('essai');
            $table->timestamp('essai_expire_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
