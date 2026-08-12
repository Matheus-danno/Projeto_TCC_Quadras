<?php

namespace App\Livewire\Salas;

use App\Enums\Esporte;
use App\Models\Quadra;
use App\Models\Sala;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Criar extends Component
{
    public string $nome = '';

    public string $esporte = '';

    public ?int $quadraId = null;

    public int $maxParticipantes = 10;

    #[Computed]
    public function quadras(): Collection
    {
        return Quadra::query()->orderBy('nome')->get();
    }

    public function incrementarVagas(): void
    {
        $this->maxParticipantes = min(50, $this->maxParticipantes + 1);
    }

    public function decrementarVagas(): void
    {
        $this->maxParticipantes = max(2, $this->maxParticipantes - 1);
    }

    public function criar()
    {
        abort_unless(auth()->check(), 403);

        $validated = $this->validate([
            'nome' => ['required', 'string', 'max:255'],
            'esporte' => ['required', 'in:'.implode(',', array_column(Esporte::cases(), 'value'))],
            'quadraId' => ['nullable', 'exists:quadras,id'],
            'maxParticipantes' => ['required', 'integer', 'min:2', 'max:50'],
        ]);

        $sala = Sala::create([
            'nome' => $validated['nome'],
            'esporte' => $validated['esporte'],
            'quadra_id' => $validated['quadraId'],
            'criador_id' => auth()->id(),
            'max_participantes' => $validated['maxParticipantes'],
        ]);

        $sala->participantes()->attach(auth()->id());

        session()->flash('sala-criada', "Sala \"{$sala->nome}\" criada com sucesso!");

        return $this->redirect(route('encontre_time'), navigate: false);
    }

    public function render()
    {
        return view('livewire.salas.criar', [
            'esportes' => Esporte::cases(),
        ]);
    }
}
