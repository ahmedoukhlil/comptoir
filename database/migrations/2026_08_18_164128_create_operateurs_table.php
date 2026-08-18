<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operateurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->json('bareme_commission');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operateurs');
    }
};
