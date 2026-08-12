<?php

namespace App\Livewire\Quadras;

use App\Enums\Esporte;
use App\Enums\ReservaStatus;
use App\Models\Quadra;
use App\Models\Reserva;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Listagem extends Component
{
    public string $cidade = '';

    public string $bairro = '';

    public string $esporte = '';

    public ?int $quadraSelecionada = null;

    public string $data = '';

    public string $horaInicio = '';

    public ?string $mensagemSucesso = null;

    /**
     * Horários de início disponíveis para reserva (quadras abrem 07h, fecham 22h).
     *
     * @return list<string>
     */
    public function horariosDisponiveis(): array
    {
        return collect(range(7, 21))
            ->map(fn (int $hora) => sprintf('%02d:00', $hora))
            ->all();
    }

    #[Computed]
    public function quadras(): Collection
    {
        return Quadra::query()
            ->when($this->cidade, fn ($query) => $query->where('cidade', $this->cidade))
            ->when($this->bairro, fn ($query) => $query->where('bairro', $this->bairro))
            ->when($this->esporte, fn ($query) => $query->where('esporte', $this->esporte))
            ->orderBy('nome')
            ->get();
    }

    #[Computed]
    public function cidades(): Collection
    {
        return Quadra::query()->distinct()->orderBy('cidade')->pluck('cidade');
    }

    #[Computed]
    public function bairros(): Collection
    {
        return Quadra::query()->distinct()->orderBy('bairro')->pluck('bairro');
    }

    public function selecionarQuadra(int $quadraId): void
    {
        $this->quadraSelecionada = $quadraId;
        $this->data = '';
        $this->horaInicio = '';
        $this->mensagemSucesso = null;
        $this->resetErrorBag();
    }

    public function cancelarSelecao(): void
    {
        $this->quadraSelecionada = null;
        $this->resetErrorBag();
    }

    public function reservar(): void
    {
        abort_unless(auth()->check(), 403);

        $validated = $this->validate([
            'data' => ['required', 'date', 'after_or_equal:today'],
            'horaInicio' => ['required', 'in:'.implode(',', $this->horariosDisponiveis())],
        ]);

        $quadra = Quadra::findOrFail($this->quadraSelecionada);

        $horaInicio = $validated['horaInicio'].':00';
        $horaFim = date('H:i:s', strtotime($horaInicio.' +1 hour'));

        $conflito = Reserva::query()
            ->where('quadra_id', $quadra->id)
            ->whereDate('data', $validated['data'])
            ->where('status', '!=', ReservaStatus::Cancelada->value)
            ->where('hora_inicio', '<', $horaFim)
            ->where('hora_fim', '>', $horaInicio)
            ->exists();

        if ($conflito) {
            $this->addError('horaInicio', 'Esse horário já está reservado para esta quadra.');

            return;
        }

        Reserva::create([
            'quadra_id' => $quadra->id,
            'user_id' => auth()->id(),
            'data' => $validated['data'],
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim,
            'status' => ReservaStatus::Confirmada,
        ]);

        $this->mensagemSucesso = "Reserva confirmada em {$quadra->nome} para {$validated['data']} às {$validated['horaInicio']}.";
        $this->quadraSelecionada = null;
    }

    public function render()
    {
        return view('livewire.quadras.listagem', [
            'esportes' => Esporte::cases(),
        ]);
    }
}
