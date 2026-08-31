<?php

namespace App\Livewire\Admin;

use App\Models\SuporteMensagem;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Mensagens extends Component
{
    use InteractsWithComponents;

    public string $filtroStatus = 'pendentes';

    #[Computed]
    public function mensagens(): Collection
    {
        return SuporteMensagem::query()
            ->with('user')
            ->when($this->filtroStatus === 'pendentes', fn ($query) => $query->whereNull('respondida_em'))
            ->when($this->filtroStatus === 'respondidas', fn ($query) => $query->whereNotNull('respondida_em'))
            ->latest()
            ->get();
    }

    public function marcarComoRespondida(int $mensagemId): void
    {
        $mensagem = SuporteMensagem::findOrFail($mensagemId);
        $mensagem->update(['respondida_em' => now()]);

        $this->toast('Mensagem marcada como respondida.', variant: 'success');

        unset($this->mensagens);
    }

    public function marcarComoPendente(int $mensagemId): void
    {
        $mensagem = SuporteMensagem::findOrFail($mensagemId);
        $mensagem->update(['respondida_em' => null]);

        unset($this->mensagens);
    }

    public function render()
    {
        return view('livewire.admin.mensagens');
    }
}
