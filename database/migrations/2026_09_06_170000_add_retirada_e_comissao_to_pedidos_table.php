<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('dono_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('numero_retirada')->nullable()->unique()->after('dono_id');
            $table->string('lote_compra')->nullable()->index()->after('numero_retirada');
            $table->decimal('comissao_percentual', 5, 2)->nullable()->after('total');
            $table->decimal('comissao_valor', 10, 2)->nullable()->after('comissao_percentual');
        });

        // Pedidos antigos não tinham conceito de retirada presencial nem de
        // comissão por dono; ficam com status "aguardando" e o dono_id do
        // primeiro item, em vez de órfãos.
        DB::table('pedidos')
            ->whereIn('status', ['confirmado', 'pendente'])
            ->update(['status' => 'aguardando']);

        DB::table('pedidos')
            ->whereNull('dono_id')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($pedido) {
                $donoId = DB::table('itens_pedido')
                    ->join('produtos', 'produtos.id', '=', 'itens_pedido.produto_id')
                    ->where('itens_pedido.pedido_id', $pedido->id)
                    ->value('produtos.dono_id');

                if ($donoId) {
                    DB::table('pedidos')->where('id', $pedido->id)->update(['dono_id' => $donoId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dono_id');
            $table->dropColumn(['numero_retirada', 'lote_compra', 'comissao_percentual', 'comissao_valor']);
        });
    }
};
