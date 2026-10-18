<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Notifications\Notification;

/**
 * Avisa o dono quando um cliente finaliza um pedido na loja dele, pra ele
 * saber que precisa separar o produto antes da retirada. Só canal
 * "database": ver PedidoStatusAlterado para o porquê.
 */
class NovoPedidoRecebido extends Notification
{
    public function __construct(private readonly Pedido $pedido) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'tipo' => 'novo_pedido',
            'icone' => 'bi-bag-plus-fill',
            'titulo' => 'Novo pedido na loja',
            'mensagem' => "Novo pedido {$this->pedido->numero_retirada} de {$this->pedido->user->name} — R$ ".number_format((float) $this->pedido->total, 2, ',', '.'),
            'link' => route('painel.loja.pedidos'),
            'linkTexto' => 'Ver pedido',
        ];
    }
}
