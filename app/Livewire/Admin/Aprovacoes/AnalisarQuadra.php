<?php

namespace App\Livewire\Admin\Aprovacoes;

use App\Models\Quadra;
use App\Services\Aprovacao\CadastroAprovacaoService;
use Closure;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use RuntimeException;

class AnalisarQuadra extends Component
{
    use InteractsWithComponents;

    public Quadra $quadra;

    public string $acao = '';

    public string $motivo = '';

    /**
     * @var array<string, string>
     */
    public const CHECKLIST = [
        'fotos_condizentes' => 'Fotos condizem com a quadra descrita',
        'endereco_confere' => 'Endereço confere com o comprovante',
        'comprovante_valido' => 'Comprovante de uso do imóvel é válido',
        'dados_consistentes' => 'Preço, horários e informações são consistentes',
    ];

    /**
     * @var array<string, bool>
     */
    public array $checklist = [];

    public function mount(Quadra $quadra, CadastroAprovacaoService $aprovacaoService): void
    {
        $this->quadra = $quadra->load('dono');
        $this->checklist = array_merge(
            array_fill_keys(array_keys(self::CHECKLIST), false),
            $quadra->checklist_aprovacao ?? []
        );

        $aprovacaoService->iniciarAnalise($this->quadra, Auth::user());
        $this->quadra->refresh();
    }

    public function toggleChecklist(string $chave): void
    {
        if (! array_key_exists($chave, self::CHECKLIST)) {
            return;
        }

        $this->checklist[$chave] = ! ($this->checklist[$chave] ?? false);
        $this->quadra->update(['checklist_aprovacao' => $this->checklist]);
    }

    #[Computed]
    public function documentos(): Collection
    {
        return $this->quadra->documentos()->get();
    }

    #[Computed]
    public function fotos(): Collection
    {
        return $this->quadra->fotos()->get();
    }

    #[Computed]
    public function historico(): Collection
    {
        return $this->quadra->historicoCadastro()->with('admin')->get();
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

        try {
            match ($this->acao) {
                'aprovar' => $aprovacaoService->aprovar($this->quadra, $admin),
                'rejeitar' => $this->executarComMotivo(fn (string $motivo) => $aprovacaoService->rejeitar($this->quadra, $admin, $motivo)),
                'ajustes' => $this->executarComMotivo(fn (string $motivo) => $aprovacaoService->solicitarAjustes($this->quadra, $admin, $motivo)),
                default => null,
            };
        } catch (RuntimeException $e) {
            $this->toast($e->getMessage(), variant: 'danger');

            return;
        }

        $this->modal('confirmar-acao')->close();
        $this->toast(__('Decisão registrada.'), variant: 'success');

        $this->redirect(route('admin.aprovacoes', ['aba' => 'quadras']), navigate: true);
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
        return view('livewire.admin.aprovacoes.analisar-quadra');
    }
}
