<?php

namespace App\Livewire\Painel;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\FormaPagamento;
use App\Models\ExcecaoData;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Configuracoes extends Component
{
    use InteractsWithComponents, PasswordValidationRules, ProfileValidationRules;

    public string $name = '';

    public string $nomeEstabelecimento = '';

    public string $telefone = '';

    public bool $editandoEstabelecimento = false;

    public bool $diasUteisAberto = true;

    public string $diasUteisInicio = '09:00';

    public string $diasUteisFim = '22:00';

    public bool $sabadoAberto = true;

    public string $sabadoInicio = '09:00';

    public string $sabadoFim = '22:00';

    public bool $domingoAberto = false;

    public string $domingoInicio = '09:00';

    public string $domingoFim = '22:00';

    public bool $aceitaCartao = true;

    public bool $aceitaPix = true;

    public bool $notifDiasUteis = true;

    public bool $notifCancelamento = true;

    public bool $notifMensagensClientes = true;

    public string $pausaMotivo = '';

    public string $pausaAte = '';

    public bool $pausaIndeterminada = false;

    public string $excecaoData = '';

    public string $excecaoDescricao = '';

    public bool $excecaoFechadoDiaTodo = true;

    public string $excecaoHoraAbertura = '';

    public string $excecaoHoraFechamento = '';

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPassword_confirmation = '';

    public bool $twoFactorEnabled = false;

    public bool $requiresConfirmation = false;

    public string $prazoCancelamentoHoras = '5';

    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->nomeEstabelecimento = $user->nome_estabelecimento ?? '';
        $this->telefone = $user->telefone ?? '';

        $horario = $user->horario_funcionamento ?? [];

        $this->diasUteisAberto = $horario['dias_uteis']['aberto'] ?? true;
        $this->diasUteisInicio = $horario['dias_uteis']['inicio'] ?? '09:00';
        $this->diasUteisFim = $horario['dias_uteis']['fim'] ?? '22:00';
        $this->sabadoAberto = $horario['sabado']['aberto'] ?? true;
        $this->sabadoInicio = $horario['sabado']['inicio'] ?? '09:00';
        $this->sabadoFim = $horario['sabado']['fim'] ?? '22:00';
        $this->domingoAberto = $horario['domingo']['aberto'] ?? false;
        $this->domingoInicio = $horario['domingo']['inicio'] ?? '09:00';
        $this->domingoFim = $horario['domingo']['fim'] ?? '22:00';

        $metodos = $user->metodos_pagamento_aceitos ?? [FormaPagamento::Cartao->value, FormaPagamento::Pix->value];
        $this->aceitaCartao = in_array(FormaPagamento::Cartao->value, $metodos, true);
        $this->aceitaPix = in_array(FormaPagamento::Pix->value, $metodos, true);

        $this->notifDiasUteis = $user->notif_dono_dias_uteis;
        $this->notifCancelamento = $user->notif_dono_cancelamento;
        $this->notifMensagensClientes = $user->notif_dono_mensagens_clientes;

        $this->pausaMotivo = $user->pausa_motivo ?? '';
        $this->pausaAte = $user->pausa_ate?->toDateString() ?? '';
        $this->pausaIndeterminada = $user->pausa_indeterminada;

        $this->prazoCancelamentoHoras = (string) $user->prazo_cancelamento_horas;

        if (Features::enabled(Features::twoFactorAuthentication())) {
            if (Fortify::confirmsTwoFactorAuthentication() && is_null($user->two_factor_confirmed_at)) {
                $disableTwoFactorAuthentication($user);
            }

            $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication();
            $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }
    }

    protected function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'nomeEstabelecimento' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:20'],
        ];
    }

    protected function messages(): array
    {
        return [
            'excecaoData.after_or_equal' => 'A data deve ser hoje ou uma data futura.',
            'pausaAte.after_or_equal' => 'A data deve ser hoje ou uma data futura.',
        ];
    }

    public function cnpjFormatado(): string
    {
        $cnpj = auth()->user()->cnpj;

        if (! $cnpj || strlen($cnpj) !== 14) {
            return $cnpj ?? '';
        }

        return substr($cnpj, 0, 2).'.'.substr($cnpj, 2, 3).'.'.substr($cnpj, 5, 3).'/'.substr($cnpj, 8, 4).'-'.substr($cnpj, 12, 2);
    }

    #[Computed]
    public function excecoes(): Collection
    {
        return auth()->user()->excecoesData()->orderBy('data')->get();
    }

    public function editarEstabelecimento(): void
    {
        $this->editandoEstabelecimento = true;
    }

    public function cancelarEdicaoEstabelecimento(): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->nomeEstabelecimento = $user->nome_estabelecimento ?? '';
        $this->telefone = $user->telefone ?? '';

        $this->resetErrorBag();
        $this->editandoEstabelecimento = false;
    }

    public function salvar(): void
    {
        $validated = $this->validate();

        auth()->user()->update([
            'name' => $validated['name'],
            'nome_estabelecimento' => $validated['nomeEstabelecimento'],
            'telefone' => preg_replace('/\D/', '', $validated['telefone']),
        ]);

        $this->editandoEstabelecimento = false;

        $this->toast('Dados atualizados com sucesso.', variant: 'success');
    }

    /**
     * Horário de funcionamento, métodos de pagamento e notificações não têm
     * botão de salvar no Figma: cada alteração é persistida assim que o
     * campo muda (wire:model.live nesses campos).
     */
    public function updated(string $property): void
    {
        if (str_starts_with($property, 'diasUteis') || str_starts_with($property, 'sabado') || str_starts_with($property, 'domingo')) {
            $this->salvarHorarioFuncionamento();
        }

        if (in_array($property, ['aceitaCartao', 'aceitaPix'], true)) {
            $this->salvarMetodosPagamento();
        }

        if (in_array($property, ['notifDiasUteis', 'notifCancelamento', 'notifMensagensClientes'], true)) {
            $this->salvarNotificacoes();
        }
    }

    public function salvarHorarioFuncionamento(): void
    {
        $this->validate([
            'diasUteisInicio' => ['required', 'date_format:H:i'],
            'diasUteisFim' => ['required', 'date_format:H:i', 'after:diasUteisInicio'],
            'sabadoInicio' => ['required', 'date_format:H:i'],
            'sabadoFim' => ['required', 'date_format:H:i', 'after:sabadoInicio'],
            'domingoInicio' => ['required_if:domingoAberto,true', 'date_format:H:i'],
            'domingoFim' => ['required_if:domingoAberto,true', 'date_format:H:i', 'after:domingoInicio'],
        ]);

        auth()->user()->update([
            'horario_funcionamento' => [
                'dias_uteis' => ['aberto' => $this->diasUteisAberto, 'inicio' => $this->diasUteisInicio, 'fim' => $this->diasUteisFim],
                'sabado' => ['aberto' => $this->sabadoAberto, 'inicio' => $this->sabadoInicio, 'fim' => $this->sabadoFim],
                'domingo' => ['aberto' => $this->domingoAberto, 'inicio' => $this->domingoInicio, 'fim' => $this->domingoFim],
            ],
        ]);

        $this->toast('Horário de funcionamento atualizado.', variant: 'success');
    }

    public function salvarMetodosPagamento(): void
    {
        $metodos = array_values(array_filter([
            $this->aceitaCartao ? FormaPagamento::Cartao->value : null,
            $this->aceitaPix ? FormaPagamento::Pix->value : null,
        ]));

        auth()->user()->update(['metodos_pagamento_aceitos' => $metodos]);

        $this->toast('Métodos de pagamento atualizados.', variant: 'success');
    }

    public function salvarNotificacoes(): void
    {
        auth()->user()->update([
            'notif_dono_dias_uteis' => $this->notifDiasUteis,
            'notif_dono_cancelamento' => $this->notifCancelamento,
            'notif_dono_mensagens_clientes' => $this->notifMensagensClientes,
        ]);

        $this->toast('Preferências de notificação atualizadas.', variant: 'success');
    }

    public function abrirModalExcecao(): void
    {
        $this->reset(['excecaoData', 'excecaoDescricao', 'excecaoFechadoDiaTodo', 'excecaoHoraAbertura', 'excecaoHoraFechamento']);
        $this->excecaoFechadoDiaTodo = true;
        $this->resetErrorBag();

        $this->modal('adicionar-excecao')->show();
    }

    public function salvarExcecao(): void
    {
        $validated = $this->validate([
            'excecaoData' => ['required', 'date', 'after_or_equal:today'],
            'excecaoDescricao' => ['required', 'string', 'max:255'],
            'excecaoHoraAbertura' => ['required_if:excecaoFechadoDiaTodo,false', 'nullable', 'date_format:H:i'],
            'excecaoHoraFechamento' => ['required_if:excecaoFechadoDiaTodo,false', 'nullable', 'date_format:H:i', 'after:excecaoHoraAbertura'],
        ]);

        auth()->user()->excecoesData()->create([
            'data' => $validated['excecaoData'],
            'descricao' => $validated['excecaoDescricao'],
            'fechado_dia_todo' => $this->excecaoFechadoDiaTodo,
            'hora_abertura' => $this->excecaoFechadoDiaTodo ? null : $validated['excecaoHoraAbertura'],
            'hora_fechamento' => $this->excecaoFechadoDiaTodo ? null : $validated['excecaoHoraFechamento'],
        ]);

        $this->modal('adicionar-excecao')->close();
        $this->toast('Exceção de data adicionada.', variant: 'success');

        unset($this->excecoes);
    }

    public function removerExcecao(int $excecaoId): void
    {
        $excecao = ExcecaoData::findOrFail($excecaoId);

        abort_unless($excecao->dono_id === auth()->id(), 403);

        $excecao->delete();

        $this->toast('Exceção de data removida.', variant: 'success');

        unset($this->excecoes);
    }

    public function pausarQuadra(): void
    {
        $validated = $this->validate([
            'pausaMotivo' => ['required', 'string', 'max:255'],
            'pausaAte' => ['required_if:pausaIndeterminada,false', 'nullable', 'date', 'after_or_equal:today'],
        ]);

        auth()->user()->update([
            'pausa_ativa' => true,
            'pausa_motivo' => $validated['pausaMotivo'],
            'pausa_ate' => $this->pausaIndeterminada ? null : $validated['pausaAte'],
            'pausa_indeterminada' => $this->pausaIndeterminada,
        ]);

        $this->toast('Quadras pausadas temporariamente.', variant: 'success');
    }

    public function retomarQuadra(): void
    {
        auth()->user()->update([
            'pausa_ativa' => false,
            'pausa_motivo' => null,
            'pausa_ate' => null,
            'pausa_indeterminada' => false,
        ]);

        $this->reset(['pausaMotivo', 'pausaAte', 'pausaIndeterminada']);

        $this->toast('Quadras reativadas.', variant: 'success');
    }

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'currentPassword' => $this->currentPasswordRules(),
                'newPassword' => $this->passwordRules(),
            ], [], [
                'currentPassword' => 'senha atual',
                'newPassword' => 'nova senha',
            ]);
        } catch (ValidationException $e) {
            $this->reset('currentPassword', 'newPassword', 'newPassword_confirmation');

            throw $e;
        }

        auth()->user()->update(['password' => $validated['newPassword']]);

        $this->reset('currentPassword', 'newPassword', 'newPassword_confirmation');

        $this->toast('Senha atualizada com sucesso.', variant: 'success');
    }

    #[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }

    public function disableTwoFactor(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;

        $this->toast('Autenticação de dois fatores desativada.', variant: 'success');
    }

    public function salvarPoliticaCancelamento(): void
    {
        $validated = $this->validate([
            'prazoCancelamentoHoras' => ['required', 'integer', 'min:0', 'max:168'],
        ], [], [
            'prazoCancelamentoHoras' => 'prazo de cancelamento',
        ]);

        auth()->user()->update(['prazo_cancelamento_horas' => $validated['prazoCancelamentoHoras']]);

        $this->toast('Política de cancelamento atualizada.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.painel.configuracoes');
    }
}
