<?php

namespace App\Livewire\Salas;

use App\Enums\FormaPagamento;
use App\Models\Sala;
use Livewire\Component;

class Pagamento extends Component
{
    public Sala $sala;

    public string $formaPagamento = 'pix';

    public string $numeroCartao = '';

    public string $nomeCartao = '';

    public string $validade = '';

    public string $cvv = '';

    public ?string $erro = null;

    public function mount(Sala $sala): void
    {
        $this->sala = $sala->load(['quadra', 'criador', 'participantes']);
    }

    public function selecionarFormaPagamento(string $forma): void
    {
        $this->formaPagamento = $forma;
        $this->resetErrorBag();
    }

    protected function rules(): array
    {
        if ($this->formaPagamento !== FormaPagamento::Cartao->value) {
            return [];
        }

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

    public function codigoPix(): string
    {
        return sprintf(
            '00020126580014BR.GOV.BCB.PIX0117alugaquadra%04d520400005303986540%s5802BR5913AlugaQuadra6009SAO PAULO6304ABCD',
            $this->sala->id,
            number_format($this->sala->valorPorPessoa(), 2, '.', '')
        );
    }

    public function confirmarPagamento()
    {
        $this->erro = null;

        if ($this->sala->participantes->contains('id', auth()->id())) {
            $this->erro = 'Você já está nessa sala.';

            return null;
        }

        if ($this->sala->participantes->count() >= $this->sala->max_participantes) {
            $this->erro = 'Essa sala já está cheia.';

            return null;
        }

        if ($this->formaPagamento === FormaPagamento::Cartao->value) {
            $this->validate();
        }

        $this->sala->participantes()->attach(auth()->id(), [
            'forma_pagamento' => $this->formaPagamento,
            'valor_pago' => $this->sala->valorPorPessoa(),
        ]);

        return redirect()->route('salas.confirmacao', $this->sala);
    }

    public function render()
    {
        return view('livewire.salas.pagamento');
    }
}
