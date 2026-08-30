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
        Schema::create('quadra_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quadra_id')->constrained()->cascadeOnDelete();
            $table->string('caminho');
            $table->boolean('capa')->default(false);
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quadra_fotos');
    }
};
