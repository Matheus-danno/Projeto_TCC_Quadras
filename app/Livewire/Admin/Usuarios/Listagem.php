<?php

namespace App\Livewire\Admin\Usuarios;

use App\Enums\UserRole;
use App\Models\User;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Listagem extends Component
{
    use InteractsWithComponents;

    public string $filtroRole = '';

    public ?int $usuarioEmEdicaoId = null;

    public string $novoRole = '';

    #[Computed]
    public function usuarios(): Collection
    {
        return User::query()
            ->when($this->filtroRole, fn ($query) => $query->where('role', $this->filtroRole))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function usuarioSelecionado(): ?User
    {
        return $this->usuarioEmEdicaoId ? User::find($this->usuarioEmEdicaoId) : null;
    }

    public function prepararTrocaRole(int $userId, string $novoRole): void
    {
        abort_if($userId === auth()->id(), 403, 'Você não pode alterar o seu próprio papel.');

        if (! in_array($novoRole, array_column(UserRole::cases(), 'value'), true)) {
            return;
        }

        $this->usuarioEmEdicaoId = $userId;
        $this->novoRole = $novoRole;

        $this->modal('confirmar-role')->show();
    }

    public function confirmarTrocaRole(): void
    {
        abort_unless($this->usuarioEmEdicaoId, 404);
        abort_if($this->usuarioEmEdicaoId === auth()->id(), 403);

        $usuario = User::findOrFail($this->usuarioEmEdicaoId);
        $roleAnterior = $usuario->role;
        $roleNovo = UserRole::from($this->novoRole);

        $usuario->update(['role' => $roleNovo]);

        Log::info('Papel de usuário alterado por administrador.', [
            'admin_id' => auth()->id(),
            'usuario_id' => $usuario->id,
            'role_anterior' => $roleAnterior->value,
            'role_novo' => $roleNovo->value,
        ]);

        $this->modal('confirmar-role')->close();
        $this->toast('Papel de '.$usuario->name.' atualizado com sucesso.', variant: 'success');

        $this->usuarioEmEdicaoId = null;
        unset($this->usuarios);
    }

    public function render()
    {
        return view('livewire.admin.usuarios.listagem', [
            'rolesDisponiveis' => UserRole::cases(),
        ]);
    }
}
