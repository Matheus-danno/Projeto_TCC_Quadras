<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Histórico de envios/reenvios e decisões do fluxo de aprovação — serve
     * tanto de histórico (pro dono ver o que aconteceu) quanto de log de
     * auditoria (quem decidiu o quê e por quê), tanto para proprietários
     * quanto para quadras.
     */
    public function up(): void
    {
        Schema::create('cadastro_historicos', function (Blueprint $table) {
            $table->id();
            $table->morphs('avaliavel');
            $table->string('evento');
            $table->string('status_anterior')->nullable();
            $table->string('status_novo');
            $table->text('motivo')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cadastro_historicos');
    }
};
