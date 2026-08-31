<?php

namespace App\Livewire\Perfil;

use App\Concerns\ProfileValidationRules;
use App\Enums\Sexo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Editar extends Component
{
    use ProfileValidationRules, WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $dataNascimento = '';

    public string $sexo = '';

    public string $endereco = '';

    public string $cep = '';

    public string $cidade = '';

    public string $estado = '';

    public string $telefone = '';

    public $avatar = null;

    public ?string $mensagemSucesso = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->dataNascimento = $user->data_nascimento?->format('Y-m-d') ?? '';
        $this->sexo = $user->sexo?->value ?? '';
        $this->endereco = $user->endereco ?? '';
        $this->cep = $this->formatarCep($user->cep ?? '');
        $this->cidade = $user->cidade ?? '';
        $this->estado = $user->estado ?? '';
        $this->telefone = $this->formatarTelefone($user->telefone ?? '');
    }

    /**
     * CPF do usuário formatado para exibição (campo somente leitura,
     * não pode ser alterado após o cadastro).
     */
    public function cpfFormatado(): string
    {
        $cpf = Auth::user()->cpf;

        if (! $cpf || strlen($cpf) !== 11) {
            return $cpf ?? '';
        }

        return substr($cpf, 0, 3).'.'.substr($cpf, 3, 3).'.'.substr($cpf, 6, 3).'-'.substr($cpf, 9, 2);
    }

    public function salvar(): void
    {
        $user = Auth::user();

        $validated = $this->validate(array_merge($this->profileRules($user->id), [
            'dataNascimento' => ['required', 'date', 'before:today'],
            'sexo' => ['required', 'in:masculino,feminino'],
            'endereco' => ['required', 'string', 'max:255'],
            'cep' => ['required', 'string'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
            'telefone' => ['required', 'string'],
        ]));

        $cepDigitos = preg_replace('/\D/', '', $validated['cep']);
        if (strlen($cepDigitos) !== 8) {
            $this->addError('cep', 'Informe um CEP válido com 8 dígitos.');

            return;
        }

        $telefoneDigitos = preg_replace('/\D/', '', $validated['telefone']);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'data_nascimento' => $validated['dataNascimento'],
            'sexo' => $validated['sexo'],
            'endereco' => $validated['endereco'],
            'cep' => $cepDigitos,
            'cidade' => $validated['cidade'],
            'estado' => strtoupper($validated['estado']),
            'telefone' => $telefoneDigitos,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->cep = $this->formatarCep($cepDigitos);
        $this->telefone = $this->formatarTelefone($telefoneDigitos);

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

    private function formatarCep(string $cep): string
    {
        $digitos = preg_replace('/\D/', '', $cep);

        return strlen($digitos) === 8 ? substr($digitos, 0, 5).'-'.substr($digitos, 5, 3) : $cep;
    }

    private function formatarTelefone(string $telefone): string
    {
        $digitos = preg_replace('/\D/', '', $telefone);

        return strlen($digitos) === 11
            ? '('.substr($digitos, 0, 2).') '.substr($digitos, 2, 5).'-'.substr($digitos, 7, 4)
            : $telefone;
    }

    public function render()
    {
        return view('livewire.perfil.editar', [
            'sexos' => Sexo::cases(),
        ]);
    }
}
