<?php

namespace App\Livewire\Painel\Cadastro;

use App\Enums\TipoDocumento;
use App\Models\CadastroHistorico;
use App\Models\Documento;
use App\Models\Quadra;
use App\Services\Aprovacao\CadastroAprovacaoService;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Status extends Component
{
    use InteractsWithComponents;
    use WithFileUploads;

    public ?TemporaryUploadedFile $novoDocumentoIdentidade = null;

    #[Computed]
    public function usuario()
    {
        return auth()->user();
    }

    #[Computed]
    public function motivoAtual(): ?CadastroHistorico
    {
        return $this->usuario->historicoCadastro()->whereNotNull('motivo')->first();
    }

    #[Computed]
    public function quadras(): Collection
    {
        return auth()->user()->quadras()->with('historicoCadastro')->orderByDesc('updated_at')->get();
    }

    public function motivoDaQuadra(Quadra $quadra): ?CadastroHistorico
    {
        return $quadra->historicoCadastro->first(fn (CadastroHistorico $h) => $h->motivo !== null);
    }

    public function reenviar(CadastroAprovacaoService $aprovacaoService): void
    {
        if ($this->novoDocumentoIdentidade) {
            $this->validate([
                'novoDocumentoIdentidade' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            ]);

            $usuario = auth()->user();

            $usuario->documentos()->where('tipo', TipoDocumento::DocumentoIdentidade)->get()
                ->each(function (Documento $documento) {
                    Storage::disk('local')->delete($documento->caminho);
                    $documento->delete();
                });

            $caminho = $this->novoDocumentoIdentidade->store('documentos/users/'.$usuario->id, 'local');

            $usuario->documentos()->create([
                'tipo' => TipoDocumento::DocumentoIdentidade,
                'caminho' => $caminho,
                'nome_original' => $this->novoDocumentoIdentidade->getClientOriginalName(),
                'tamanho_bytes' => $this->novoDocumentoIdentidade->getSize(),
            ]);

            $this->novoDocumentoIdentidade = null;
        }

        $aprovacaoService->reenviar(auth()->user());

        unset($this->usuario, $this->motivoAtual);

        $this->toast(__('Cadastro reenviado para análise.'), variant: 'success');
    }

    public function render()
    {
        return view('livewire.painel.cadastro.status');
    }
}
