<?php

namespace App\Livewire\Painel\Quadras;

use App\Enums\Esporte;
use App\Models\Quadra;
use App\Models\QuadraFoto;
use App\Services\Geocoding\NominatimClient;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Formulario extends Component
{
    use InteractsWithComponents;
    use WithFileUploads;

    protected NominatimClient $geocoder;

    public function boot(NominatimClient $geocoder): void
    {
        $this->geocoder = $geocoder;
    }

    public ?Quadra $quadra = null;

    public string $nome = '';

    public string $endereco = '';

    public string $bairro = '';

    public string $cidade = '';

    public string $cep = '';

    public string $esporte = '';

    public string $valor_hora = '';

    public string $capacidade_maxima = '';

    public bool $cobertura = false;

    public string $descricao = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $novasFotos = [];

    public ?string $bloqueioExclusao = null;

    public function mount(?Quadra $quadra = null): void
    {
        if ($quadra?->exists) {
            $this->authorize('update', $quadra);

            $this->quadra = $quadra;
            $this->nome = $quadra->nome;
            $this->endereco = $quadra->endereco;
            $this->bairro = $quadra->bairro;
            $this->cidade = $quadra->cidade;
            $this->cep = $quadra->cep ?? '';
            $this->esporte = $quadra->esporte->value;
            $this->valor_hora = (string) $quadra->valor_hora;
            $this->capacidade_maxima = $quadra->capacidade_maxima ? (string) $quadra->capacidade_maxima : '';
            $this->cobertura = $quadra->cobertura;
            $this->descricao = $quadra->descricao ?? '';
        }
    }

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'endereco' => ['required', 'string', 'max:255'],
            'bairro' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'cep' => ['required', 'string', 'max:9'],
            'esporte' => ['required', 'in:'.implode(',', array_column(Esporte::cases(), 'value'))],
            'valor_hora' => ['required', 'numeric', 'min:0.01'],
            'capacidade_maxima' => ['required', 'integer', 'min:1'],
            'cobertura' => ['boolean'],
            'descricao' => ['required', 'string', 'max:1000'],
            'novasFotos' => ['array', 'max:8'],
            'novasFotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function fotosExistentes()
    {
        return $this->quadra?->fotos()->get() ?? collect();
    }

    public function totalFotos(): int
    {
        return $this->fotosExistentes()->count() + count($this->novasFotos);
    }

    public function removerFotoExistente(int $fotoId): void
    {
        $foto = QuadraFoto::findOrFail($fotoId);

        $this->authorize('update', $foto->quadra);

        Storage::disk('public')->delete($foto->caminho);
        $foto->delete();

        $this->reordenarFotos($foto->quadra);
    }

    public function removerNovaFoto(int $indice): void
    {
        unset($this->novasFotos[$indice]);
        $this->novasFotos = array_values($this->novasFotos);
    }

    public function salvar(): void
    {
        $validated = $this->validate();

        if (($this->fotosExistentes()->count() + count($this->novasFotos)) > 8) {
            $this->addError('novasFotos', __('Você pode ter no máximo 8 fotos por quadra.'));

            return;
        }

        $dados = [
            'nome' => $validated['nome'],
            'endereco' => $validated['endereco'],
            'bairro' => $validated['bairro'],
            'cidade' => $validated['cidade'],
            'cep' => $validated['cep'],
            'esporte' => $validated['esporte'],
            'valor_hora' => $validated['valor_hora'],
            'capacidade_maxima' => $validated['capacidade_maxima'],
            'cobertura' => $this->cobertura,
            'descricao' => $validated['descricao'],
        ];

        $coordenadas = $this->geocoder->geocodificar(
            trim("{$validated['endereco']}, {$validated['bairro']}, {$validated['cidade']}")
        );

        if ($coordenadas !== null) {
            [$dados['latitude'], $dados['longitude']] = $coordenadas;
        } elseif (! $this->quadra) {
            $this->toast(
                __('Não foi possível localizar o endereço automaticamente. A quadra pode não aparecer em buscas por proximidade.'),
                variant: 'warning',
            );
        }

        if ($this->quadra) {
            $this->authorize('update', $this->quadra);

            $this->quadra->update($dados);
        } else {
            $this->quadra = Quadra::create([
                ...$dados,
                'dono_id' => auth()->id(),
            ]);
        }

        foreach ($this->novasFotos as $foto) {
            $caminho = $foto->store('quadras/'.$this->quadra->id, 'public');

            QuadraFoto::create([
                'quadra_id' => $this->quadra->id,
                'caminho' => $caminho,
                'ordem' => 0,
            ]);
        }

        $this->novasFotos = [];
        $this->reordenarFotos($this->quadra);

        $this->redirect(route('painel.quadras.show', $this->quadra), navigate: true);
    }

    public function pedirExclusao(): void
    {
        $this->authorize('delete', $this->quadra);

        $this->bloqueioExclusao = $this->quadra->temReservaFutura()
            ? __('Esta quadra tem reservas futuras (pendentes ou confirmadas) e não pode ser excluída. Cancele ou aguarde essas reservas antes de excluir.')
            : null;

        $this->modal('excluir-quadra')->show();
    }

    public function excluir(): void
    {
        $this->authorize('delete', $this->quadra);

        if ($this->quadra->temReservaFutura()) {
            $this->bloqueioExclusao = __('Esta quadra tem reservas futuras (pendentes ou confirmadas) e não pode ser excluída. Cancele ou aguarde essas reservas antes de excluir.');

            return;
        }

        $this->quadra->delete();

        $this->modal('excluir-quadra')->close();
        $this->toast(__('Quadra excluída com sucesso.'), variant: 'success');

        $this->redirect(route('painel.quadras'), navigate: true);
    }

    protected function reordenarFotos(Quadra $quadra): void
    {
        $fotos = $quadra->fotos()->orderBy('ordem')->orderBy('id')->get();

        foreach ($fotos->values() as $indice => $foto) {
            $foto->update([
                'ordem' => $indice,
                'capa' => $indice === 0,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.painel.quadras.formulario', [
            'esportes' => Esporte::cases(),
        ]);
    }
}
