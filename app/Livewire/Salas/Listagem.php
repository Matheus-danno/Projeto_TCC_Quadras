<?php

namespace App\Livewire\Salas;

use App\Enums\Esporte;
use App\Enums\NivelHabilidade;
use App\Enums\Privacidade;
use App\Enums\SalaStatus;
use App\Models\Sala;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Listagem extends Component
{
    public string $esporte = '';

    public string $nivel = '';

    public string $distanciaKm = '';

    public string $horario = '';

    public string $data = '';

    public ?float $userLat = null;

    public ?float $userLng = null;

    public bool $ordenarPorProximidade = false;

    public array $erros = [];

    #[Computed]
    public function salas(): Collection
    {
        $salas = Sala::query()
            ->with(['quadra', 'criador.avaliacoesRecebidas', 'participantes'])
            ->where('privacidade', Privacidade::Publica->value)
            ->where('status', SalaStatus::Aberta->value)
            ->when($this->esporte, fn ($query) => $query->where('esporte', $this->esporte))
            ->when($this->nivel, fn ($query) => $query->where('nivel_desejado', $this->nivel))
            ->when($this->data, fn ($query) => $query->whereDate('data', $this->data))
            ->when($this->horario, fn ($query) => $query->where('horario_inicio', $this->horario))
            ->orderByDesc('destaque')
            ->orderBy('data')
            ->orderBy('horario_inicio')
            ->get();

        if ($this->userLat !== null && $this->userLng !== null) {
            $salas->each(function (Sala $sala) {
                $sala->distanciaKm = $sala->quadra?->distanciaKmAte($this->userLat, $this->userLng);
            });

            if ($this->distanciaKm !== '') {
                $salas = $salas->filter(
                    fn (Sala $sala) => $sala->distanciaKm !== null && $sala->distanciaKm <= (float) $this->distanciaKm
                );
            }

            if ($this->ordenarPorProximidade) {
                $salas = $salas
                    ->filter(fn (Sala $sala) => $sala->distanciaKm !== null)
                    ->sortBy(fn (Sala $sala) => $sala->distanciaKm)
                    ->values();
            }
        }

        return $salas;
    }

    public function usarLocalizacao(float $latitude, float $longitude): void
    {
        $this->userLat = $latitude;
        $this->userLng = $longitude;
        $this->ordenarPorProximidade = true;

        unset($this->salas);
    }

    public function limparFiltros(): void
    {
        $this->reset(['esporte', 'nivel', 'distanciaKm', 'horario', 'data']);

        unset($this->salas);
    }

    public function entrar(int $salaId): void
    {
        unset($this->erros[$salaId]);

        if (! auth()->check()) {
            $this->dispatch('login-necessario', mensagem: 'Você precisa entrar para participar de uma sala.');

            return;
        }

        $sala = Sala::with('participantes')->findOrFail($salaId);

        $resultado = $sala->entrarComo(auth()->user());

        if (! $resultado['sucesso']) {
            $this->erros[$salaId] = $resultado['mensagem'];

            return;
        }

        unset($this->salas);
    }

    public function render()
    {
        return view('livewire.salas.listagem', [
            'esportes' => Esporte::cases(),
            'niveis' => NivelHabilidade::cases(),
        ]);
    }
}
