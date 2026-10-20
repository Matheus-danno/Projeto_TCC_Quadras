<?php

namespace App\Services\Privacidade;

use App\Models\Documento;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Reserva;
use App\Models\Sala;
use App\Models\User;

/**
 * Monta a cópia dos dados pessoais de um usuário para o botão "Baixar meus
 * dados" (LGPD, art. 18, II e V — acesso e portabilidade).
 *
 * Fica de fora o que é segredo de autenticação (hash da senha, segredo e
 * códigos de recuperação do 2FA, token de sessão) e o conteúdo dos arquivos
 * de documento — deles só vão os metadados.
 */
class ExportadorDeDadosPessoais
{
    /**
     * @return array<string, mixed>
     */
    public function exportar(User $user): array
    {
        $user->load([
            'endereco',
            'notificacoes',
            'reservas.quadra',
            'pedidos.itens.produto',
            'salasCriadas',
            'salas',
            'cartoes',
            'documentos',
        ]);

        return [
            'gerado_em' => now()->toIso8601String(),
            'plataforma' => config('app.name'),
            'conta' => collect($user->attributesToArray())
                ->except(['id', 'two_factor_confirmed_at'])
                ->all(),
            'endereco' => $user->endereco?->only([
                'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado',
            ]),
            'preferencias_de_notificacao' => $user->notificacoes
                ?->makeHidden(['id', 'user_id'])
                ->attributesToArray(),
            'reservas' => $user->reservas->map(fn (Reserva $reserva) => [
                'codigo' => $reserva->codigoReserva(),
                'quadra' => $reserva->quadra?->nome,
                'data' => $reserva->data?->toDateString(),
                'hora_inicio' => $reserva->hora_inicio,
                'hora_fim' => $reserva->hora_fim,
                'status' => $reserva->status?->value,
                'valor' => $reserva->valor,
                'forma_de_pagamento' => $reserva->metodo_pagamento,
                'criada_em' => $reserva->created_at?->toIso8601String(),
            ])->values()->all(),
            'pedidos_da_loja' => $user->pedidos->map(fn (Pedido $pedido) => [
                'numero_retirada' => $pedido->numero_retirada,
                'status' => $pedido->status?->value,
                'total' => $pedido->total,
                'itens' => $pedido->itens->map(fn (ItemPedido $item) => [
                    'produto' => $item->produto?->nome,
                    'quantidade' => $item->quantidade,
                    'preco_unitario' => $item->preco_unitario,
                ])->values()->all(),
                'criado_em' => $pedido->created_at?->toIso8601String(),
            ])->values()->all(),
            'salas_criadas' => $user->salasCriadas->map(fn (Sala $sala) => $this->resumoSala($sala))->values()->all(),
            'salas_em_que_participa' => $user->salas->map(fn (Sala $sala) => $this->resumoSala($sala))->values()->all(),
            'cartoes_salvos' => $user->cartoes->map->only(['nome_titular', 'bandeira', 'numero_final', 'validade'])->values()->all(),
            'documentos_enviados' => $user->documentos->map(fn (Documento $documento) => [
                'tipo' => $documento->tipo?->value,
                'nome_original' => $documento->nome_original,
                'enviado_em' => $documento->created_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resumoSala(Sala $sala): array
    {
        return [
            'nome' => $sala->nome,
            'esporte' => $sala->esporte?->value,
            'data' => $sala->data?->toDateString(),
            'horario_inicio' => $sala->horario_inicio,
            'horario_fim' => $sala->horario_fim,
        ];
    }
}
