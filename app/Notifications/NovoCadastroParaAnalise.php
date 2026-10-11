<?php

namespace App\Notifications;

use App\Models\Quadra;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa os administradores que um novo cadastro (ou reenvio) chegou pra análise.
 */
class NovoCadastroParaAnalise extends Notification
{
    public function __construct(private readonly User|Quadra $entidade) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    private function link(): string
    {
        return $this->entidade instanceof Quadra
            ? route('admin.aprovacoes.quadra', $this->entidade)
            : route('admin.aprovacoes.proprietario', $this->entidade);
    }

    private function descricao(): string
    {
        return $this->entidade instanceof Quadra
            ? "A quadra \"{$this->entidade->nome}\" (de {$this->entidade->dono?->name})"
            : "O proprietário {$this->entidade->name}";
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Novo cadastro para análise')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line($this->descricao().' enviou um cadastro para análise.')
            ->action('Analisar agora', $this->link());
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'tipo' => 'novo_cadastro_analise',
            'icone' => 'bi-inbox-fill',
            'titulo' => 'Novo cadastro para análise',
            'mensagem' => $this->descricao().' enviou um cadastro para análise.',
            'link' => $this->link(),
            'linkTexto' => 'Analisar',
        ];
    }
}
