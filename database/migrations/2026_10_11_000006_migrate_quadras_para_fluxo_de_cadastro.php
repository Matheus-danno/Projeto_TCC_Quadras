<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Evolui o status de 3 estados (pendente/aprovada/suspensa) da quadra
     * para o fluxo de aprovação de cadastro compartilhado com os donos
     * (pendente/em_analise/ajustes_solicitados/aprovado/rejeitado/suspenso).
     */
    public function up(): void
    {
        Schema::table('quadras', function (Blueprint $table) {
            $table->renameColumn('status_aprovacao', 'status_cadastro');
        });

        DB::table('quadras')->where('status_cadastro', 'aprovada')->update(['status_cadastro' => 'aprovado']);
        DB::table('quadras')->where('status_cadastro', 'suspensa')->update(['status_cadastro' => 'suspenso']);

        Schema::table('quadras', function (Blueprint $table) {
            $table->string('tipo_piso')->nullable()->after('cobertura');
            $table->json('checklist_aprovacao')->nullable()->after('status_cadastro');
        });

        // O default antigo ("aprovada") não existe mais como valor válido do
        // enum. Daqui pra frente, uma quadra nova nasce pendente de análise.
        Schema::table('quadras', function (Blueprint $table) {
            $table->string('status_cadastro')->default('pendente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quadras', function (Blueprint $table) {
            $table->dropColumn(['tipo_piso', 'checklist_aprovacao']);
        });

        DB::table('quadras')->where('status_cadastro', 'aprovado')->update(['status_cadastro' => 'aprovada']);
        DB::table('quadras')->where('status_cadastro', 'suspenso')->update(['status_cadastro' => 'suspensa']);

        Schema::table('quadras', function (Blueprint $table) {
            $table->renameColumn('status_cadastro', 'status_aprovacao');
        });
    }
};
