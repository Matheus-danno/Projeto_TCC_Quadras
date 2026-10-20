<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Registro do consentimento LGPD: quando o usuário aceitou a Política de
     * Privacidade e os Termos de Uso, e qual versão desses textos (ver
     * config/privacidade.php). Contas anteriores ficam nulas.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('consentimento_em')->nullable()->after('comissao_aceita_em');
            $table->string('consentimento_versao', 20)->nullable()->after('consentimento_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['consentimento_em', 'consentimento_versao']);
        });
    }
};
