<?php

namespace App\Notifications;

use App\Enums\PedidoStatus;
use App\Models\Pedido;
use Illuminate\Notifications\Notification;

/**
 * Avisa o jogador quando o dono marca um pedido da Loja como retirado ou
 * cancelado. Só usa o canal "database" (in-app): o mailer do ambiente é
 * "log" (não envia e-mails de verdade) e não há worker de fila garantido,
 * então uma notificação síncrona e persistida é a única forma confiável de
 * o jogador realmente ver o aviso.
 */
class PedidoStatusAlterado extends Notification
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
        $estabelecimento = $this->pedido->dono->nome_estabelecimento ?? $this->pedido->dono->name;

        return match ($this->pedido->status) {
            PedidoStatus::Retirado => [
                'tipo' => 'pedido_retirado',
                'icone' => 'bi-bag-check-fill',
                'titulo' => 'Pedido retirado',
                'mensagem' => "Seu pedido {$this->pedido->numero_retirada} em {$estabelecimento} foi marcado como retirado.",
                'link' => route('perfil').'#pedidos',
                'linkTexto' => 'Ver meus pedidos',
            ],
            PedidoStatus::Cancelado => [
                'tipo' => 'pedido_cancelado',
                'icone' => 'bi-bag-x-fill',
                'titulo' => 'Pedido cancelado',
                'mensagem' => "Seu pedido {$this->pedido->numero_retirada} em {$estabelecimento} foi cancelado pela loja.",
                'link' => route('perfil').'#pedidos',
                'linkTexto' => 'Ver meus pedidos',
            ],
            default => [
                'tipo' => 'pedido_atualizado',
                'icone' => 'bi-bag',
                'titulo' => 'Pedido atualizado',
                'mensagem' => "O status do seu pedido {$this->pedido->numero_retirada} foi atualizado.",
                'link' => route('perfil').'#pedidos',
                'linkTexto' => 'Ver meus pedidos',
            ],
        };
    }
}
