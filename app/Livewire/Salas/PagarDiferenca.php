<?php

namespace App\Livewire\Salas;

use App\Models\Sala;
use Livewire\Component;

class PagarDiferenca extends Component
{
    public Sala $sala;

    public string $metodo = 'pix';

    public function mount(Sala $sala): void
    {
        abort_unless(auth()->id() === $sala->criador_id, 403);
        abort_unless($sala->podeFecharComVagas(), 404);

        $this->sala = $sala;
    }

    /**
     * Código Pix "copia e cola" (simulado, sem gateway de pagamento real).
     */
    public function codigoPix(): string
    {
        $valorCentavos = (int) round($this->sala->diferencaParaFechar() * 100);
        $identificador = str_pad((string) $this->sala->id, 8, '0', STR_PAD_LEFT);
        $hash = strtoupper(substr(md5($this->sala->id.$valorCentavos), 0, 4));

        return "00020126580014BR.GOV.BCB.PIX0136{$identificador}alugaquadra5204000053039865802BR5913AlugaQuadra6009SAO PAULO62070503***6304{$hash}";
    }

    public function confirmarPagamento(): void
    {
        abort_unless($this->sala->podeFecharComVagas(), 404);

        $diferenca = $this->sala->diferencaParaFechar();

        $this->sala->fecharComMenosJogadores();

        session()->flash('sala-criada', 'Sala fechada! Você pagou a diferença de R$ '.number_format($diferenca, 2, ',', '.').'.');

        $this->redirect(route('salas.grupo', $this->sala), navigate: false);
    }

    public function render()
    {
        return view('livewire.salas.pagar-diferenca');
    }
}
