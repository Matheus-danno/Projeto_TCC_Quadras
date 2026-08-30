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

    public string $quadraNome = '';

    public string $cobertura = '';

    public string $busca = '';

    public ?int $quadraSelecionada = null;

    public string $data = '';

    public string $horaInicio = '';

    public ?float $userLat = null;

    public ?float $userLng = null;

    public bool $ordenarPorProximidade = false;

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
        $quadras = Quadra::query()
            ->with('fotos')
            ->when($this->cidade, fn ($query) => $query->where('cidade', $this->cidade))
            ->when($this->bairro, fn ($query) => $query->where('bairro', $this->bairro))
            ->when($this->esporte, fn ($query) => $query->where('esporte', $this->esporte))
            ->when($this->quadraNome, fn ($query) => $query->where('nome', $this->quadraNome))
            ->when($this->cobertura !== '', fn ($query) => $query->where('cobertura', $this->cobertura === '1'))
            ->when($this->busca, function ($query) {
                $termo = '%'.$this->busca.'%';

                $query->where(fn ($sub) => $sub
                    ->where('nome', 'like', $termo)
                    ->orWhere('cidade', 'like', $termo)
                    ->orWhere('bairro', 'like', $termo)
                    ->orWhere('endereco', 'like', $termo)
                );
            })
            ->orderBy('nome')
            ->get();

        if ($this->userLat !== null && $this->userLng !== null) {
            $quadras->each(function (Quadra $quadra) {
                $quadra->distanciaKm = $quadra->distanciaKmAte($this->userLat, $this->userLng);
            });

            if ($this->ordenarPorProximidade) {
                $quadras = $quadras->sortBy(fn (Quadra $quadra) => $quadra->distanciaKm ?? INF)->values();
            }
        }

        return $quadras;
    }

    public function usarLocalizacao(float $latitude, float $longitude): void
    {
        $this->userLat = $latitude;
        $this->userLng = $longitude;
        $this->ordenarPorProximidade = true;
        $this->cidade = $this->cidadeMaisProximaDe($latitude, $longitude) ?? $this->cidade;

        unset($this->quadras);
    }

    /**
     * Reseta todos os filtros (cidade, bairro, esporte, quadra, cobertura, busca)
     * e desfaz a ordenação/localização por proximidade.
     */
    public function limparFiltros(): void
    {
        $this->reset(['cidade', 'bairro', 'esporte', 'quadraNome', 'cobertura', 'busca', 'userLat', 'userLng', 'ordenarPorProximidade']);

        unset($this->quadras);
    }

    public function temFiltrosAtivos(): bool
    {
        return $this->cidade !== ''
            || $this->bairro !== ''
            || $this->esporte !== ''
            || $this->quadraNome !== ''
            || $this->cobertura !== ''
            || $this->busca !== ''
            || $this->ordenarPorProximidade;
    }

    /**
     * Aproxima a cidade do usuário como a cidade da quadra cadastrada mais próxima
     * das coordenadas informadas (não há serviço de geocodificação reversa integrado).
     */
    private function cidadeMaisProximaDe(float $latitude, float $longitude): ?string
    {
        return Quadra::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->sortBy(fn (Quadra $quadra) => $quadra->distanciaKmAte($latitude, $longitude) ?? INF)
            ->first()
            ?->cidade;
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

    #[Computed]
    public function nomesQuadras(): Collection
    {
        return Quadra::query()->distinct()->orderBy('nome')->pluck('nome');
    }

    public function selecionarQuadra(int $quadraId): void
    {
        $this->quadraSelecionada = $quadraId;
        $this->data = '';
        $this->horaInicio = '';
        $this->resetErrorBag();
    }

    public function cancelarSelecao(): void
    {
        $this->quadraSelecionada = null;
        $this->resetErrorBag();
    }

    public function reservar()
    {
        abort_unless(auth()->check(), 403);

        $validated = $this->validate([
            'data' => ['required', 'date', 'after_or_equal:today'],
            'horaInicio' => ['required', 'in:'.implode(',', $this->horariosDisponiveis())],
        ]);

        $quadra = Quadra::findOrFail($this->quadraSelecionada);

        $horaInicio = $validated['horaInicio'].':00';
        $horaFim = date('H:i:s', strtotime($horaInicio.' +1 hour'));

        if (! $quadra->horarioDisponivel($validated['data'], $horaInicio, $horaFim)) {
            $this->addError('horaInicio', 'Esse horário já está reservado para esta quadra.');

            return null;
        }

        $reserva = Reserva::create([
            'quadra_id' => $quadra->id,
            'user_id' => auth()->id(),
            'data' => $validated['data'],
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim,
            'status' => ReservaStatus::Pendente,
        ]);

        return $this->redirect(route('reservas.pagamento', $reserva), navigate: false);
    }

    public function render()
    {
        return view('livewire.quadras.listagem', [
            'esportes' => Esporte::cases(),
        ]);
    }
}
