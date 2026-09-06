<?php

namespace App\Livewire\Auth;

use App\Concerns\PasswordValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use App\Rules\CnpjValido;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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

    public bool $aceitaComissao = false;

    /**
     * Siglas de UF válidas, usadas tanto no formulário (blade) quanto aqui.
     *
     * @var list<string>
     */
    public const UFS = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS',
        'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC',
        'SP', 'SE', 'TO',
    ];

    protected function rules(): array
    {
        return [
            'nomeEstabelecimento' => ['required', 'string', 'min:3', 'max:255'],
            'cnpj' => [
                'required',
                'string',
                new CnpjValido,
                function (string $attribute, mixed $value, Closure $fail) {
                    if (User::where('cnpj', preg_replace('/\D/', '', $value))->exists()) {
                        $fail('Esse CNPJ já está cadastrado.');
                    }
                },
            ],
            'telefone' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) {
                    $digitos = preg_replace('/\D/', '', $value);

                    if (strlen($digitos) < 10 || strlen($digitos) > 11) {
                        $fail('Informe um telefone válido, com DDD.');
                    }
                },
            ],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'endereco' => ['required', 'string', 'min:5', 'max:255'],
            'cidade' => ['required', 'string', 'min:2', 'max:255'],
            'estado' => ['required', 'string', Rule::in(self::UFS)],
            'password' => $this->passwordRules(),
            'aceitaComissao' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'aceitaComissao.accepted' => 'Você precisa aceitar o termo de comissão para continuar.',
            'estado.in' => 'Selecione um estado (UF) válido.',
        ];
    }

    public function registrar()
    {
        $validated = $this->validate();

        $user = User::create([
            'name' => $validated['name'],
            'nome_estabelecimento' => $validated['nomeEstabelecimento'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::DonoQuadra,
            'cnpj' => preg_replace('/\D/', '', $validated['cnpj']),
            'endereco' => $validated['endereco'],
            'cidade' => $validated['cidade'],
            'estado' => strtoupper($validated['estado']),
            'telefone' => preg_replace('/\D/', '', $validated['telefone']),
            'comissao_aceita_em' => now(),
        ]);

        Auth::login($user);

        return $this->redirect(route('painel.dashboard'), navigate: false);
    }

    public function render()
    {
        return view('livewire.auth.registrar-dono');
    }
}
