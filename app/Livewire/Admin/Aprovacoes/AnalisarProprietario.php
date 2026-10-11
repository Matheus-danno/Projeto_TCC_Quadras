<?php

namespace App\Livewire\Admin\Aprovacoes;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Aprovacao\CadastroAprovacaoService;
use Closure;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AnalisarProprietario extends Component
{
    use InteractsWithComponents;

    public User $usuario;

    public string $acao = '';

    public string $motivo = '';

    /**
     * @var array<string, string>
     */
    public const CHECKLIST = [
        'documento_legivel' => 'Documento de identidade legível',
        'documento_confere' => 'CPF/CNPJ confere com o documento enviado',
        'titularidade_confere' => 'Titularidade da chave Pix/conta bate com o CPF/CNPJ',
        'endereco_confere' => 'Endereço informado parece válido',
    ];

    /**
     * @var array<string, bool>
     */
    public array $checklist = [];

    public function mount(User $usuario, CadastroAprovacaoService $aprovacaoService): void
    {
        abort_if($usuario->role !== UserRole::DonoQuadra, 404);

        $this->usuario = $usuario;
        $this->checklist = array_merge(
            array_fill_keys(array_keys(self::CHECKLIST), false),
            $usuario->checklist_aprovacao ?? []
        );

        $aprovacaoService->iniciarAnalise($this->usuario, Auth::user());
        $this->usuario->refresh();
    }

    public function toggleChecklist(string $chave): void
    {
        if (! array_key_exists($chave, self::CHECKLIST)) {
            return;
        }

        $this->checklist[$chave] = ! ($this->checklist[$chave] ?? false);
        $this->usuario->update(['checklist_aprovacao' => $this->checklist]);
    }

    #[Computed]
    public function documentos(): Collection
    {
        return $this->usuario->documentos()->get();
    }

    #[Computed]
    public function historico(): Collection
    {
        return $this->usuario->historicoCadastro()->with('admin')->get();
    }

    #[Computed]
    public function quadras(): Collection
    {
        return $this->usuario->quadras()->get();
    }

    public function abrirConfirmacao(string $acao): void
    {
        $this->acao = $acao;
        $this->motivo = '';
        $this->resetErrorBag('motivo');

        $this->modal('confirmar-acao')->show();
    }

    public function confirmar(CadastroAprovacaoService $aprovacaoService): void
    {
        $admin = Auth::user();

        match ($this->acao) {
            'aprovar' => $aprovacaoService->aprovar($this->usuario, $admin),
            'rejeitar' => $this->executarComMotivo(fn (string $motivo) => $aprovacaoService->rejeitar($this->usuario, $admin, $motivo)),
            'ajustes' => $this->executarComMotivo(fn (string $motivo) => $aprovacaoService->solicitarAjustes($this->usuario, $admin, $motivo)),
            default => null,
        };

        $this->modal('confirmar-acao')->close();
        $this->toast(__('Decisão registrada.'), variant: 'success');

        $this->redirect(route('admin.aprovacoes', ['aba' => 'proprietarios']), navigate: true);
    }

    /**
     * Valida o motivo (obrigatório pra rejeitar/pedir ajustes) antes de
     * executar a ação — se a validação falhar, o Livewire interrompe aqui
     * e reexibe o erro no modal, sem chegar ao toast/redirect.
     */
    private function executarComMotivo(Closure $callback): void
    {
        $validated = $this->validate(['motivo' => ['required', 'string', 'min:5', 'max:1000']]);

        $callback($validated['motivo']);
    }

    public function render()
    {
        return view('livewire.admin.aprovacoes.analisar-proprietario');
    }
}
