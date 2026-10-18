<?php

namespace App\Notifications;

use App\Models\QuadraFoto;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa o dono quando o admin recusa uma foto específica de uma quadra
 * durante a análise do cadastro, explicando o motivo daquela foto em
 * particular (diferente da decisão geral do cadastro, que usa
 * CadastroStatusAlterado).
 */
class FotoRecusada extends Notification
{
    public function __construct(
        private readonly QuadraFoto $foto,
        private readonly string $motivo,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    private function link(): string
    {
        return route('painel.quadras.editar', $this->foto->quadra);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Uma foto da sua quadra foi recusada')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Uma das fotos enviadas para "'.$this->foto->quadra->nome.'" foi recusada durante a análise.')
            ->line('Motivo: '.$this->motivo)
            ->action('Trocar a foto', $this->link())
            ->line('Envie uma nova foto no lugar dela para seguir com a aprovação.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'tipo' => 'foto_recusada',
            'icone' => 'bi-image-fill',
            'titulo' => 'Foto recusada',
            'mensagem' => 'Uma foto de "'.$this->foto->quadra->nome.'" foi recusada. Motivo: '.$this->motivo,
            'link' => $this->link(),
            'linkTexto' => 'Trocar foto',
        ];
    }
}
