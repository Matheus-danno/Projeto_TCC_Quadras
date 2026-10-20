<?php

namespace App\Livewire\Auth;

use App\Concerns\PasswordValidationRules;
use App\Enums\StatusCadastro;
use App\Enums\TipoDocumento;
use App\Enums\TipoPessoa;
use App\Enums\UserRole;
use App\Models\User;
use App\Rules\CnpjValido;
use App\Rules\CpfValido;
use App\Services\Aprovacao\CadastroAprovacaoService;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class RegistrarDono extends Component
{
    use PasswordValidationRules;
    use WithFileUploads;

    public string $tipoPessoa = 'juridica';

    public string $nomeEstabelecimento = '';

    public string $cnpj = '';

    public string $cpf = '';

    public string $telefone = '';

    public string $name = '';

    public string $email = '';

    public string $logradouro = '';

    public string $numero = '';

    public string $complemento = '';

    public string $bairro = '';

    public string $cidade = '';

    public string $estado = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $aceitaComissao = false;

    public bool $aceitaTermos = false;

    public ?TemporaryUploadedFile $documentoIdentidade = null;

    public string $chavePixRecebimento = '';

    public string $titularNome = '';

    public string $titularDocumento = '';

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
            'tipoPessoa' => ['required', Rule::in(array_column(TipoPessoa::cases(), 'value'))],
            'nomeEstabelecimento' => ['required', 'string', 'min:3', 'max:255'],
            'cnpj' => [
                Rule::requiredIf($this->tipoPessoa === TipoPessoa::Juridica->value),
                'nullable',
                'string',
                new CnpjValido,
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($this->tipoPessoa !== TipoPessoa::Juridica->value) {
                        return;
                    }

                    if (User::where('cnpj', preg_replace('/\D/', '', $value))->exists()) {
                        $fail('Esse CNPJ já está cadastrado.');
                    }
                },
            ],
            'cpf' => [
                Rule::requiredIf($this->tipoPessoa === TipoPessoa::Fisica->value),
                'nullable',
                'string',
                new CpfValido,
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($this->tipoPessoa !== TipoPessoa::Fisica->value) {
                        return;
                    }

                    if (User::where('cpf', preg_replace('/\D/', '', $value))->exists()) {
                        $fail('Esse CPF já está cadastrado.');
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
            'logradouro' => ['required', 'string', 'min:5', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'min:2', 'max:255'],
            'estado' => ['required', 'string', Rule::in(self::UFS)],
            'password' => $this->passwordRules(),
            'aceitaComissao' => ['accepted'],
            'aceitaTermos' => ['accepted'],
            'documentoIdentidade' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'chavePixRecebimento' => ['required', 'string', 'max:255'],
            'titularNome' => ['required', 'string', 'min:3', 'max:255'],
            'titularDocumento' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'aceitaComissao.accepted' => 'Você precisa aceitar o termo de comissão para continuar.',
            'aceitaTermos.accepted' => 'Você precisa aceitar a Política de Privacidade e os Termos de Uso para continuar.',
            'estado.in' => 'Selecione um estado (UF) válido.',
            'documentoIdentidade.required' => 'Envie uma foto ou PDF do seu documento de identidade.',
            'documentoIdentidade.mimes' => 'O documento precisa ser JPG, PNG ou PDF.',
            'documentoIdentidade.max' => 'O documento pode ter no máximo 10 MB.',
            'titularDocumento.required' => 'Informe o CPF/CNPJ do titular da chave Pix ou conta.',
        ];
    }

    public function updatedTipoPessoa(): void
    {
        $this->resetErrorBag(['cnpj', 'cpf']);
    }

    public function registrar(CadastroAprovacaoService $aprovacaoService)
    {
        $validated = $this->validate();

        $tipoPessoa = TipoPessoa::from($validated['tipoPessoa']);

        $user = DB::transaction(function () use ($validated, $tipoPessoa) {
            $user = User::create([
                'name' => $validated['name'],
                'nome_estabelecimento' => $validated['nomeEstabelecimento'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => UserRole::DonoQuadra,
                'cnpj' => $tipoPessoa === TipoPessoa::Juridica ? preg_replace('/\D/', '', $validated['cnpj']) : null,
                'cpf' => $tipoPessoa === TipoPessoa::Fisica ? preg_replace('/\D/', '', $validated['cpf']) : null,
                'telefone' => preg_replace('/\D/', '', $validated['telefone']),
                'comissao_aceita_em' => now(),
                'consentimento_em' => now(),
                'consentimento_versao' => config('privacidade.versao'),
                'status_cadastro' => StatusCadastro::Pendente,
                'chave_pix_recebimento' => $validated['chavePixRecebimento'],
                'titular_recebimento_nome' => $validated['titularNome'],
                'titular_recebimento_documento' => preg_replace('/\D/', '', $validated['titularDocumento']),
            ]);

            $user->endereco()->create([
                'logradouro' => $validated['logradouro'],
                'numero' => $validated['numero'],
                'complemento' => $validated['complemento'],
                'bairro' => $validated['bairro'],
                'cidade' => $validated['cidade'],
                'estado' => strtoupper($validated['estado']),
            ]);

            $caminho = $this->documentoIdentidade->store('documentos/users/'.$user->id, 'local');

            $user->documentos()->create([
                'tipo' => TipoDocumento::DocumentoIdentidade,
                'caminho' => $caminho,
                'nome_original' => $this->documentoIdentidade->getClientOriginalName(),
                'tamanho_bytes' => $this->documentoIdentidade->getSize(),
            ]);

            return $user;
        });

        $aprovacaoService->registrarEnvio($user);

        Auth::login($user);

        return $this->redirect(route('painel.cadastro.status'), navigate: false);
    }

    public function render()
    {
        return view('livewire.auth.registrar-dono', [
            'tiposPessoa' => TipoPessoa::cases(),
        ]);
    }
}
