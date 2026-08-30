<?php

namespace App\Livewire\Admin\Quadras;

use App\Enums\Esporte;
use App\Enums\UserRole;
use App\Models\Quadra;
use App\Models\User;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Listagem extends Component
{
    use InteractsWithComponents;

    public string $filtroCidade = '';

    public string $filtroEsporte = '';

    public string $filtroDonoId = '';

    public ?int $quadraEmEdicaoId = null;

    public ?int $quadraParaExcluirId = null;

    public ?string $bloqueioExclusao = null;

    public string $nome = '';

    public string $endereco = '';

    public string $cidade = '';

    public string $bairro = '';

    public string $esporte = '';

    public string $valor_hora = '';

    public bool $cobertura = false;

    public string $descricao = '';

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'endereco' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'bairro' => ['required', 'string', 'max:255'],
            'esporte' => ['required', 'in:'.implode(',', array_column(Esporte::cases(), 'value'))],
            'valor_hora' => ['required', 'numeric', 'min:0.01'],
            'cobertura' => ['boolean'],
            'descricao' => ['nullable', 'string', 'max:1000'],
        ];
    }

    #[Computed]
    public function quadras(): Collection
    {
        return Quadra::query()
            ->with('dono')
            ->when($this->filtroCidade, fn ($query) => $query->where('cidade', $this->filtroCidade))
            ->when($this->filtroEsporte, fn ($query) => $query->where('esporte', $this->filtroEsporte))
            ->when($this->filtroDonoId, fn ($query) => $query->where('dono_id', $this->filtroDonoId))
            ->orderBy('nome')
            ->get();
    }

    #[Computed]
    public function cidades(): Collection
    {
        return Quadra::query()->distinct()->orderBy('cidade')->pluck('cidade');
    }

    #[Computed]
    public function donos(): Collection
    {
        return User::query()
            ->where('role', UserRole::DonoQuadra->value)
            ->orderBy('name')
            ->get();
    }

    public function editar(int $quadraId): void
    {
        $quadra = Quadra::findOrFail($quadraId);

        $this->authorize('update', $quadra);

        $this->quadraEmEdicaoId = $quadra->id;
        $this->nome = $quadra->nome;
        $this->endereco = $quadra->endereco;
        $this->cidade = $quadra->cidade;
        $this->bairro = $quadra->bairro;
        $this->esporte = $quadra->esporte->value;
        $this->valor_hora = (string) $quadra->valor_hora;
        $this->cobertura = $quadra->cobertura;
        $this->descricao = $quadra->descricao ?? '';
        $this->resetErrorBag();

        $this->modal('form-quadra')->show();
    }

    public function salvar(): void
    {
        $quadra = Quadra::findOrFail($this->quadraEmEdicaoId);

        $this->authorize('update', $quadra);

        $validated = $this->validate();

        $quadra->update($validated);

        $this->modal('form-quadra')->close();
        $this->toast('Quadra atualizada com sucesso.', variant: 'success');

        unset($this->quadras);
    }

    public function pedirExclusao(int $quadraId): void
    {
        $quadra = Quadra::findOrFail($quadraId);

        $this->authorize('delete', $quadra);

        $this->quadraParaExcluirId = $quadra->id;
        $this->bloqueioExclusao = $quadra->temReservaFutura()
            ? 'Esta quadra tem reservas futuras (pendentes ou confirmadas) e não pode ser excluída. Cancele ou aguarde essas reservas antes de excluir.'
            : null;

        $this->modal('excluir-quadra')->show();
    }

    public function excluir(): void
    {
        abort_unless($this->quadraParaExcluirId, 404);

        $quadra = Quadra::findOrFail($this->quadraParaExcluirId);

        $this->authorize('delete', $quadra);

        if ($quadra->temReservaFutura()) {
            $this->bloqueioExclusao = 'Esta quadra tem reservas futuras (pendentes ou confirmadas) e não pode ser excluída. Cancele ou aguarde essas reservas antes de excluir.';

            return;
        }

        $quadra->delete();

        $this->modal('excluir-quadra')->close();
        $this->toast('Quadra excluída com sucesso.', variant: 'success');

        $this->quadraParaExcluirId = null;
        unset($this->quadras);
    }

    public function render()
    {
        return view('livewire.admin.quadras.listagem', [
            'esportes' => Esporte::cases(),
        ]);
    }
}
