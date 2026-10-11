<?php

namespace App\Services\Aprovacao;

use App\Enums\StatusCadastro;
use App\Enums\UserRole;
use App\Models\CadastroHistorico;
use App\Models\Quadra;
use App\Models\User;
use App\Notifications\CadastroStatusAlterado;
use App\Notifications\NovoCadastroParaAnalise;
use InvalidArgumentException;
use RuntimeException;

/**
 * Centraliza as transições do fluxo de aprovação de cadastros (donos e
 * quadras usam o mesmo enum de status e o mesmo histórico/log de auditoria),
 * pra não duplicar a lógica entre as telas de admin e do painel do dono.
 */
class CadastroAprovacaoService
{
    /**
     * Registra o envio inicial de um cadastro (dono ou quadra) recém-criado:
     * grava o histórico e avisa os administradores. Não muda o status — o
     * registro já nasce "pendente".
     */
    public function registrarEnvio(User|Quadra $entidade): void
    {
        $this->log($entidade, 'enviado', null, $entidade->status_cadastro);

        $this->notificarAdmins($entidade);
    }

    /**
     * O dono corrige e reenvia um cadastro com ajustes solicitados (ou
     * rejeitado), voltando para o início da fila.
     */
    public function reenviar(User|Quadra $entidade): void
    {
        if (! $entidade->status_cadastro->podeReenviar()) {
            throw new RuntimeException('Este cadastro não está em um status que permita reenvio.');
        }

        $statusAnterior = $entidade->status_cadastro;

        $entidade->update(['status_cadastro' => StatusCadastro::Pendente]);

        $this->log($entidade, 'reenviado', $statusAnterior, StatusCadastro::Pendente);

        $this->notificarAdmins($entidade);
    }

    public function iniciarAnalise(User|Quadra $entidade, User $admin): void
    {
        if ($entidade->status_cadastro !== StatusCadastro::Pendente) {
            return;
        }

        $statusAnterior = $entidade->status_cadastro;

        $entidade->update(['status_cadastro' => StatusCadastro::EmAnalise]);

        $this->log($entidade, 'em_analise', $statusAnterior, StatusCadastro::EmAnalise, admin: $admin);
    }

    public function aprovar(User|Quadra $entidade, User $admin): void
    {
        if ($entidade instanceof Quadra && ! $entidade->dono?->cadastroAprovado()) {
            throw new RuntimeException('O proprietário desta quadra ainda não foi aprovado.');
        }

        $statusAnterior = $entidade->status_cadastro;

        $entidade->update(['status_cadastro' => StatusCadastro::Aprovado]);

        $this->log($entidade, 'aprovado', $statusAnterior, StatusCadastro::Aprovado, admin: $admin);

        $this->notificarDono($entidade, StatusCadastro::Aprovado);
    }

    public function rejeitar(User|Quadra $entidade, User $admin, string $motivo): void
    {
        $motivo = trim($motivo);

        if ($motivo === '') {
            throw new InvalidArgumentException('Informe o motivo da rejeição.');
        }

        $statusAnterior = $entidade->status_cadastro;

        $entidade->update(['status_cadastro' => StatusCadastro::Rejeitado]);

        $this->log($entidade, 'rejeitado', $statusAnterior, StatusCadastro::Rejeitado, $motivo, $admin);

        $this->notificarDono($entidade, StatusCadastro::Rejeitado, $motivo);
    }

    public function solicitarAjustes(User|Quadra $entidade, User $admin, string $motivo): void
    {
        $motivo = trim($motivo);

        if ($motivo === '') {
            throw new InvalidArgumentException('Informe o que precisa ser ajustado.');
        }

        $statusAnterior = $entidade->status_cadastro;

        $entidade->update(['status_cadastro' => StatusCadastro::AjustesSolicitados]);

        $this->log($entidade, 'ajustes_solicitados', $statusAnterior, StatusCadastro::AjustesSolicitados, $motivo, $admin);

        $this->notificarDono($entidade, StatusCadastro::AjustesSolicitados, $motivo);
    }

    /**
     * Edição de um campo sensível numa quadra já aprovada: volta pra análise
     * automaticamente, sem decisão manual do admin.
     */
    public function voltarParaAnalisePorEdicaoSensivel(Quadra $quadra): void
    {
        $statusAnterior = $quadra->status_cadastro;

        $quadra->update(['status_cadastro' => StatusCadastro::EmAnalise]);

        $this->log($quadra, 'reenviado', $statusAnterior, StatusCadastro::EmAnalise, 'Dados sensíveis alterados pelo proprietário (endereço/documentos).');

        $this->notificarAdmins($quadra);
    }

    private function log(
        User|Quadra $entidade,
        string $evento,
        ?StatusCadastro $statusAnterior,
        StatusCadastro $statusNovo,
        ?string $motivo = null,
        ?User $admin = null,
    ): void {
        CadastroHistorico::create([
            'avaliavel_type' => $entidade::class,
            'avaliavel_id' => $entidade->id,
            'evento' => $evento,
            'status_anterior' => $statusAnterior?->value,
            'status_novo' => $statusNovo->value,
            'motivo' => $motivo,
            'admin_id' => $admin?->id,
        ]);
    }

    private function notificarDono(User|Quadra $entidade, StatusCadastro $status, ?string $motivo = null): void
    {
        $dono = $entidade instanceof Quadra ? $entidade->dono : $entidade;

        $dono?->notify(new CadastroStatusAlterado($entidade, $status, $motivo));
    }

    private function notificarAdmins(User|Quadra $entidade): void
    {
        User::query()->where('role', UserRole::Admin)->get()
            ->each(fn (User $admin) => $admin->notify(new NovoCadastroParaAnalise($entidade)));
    }
}
