<?php

namespace App\Livewire\Auth;

use App\Concerns\PasswordValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class RegistrarDono extends Component
{
    use PasswordValidationRules;

    public string $nomeEstabelecimento = '';

    public string $cnpj = '';

    public string $telefone = '';

    public string $name = '';

    public string $email = '';

    public string $endereco = '';

    public string $cidade = '';

    public string $estado = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function registrar()
    {
        $validated = $this->validate([
            'nomeEstabelecimento' => ['required', 'string', 'max:255'],
            'cnpj' => ['required', 'string'],
            'telefone' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'endereco' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
            'password' => $this->passwordRules(),
        ]);

        $cnpjDigitos = preg_replace('/\D/', '', $validated['cnpj']);
        if (strlen($cnpjDigitos) !== 14) {
            $this->addError('cnpj', 'Informe um CNPJ válido com 14 dígitos.');

            return;
        }

        if (User::where('cnpj', $cnpjDigitos)->exists()) {
            $this->addError('cnpj', 'Esse CNPJ já está cadastrado.');

            return;
        }

        $user = User::create([
            'name' => $validated['name'],
            'nome_estabelecimento' => $validated['nomeEstabelecimento'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::DonoQuadra,
            'cnpj' => $cnpjDigitos,
            'endereco' => $validated['endereco'],
            'cidade' => $validated['cidade'],
            'estado' => strtoupper($validated['estado']),
            'telefone' => preg_replace('/\D/', '', $validated['telefone']),
        ]);

        Auth::login($user);

        return $this->redirect(route('painel.dashboard'), navigate: false);
    }

    public function render()
    {
        return view('livewire.auth.registrar-dono');
    }
}
