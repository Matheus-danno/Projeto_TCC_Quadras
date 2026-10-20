<?php

namespace App\Livewire\Auth;

use App\Concerns\PasswordValidationRules;
use App\Enums\Sexo;
use App\Models\User;
use App\Rules\CpfValido;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Registrar extends Component
{
    use PasswordValidationRules;

    /**
     * Idade mínima para criar conta de jogador. Abaixo dela, pela LGPD
     * (art. 14), o tratamento de dados exigiria consentimento de um dos pais.
     */
    public const IDADE_MINIMA = 16;

    public string $name = '';

    public string $email = '';

    public string $cpf = '';

    public string $diaNascimento = '';

    public string $mesNascimento = '';

    public string $anoNascimento = '';

    public string $sexo = '';

    public string $logradouro = '';

    public string $numero = '';

    public string $complemento = '';

    public string $bairro = '';

    public string $cep = '';

    public string $cidade = '';

    public string $estado = '';

    public string $telefone = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * @return list<int>
     */
    public function dias(): array
    {
        return range(1, 31);
    }

    /**
     * @return array<int, string>
     */
    public function meses(): array
    {
        return [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];
    }

    /**
     * @return list<int>
     */
    public function anos(): array
    {
        return range((int) now()->format('Y'), (int) now()->format('Y') - 100);
    }

    public function registrar()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'cpf' => ['required', 'string', new CpfValido],
            'diaNascimento' => ['required', 'integer', 'min:1', 'max:31'],
            'mesNascimento' => ['required', 'integer', 'min:1', 'max:12'],
            'anoNascimento' => ['required', 'integer', 'min:1900', 'max:'.now()->format('Y')],
            'sexo' => ['required', 'in:masculino,feminino'],
            'logradouro' => ['required', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['required', 'string', 'max:255'],
            'cep' => ['required', 'string'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', Rule::in(RegistrarDono::UFS)],
            'telefone' => ['required', 'string'],
            'password' => $this->passwordRules(),
        ], [
            'estado.in' => 'Selecione um estado (UF) válido.',
        ]);

        $cpfDigitos = preg_replace('/\D/', '', $validated['cpf']);

        $cepDigitos = preg_replace('/\D/', '', $validated['cep']);
        if (strlen($cepDigitos) !== 8) {
            $this->addError('cep', 'Informe um CEP válido com 8 dígitos.');

            return;
        }

        if (! checkdate((int) $validated['mesNascimento'], (int) $validated['diaNascimento'], (int) $validated['anoNascimento'])) {
            $this->addError('diaNascimento', 'Informe uma data de nascimento válida.');

            return;
        }

        $dataNascimento = sprintf('%04d-%02d-%02d', $validated['anoNascimento'], $validated['mesNascimento'], $validated['diaNascimento']);

        if (Carbon::parse($dataNascimento)->age < self::IDADE_MINIMA) {
            $this->addError('diaNascimento', 'É preciso ter pelo menos '.self::IDADE_MINIMA.' anos para criar uma conta.');

            return;
        }

        if (User::where('cpf', $cpfDigitos)->exists()) {
            $this->addError('cpf', 'Esse CPF já está cadastrado.');

            return;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'cpf' => $cpfDigitos,
            'data_nascimento' => $dataNascimento,
            'sexo' => $validated['sexo'],
            'telefone' => preg_replace('/\D/', '', $validated['telefone']),
        ]);

        $user->endereco()->create([
            'logradouro' => $validated['logradouro'],
            'numero' => $validated['numero'],
            'complemento' => $validated['complemento'],
            'bairro' => $validated['bairro'],
            'cep' => $cepDigitos,
            'cidade' => $validated['cidade'],
            'estado' => strtoupper($validated['estado']),
        ]);

        Auth::login($user);

        return $this->redirect(route('quadras.index'), navigate: false);
    }

    public function render()
    {
        return view('livewire.auth.registrar', [
            'sexos' => Sexo::cases(),
        ]);
    }
}
