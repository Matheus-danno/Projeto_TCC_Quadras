<?php

namespace App\Livewire\Admin\Usuarios;

use App\Enums\UserRole;
use App\Models\User;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Listagem extends Component
{
    use InteractsWithComponents, WithPagination;

    public string $busca = '';

    public string $filtroPapel = '';

    public function updating($property): void
    {
        if (in_array($property, ['busca', 'filtroPapel'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function usuarios()
    {
        return User::query()
            ->where('role', '!=', UserRole::Admin)
            ->when($this->busca, function (Builder $query) {
                $termo = '%'.$this->busca.'%';

                $query->where(fn ($q) => $q->where('name', 'like', $termo)->orWhere('email', 'like', $termo));
            })
            ->when($this->filtroPapel, fn (Builder $query) => $query->where('role', $this->filtroPapel))
            ->orderBy('name')
            ->paginate(10);
    }

    public function bloquear(int $userId): void
    {
        $usuario = User::findOrFail($userId);

        abort_if($usuario->role === UserRole::Admin, 403);

        $usuario->update(['bloqueado_em' => now()]);

        $this->toast(__('Usuário bloqueado.'), variant: 'success');

        unset($this->usuarios);
    }

    public function desbloquear(int $userId): void
    {
        User::findOrFail($userId)->update(['bloqueado_em' => null]);

        $this->toast(__('Usuário desbloqueado.'), variant: 'success');

        unset($this->usuarios);
    }

    public function render()
    {
        return view('livewire.admin.usuarios.listagem', [
            'papeisDisponiveis' => [UserRole::Jogador, UserRole::DonoQuadra],
        ]);
    }
}
