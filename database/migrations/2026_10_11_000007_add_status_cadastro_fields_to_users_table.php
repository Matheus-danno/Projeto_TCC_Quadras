<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status_cadastro')->nullable()->after('role');
            $table->string('titular_recebimento_nome')->nullable()->after('chave_pix_recebimento');
            $table->string('titular_recebimento_documento')->nullable()->after('titular_recebimento_nome');
            $table->json('checklist_aprovacao')->nullable()->after('status_cadastro');
        });

        // Quem já existe no banco (donos e jogadores de antes desse fluxo existir)
        // não deve ficar bloqueado: todos nascem aprovados retroativamente.
        DB::table('users')->update(['status_cadastro' => 'aprovado']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('status_cadastro')->nullable(false)->default('aprovado')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status_cadastro', 'titular_recebimento_nome', 'titular_recebimento_documento', 'checklist_aprovacao']);
        });
    }
};
