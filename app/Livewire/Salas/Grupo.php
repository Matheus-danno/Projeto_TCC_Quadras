<?php

namespace App\Livewire\Salas;

use App\Models\AtividadeSala;
use App\Models\Sala;
use Livewire\Component;

class Grupo extends Component
{
    public Sala $sala;

    public ?string $erro = null;

    public function mount(Sala $sala): void
    {
        abort_unless(auth()->check() && $sala->participantes->contains('id', auth()->id()), 403);

        $this->sala = $sala->load(['quadra', 'criador', 'participantes', 'atividades.user']);
    }

    public function sairDaSala(): void
    {
        $this->erro = null;

        if (auth()->id() === $this->sala->criador_id && $this->sala->participantes->count() > 1) {
            $this->erro = 'Você é o organizador da sala. Não é possível sair enquanto houver outros jogadores confirmados.';

            return;
        }

        $minutos = $this->sala->minutosParaComeco();

        if ($minutos === null || $minutos < 300) {
            $this->erro = 'Cancelamentos só podem ser feitos até 5h antes do início do jogo.';

            return;
        }

        $this->sala->participantes()->detach(auth()->id());

        AtividadeSala::create([
            'sala_id' => $this->sala->id,
            'user_id' => auth()->id(),
            'descricao' => auth()->user()->name.' saiu da sala',
        ]);

        session()->flash('sala-criada', 'Você saiu da sala.');

        $this->redirect(route('encontre_time'), navigate: false);
    }

    public function render()
    {
        return view('livewire.salas.grupo');
    }
}
