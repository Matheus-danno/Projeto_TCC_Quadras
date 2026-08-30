<?php

namespace App\Livewire\Painel\Quadras;

use App\Models\Quadra;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Listagem extends Component
{
    use InteractsWithComponents;

    public ?int $quadraParaExcluirId = null;

    public ?string $bloqueioExclusao = null;

    public string $busca = '';

    public string $filtroStatus = 'todas';

    #[Computed]
    public function quadras(): Collection
    {
        return Quadra::query()
            ->where('dono_id', auth()->id())
            ->when($this->busca, fn ($query) => $query->where('nome', 'like', '%'.$this->busca.'%'))
            ->when($this->filtroStatus === 'ativa', fn ($query) => $query->where('ativa', true))
            ->when($this->filtroStatus === 'inativa', fn ($query) => $query->where('ativa', false))
            ->with('fotos')
            ->withCount('reservas')
            ->orderBy('nome')
            ->get();
    }

    public function cancelar(int $quadraId): void
    {
        $quadra = Quadra::findOrFail($quadraId);

        $this->authorize('update', $quadra);

        $quadra->update(['ativa' => false]);

        $this->toast('Quadra cancelada.', variant: 'success');

        unset($this->quadras);
    }

    public function ativar(int $quadraId): void
    {
        $quadra = Quadra::findOrFail($quadraId);

        $this->authorize('update', $quadra);

        $quadra->update(['ativa' => true]);

        $this->toast('Quadra ativada novamente.', variant: 'success');

        unset($this->quadras);
    }

    public function pedirExclusao(int $quadraId): void
    {
        $quadra = Quadra::findOrFail($quadraId);

        $this->authorize('delete', $quadra);

        $this->quadraParaExcluirId = $quadra->id;
        $this->bloqueioExclusao = $quadra->temReservaFutura()
            ? 'Esta quadra tem reservas futuras (pendentes ou confirmadas) e não pode ser excluída. Cancele ou aguarde essas reservas antes de excluir.'
            : null;

        $this->modal('excluir-quadra')->show();
    }

    public function excluir(): void
    {
        abort_unless($this->quadraParaExcluirId, 404);

        $quadra = Quadra::findOrFail($this->quadraParaExcluirId);

        $this->authorize('delete', $quadra);

        if ($quadra->temReservaFutura()) {
            $this->bloqueioExclusao = 'Esta quadra tem reservas futuras (pendentes ou confirmadas) e não pode ser excluída. Cancele ou aguarde essas reservas antes de excluir.';

            return;
        }

        $quadra->delete();

        $this->modal('excluir-quadra')->close();
        $this->toast('Quadra excluída com sucesso.', variant: 'success');

        $this->quadraParaExcluirId = null;
        unset($this->quadras);
    }

    public function render()
    {
        return view('livewire.painel.quadras.listagem');
    }
}
