<?php

namespace App\Livewire\Perfil;

use App\Concerns\ProfileValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Editar extends Component
{
    use ProfileValidationRules, WithFileUploads;

    public string $name = '';

    public string $email = '';

    public $avatar = null;

    public ?string $mensagemSucesso = null;

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function salvar(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->mensagemSucesso = 'Dados atualizados com sucesso.';
    }

    public function salvarFoto(): void
    {
        $this->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Escolha uma foto antes de salvar.',
            'avatar.image' => 'O arquivo precisa ser uma imagem.',
            'avatar.max' => 'A imagem pode ter no máximo 2MB.',
        ]);

        $user = Auth::user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update([
            'avatar_path' => $this->avatar->store('avatars/'.$user->id, 'public'),
        ]);

        $this->avatar = null;
        $this->mensagemSucesso = 'Foto de perfil atualizada.';
    }

    public function removerFoto(): void
    {
        $user = Auth::user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        $this->mensagemSucesso = 'Foto de perfil removida.';
    }

    public function render()
    {
        return view('livewire.perfil.editar');
    }
}
