<?php

namespace App\Notifications;

use App\Enums\StatusCadastro;
use App\Models\Quadra;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa o proprietário quando o cadastro dele (ou de uma quadra dele) é
 * aprovado, rejeitado ou recebe pedido de ajustes.
 */
class CadastroStatusAlterado extends Notification
{
    public function __construct(
        private readonly User|Quadra $entidade,
        private readonly StatusCadastro $status,
        private readonly ?string $motivo = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    private function nome(): string
    {
        return $this->entidade instanceof Quadra
            ? "a quadra \"{$this->entidade->nome}\""
            : 'o seu cadastro';
    }

    private function linkStatus(): string
    {
        return route('painel.cadastro.status');
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mensagem = (new MailMessage)->subject($this->assunto());

        $mensagem->greeting('Olá, '.$notifiable->name.'!');

        $mensagem->line(match ($this->status) {
            StatusCadastro::Aprovado => 'Boas notícias: '.$this->nome().' foi aprovado(a) e já pode operar na plataforma.',
            StatusCadastro::AjustesSolicitados => 'Pedimos alguns ajustes em '.$this->nome().' antes de aprovar.',
            StatusCadastro::Rejeitado => 'Infelizmente '.$this->nome().' não foi aprovado(a).',
            default => 'O status de '.$this->nome().' foi atualizado.',
        });

        if ($this->motivo) {
            $mensagem->line('Motivo: '.$this->motivo);
        }

        return $mensagem->action('Ver status do cadastro', $this->linkStatus())
            ->line('Qualquer dúvida, é só responder este e-mail.');
    }

    private function assunto(): string
    {
        return match ($this->status) {
            StatusCadastro::Aprovado => 'Cadastro aprovado no AlugaQuadra',
            StatusCadastro::AjustesSolicitados => 'Ajustes solicitados no seu cadastro',
            StatusCadastro::Rejeitado => 'Cadastro não aprovado',
            default => 'Atualização no seu cadastro',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return match ($this->status) {
            StatusCadastro::Aprovado => [
                'tipo' => 'cadastro_aprovado',
                'icone' => 'bi-check-circle-fill',
                'titulo' => 'Cadastro aprovado',
                'mensagem' => ucfirst($this->nome()).' foi aprovado(a) e já pode operar.',
                'link' => $this->linkStatus(),
                'linkTexto' => 'Ver status',
            ],
            StatusCadastro::AjustesSolicitados => [
                'tipo' => 'cadastro_ajustes',
                'icone' => 'bi-pencil-square',
                'titulo' => 'Ajustes solicitados',
                'mensagem' => 'Pedimos ajustes em '.$this->nome().'. Motivo: '.$this->motivo,
                'link' => $this->linkStatus(),
                'linkTexto' => 'Corrigir e reenviar',
            ],
            StatusCadastro::Rejeitado => [
                'tipo' => 'cadastro_rejeitado',
                'icone' => 'bi-x-circle-fill',
                'titulo' => 'Cadastro não aprovado',
                'mensagem' => ucfirst($this->nome()).' não foi aprovado(a). Motivo: '.$this->motivo,
                'link' => $this->linkStatus(),
                'linkTexto' => 'Ver detalhes',
            ],
            default => [
                'tipo' => 'cadastro_atualizado',
                'icone' => 'bi-info-circle-fill',
                'titulo' => 'Cadastro atualizado',
                'mensagem' => 'O status de '.$this->nome().' mudou para '.$this->status->label().'.',
                'link' => $this->linkStatus(),
                'linkTexto' => 'Ver status',
            ],
        };
    }
}
