<?php

namespace App\Livewire\Admin\Financeiro;

use App\Enums\ReservaStatus;
use App\Enums\StatusPagamento;
use App\Enums\UserRole;
use App\Models\Repasse;
use App\Models\Reserva;
use App\Models\User;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Repasses extends Component
{
    use InteractsWithComponents;

    public ?int $donoSelecionadoId = null;

    public string $valor = '';

    public string $observacao = '';

    /**
     * Soma, em centavos, do valor que já deveria ter sido gerado para o
     * proprietário (reservas confirmadas e pagas), de todos os tempos.
     */
    private function geradoCentavos(int $donoId): int
    {
        return (int) Reserva::query()
            ->whereHas('quadra', fn ($query) => $query->where('dono_id', $donoId))
            ->where('status', ReservaStatus::Confirmada)
            ->where('status_pagamento', StatusPagamento::Pago)
            ->sum('valor_proprietario_centavos');
    }

    private function repassadoCentavos(int $donoId): int
    {
        return (int) Repasse::query()->where('dono_id', $donoId)->sum('valor_centavos');
    }

    /**
     * @return Collection<int, array{dono: User, gerado: float, repassado: float, pendente: float}>
     */
    #[Computed]
    public function donos(): Collection
    {
        return User::query()
            ->where('role', UserRole::DonoQuadra)
            ->orderBy('name')
            ->get()
            ->map(function (User $dono) {
                $geradoCentavos = $this->geradoCentavos($dono->id);
                $repassadoCentavos = $this->repassadoCentavos($dono->id);

                return [
                    'dono' => $dono,
                    'gerado' => $geradoCentavos / 100.0,
                    'repassado' => $repassadoCentavos / 100.0,
                    'pendente' => ($geradoCentavos - $repassadoCentavos) / 100.0,
                ];
            });
    }

    #[Computed]
    public function ultimosRepasses(): Collection
    {
        return Repasse::query()->with(['dono', 'criadoPor'])->latest()->limit(10)->get();
    }

    public function abrirMarcarPago(int $donoId): void
    {
        $linha = $this->donos->firstWhere('dono.id', $donoId);

        $this->donoSelecionadoId = $donoId;
        $this->valor = $linha ? number_format(max($linha['pendente'], 0), 2, '.', '') : '0.00';
        $this->observacao = '';
        $this->resetErrorBag();

        $this->modal('marcar-repasse')->show();
    }

    public function marcarPago(): void
    {
        $validated = $this->validate([
            'valor' => ['required', 'numeric', 'min:0.01'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ], [], [
            'valor' => __('valor'),
        ]);

        Repasse::create([
            'dono_id' => $this->donoSelecionadoId,
            'valor_centavos' => (int) round(((float) $validated['valor']) * 100),
            'observacao' => $validated['observacao'] ?: null,
            'criado_por_id' => auth()->id(),
        ]);

        $this->modal('marcar-repasse')->close();
        $this->toast(__('Repasse registrado.'), variant: 'success');

        $this->donoSelecionadoId = null;
        unset($this->donos, $this->ultimosRepasses);
    }

    public function render()
    {
        return view('livewire.admin.financeiro.repasses');
    }
}
