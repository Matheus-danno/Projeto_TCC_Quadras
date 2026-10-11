<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Documentos enviados para análise (identidade, titularidade de
     * recebimento, comprovante de imóvel). Guardados no disco privado
     * "local" — nunca em disco público.
     */
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentavel');
            $table->string('tipo');
            $table->string('caminho');
            $table->string('nome_original');
            $table->unsignedBigInteger('tamanho_bytes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
