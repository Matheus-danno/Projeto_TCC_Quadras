<?php

namespace App\Livewire\Salas;

use App\Enums\Esporte;
use App\Models\Sala;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Listagem extends Component
{
    public string $esporte = '';

    public array $erros = [];

    #[Computed]
    public function salas(): Collection
    {
        return Sala::query()
            ->with(['quadra', 'criador', 'participantes'])
            ->when($this->esporte, fn ($query) => $query->where('esporte', $this->esporte))
            ->latest()
            ->get();
    }

    public function entrar(int $salaId): void
    {
        unset($this->erros[$salaId]);

        if (! auth()->check()) {
            $this->erros[$salaId] = 'Você precisa entrar para participar de uma sala.';

            return;
        }

        $sala = Sala::with('participantes')->findOrFail($salaId);

        if ($sala->participantes->contains('id', auth()->id())) {
            $this->erros[$salaId] = 'Você já está nessa sala.';

            return;
        }

        if ($sala->participantes->count() >= $sala->max_participantes) {
            $this->erros[$salaId] = 'Essa sala já está cheia.';

            return;
        }

        $sala->participantes()->attach(auth()->id());

        unset($this->salas);
    }

    public function render()
    {
        return view('livewire.salas.listagem', [
            'esportes' => Esporte::cases(),
        ]);
    }
}
