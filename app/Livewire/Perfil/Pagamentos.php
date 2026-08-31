<?php

namespace App\Livewire\Perfil;

use App\Enums\ReservaStatus;
use App\Models\Cartao;
use App\Models\Reserva;
use App\Models\Sala;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Pagamentos extends Component
{
    public bool $mostrarFormulario = false;

    public string $numeroCartao = '';

    public string $nomeCartao = '';

    public string $validade = '';

    public string $cvv = '';

    public ?string $mensagemSucesso = null;

    #[Computed]
    public function cartoes()
    {
        return Auth::user()->cartoes()
            ->orderByDesc('principal')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Histórico de comprovantes de pagamento: reservas diretas de quadra pagas
     * e participações pagas em salas, unificadas e ordenadas da mais recente.
     */
    #[Computed]
    public function comprovantes()
    {
        $user = Auth::user();

        $reservas = $user->reservas()
            ->with('quadra')
            ->where('status', ReservaStatus::Confirmada)
            ->whereNotNull('metodo_pagamento')
            ->get()
            ->map(fn (Reserva $reserva) => [
                'chave' => 'reserva-'.$reserva->id,
                'data' => $reserva->updated_at,
                'quadra' => $reserva->quadra?->nome ?? '—',
                'referencia' => $reserva->codigoReserva(),
                'forma_pagamento' => $this->rotuloFormaPagamento($reserva->metodo_pagamento),
                'valor' => (float) ($reserva->quadra->valor_hora ?? 0),
            ]);

        $participacoes = $user->salas()
            ->with('quadra')
            ->withPivot(['forma_pagamento', 'valor_pago', 'updated_at'])
            ->wherePivotNotNull('forma_pagamento')
            ->get()
            ->map(fn (Sala $sala) => [
                'chave' => 'sala-'.$sala->id,
                'data' => $sala->pivot->updated_at,
                'quadra' => $sala->quadra?->nome ?? '—',
                'referencia' => 'Sala: '.$sala->nome,
                'forma_pagamento' => $this->rotuloFormaPagamento($sala->pivot->forma_pagamento),
                'valor' => (float) ($sala->pivot->valor_pago ?? 0),
            ]);

        return $reservas->concat($participacoes)
            ->sortByDesc('data')
            ->values();
    }

    private function rotuloFormaPagamento(?string $forma): string
    {
        return match ($forma) {
            'pix' => 'Pix',
            'cartao' => 'Cartão de Crédito',
            'credito' => 'Créditos AlugaQuadra',
            default => 'Não informado',
        };
    }

    public function abrirFormulario(): void
    {
        $this->mostrarFormulario = true;
        $this->mensagemSucesso = null;
    }

    public function cancelarFormulario(): void
    {
        $this->mostrarFormulario = false;
        $this->reset(['numeroCartao', 'nomeCartao', 'validade', 'cvv']);
        $this->resetErrorBag();
    }

    protected function rules(): array
    {
        return [
            'numeroCartao' => ['required', 'digits_between:13,19'],
            'nomeCartao' => ['required', 'string', 'min:3'],
            'validade' => ['required', 'regex:/^\d{2}\/\d{2}$/'],
            'cvv' => ['required', 'digits_between:3,4'],
        ];
    }

    protected function messages(): array
    {
        return [
            'numeroCartao.required' => 'Informe o número do cartão.',
            'numeroCartao.digits_between' => 'Número do cartão inválido.',
            'nomeCartao.required' => 'Informe o nome impresso no cartão.',
            'nomeCartao.min' => 'Informe o nome impresso no cartão.',
            'validade.required' => 'Informe a validade do cartão.',
            'validade.regex' => 'Use o formato MM/AA.',
            'cvv.required' => 'Informe o CVV.',
            'cvv.digits_between' => 'CVV inválido.',
        ];
    }

    public function adicionarCartao(): void
    {
        $validated = $this->validate();

        $user = Auth::user();

        $user->cartoes()->create([
            'nome_titular' => $validated['nomeCartao'],
            'numero_final' => substr($validated['numeroCartao'], -4),
            'bandeira' => Cartao::identificarBandeira($validated['numeroCartao']),
            'validade' => $validated['validade'],
            'principal' => ! $user->cartoes()->exists(),
        ]);

        $this->reset(['numeroCartao', 'nomeCartao', 'validade', 'cvv']);
        $this->mostrarFormulario = false;
        $this->mensagemSucesso = 'Cartão adicionado com sucesso.';

        unset($this->cartoes);
    }

    public function definirPrincipal(int $cartaoId): void
    {
        $user = Auth::user();
        $cartao = $user->cartoes()->findOrFail($cartaoId);

        $user->cartoes()->where('id', '!=', $cartao->id)->update(['principal' => false]);
        $cartao->update(['principal' => true]);

        $this->mensagemSucesso = 'Cartão principal atualizado.';

        unset($this->cartoes);
    }

    public function removerCartao(int $cartaoId): void
    {
        $user = Auth::user();
        $cartao = $user->cartoes()->findOrFail($cartaoId);
        $eraPrincipal = $cartao->principal;

        $cartao->delete();

        if ($eraPrincipal) {
            $user->cartoes()->orderByDesc('created_at')->first()?->update(['principal' => true]);
        }

        $this->mensagemSucesso = 'Cartão removido.';

        unset($this->cartoes);
    }

    public function render()
    {
        return view('livewire.perfil.pagamentos');
    }
}
