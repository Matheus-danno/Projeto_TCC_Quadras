<?php

namespace App\Livewire\Admin\Aprovacoes;

use App\Enums\StatusCadastro;
use App\Enums\UserRole;
use App\Models\Quadra;
use App\Models\User;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Fila extends Component
{
    use InteractsWithComponents, WithPagination;

    #[Url]
    public string $aba = 'proprietarios';

    public string $busca = '';

    public string $filtroStatus = '';

    /**
     * Status que fazem parte da fila de aprovação (moderação pós-aprovação,
     * como "suspenso", fica fora do filtro aqui).
     *
     * @var list<StatusCadastro>
     */
    public const STATUS_FILA = [
        StatusCadastro::Pendente,
        StatusCadastro::EmAnalise,
        StatusCadastro::AjustesSolicitados,
        StatusCadastro::Aprovado,
        StatusCadastro::Rejeitado,
    ];

    public function updating($property): void
    {
        if (in_array($property, ['busca', 'filtroStatus', 'aba'], true)) {
            $this->resetPage();
        }
    }

    public function mudarAba(string $aba): void
    {
        $this->aba = $aba;
        $this->filtroStatus = '';
        $this->busca = '';
        $this->resetPage();
    }

    #[Computed]
    public function proprietariosPendentesCount(): int
    {
        return User::query()
            ->where('role', UserRole::DonoQuadra)
            ->whereIn('status_cadastro', [StatusCadastro::Pendente, StatusCadastro::EmAnalise])
            ->count();
    }

    #[Computed]
    public function quadrasPendentesCount(): int
    {
        return Quadra::query()
            ->whereIn('status_cadastro', [StatusCadastro::Pendente, StatusCadastro::EmAnalise])
            ->count();
    }

    #[Computed]
    public function totalPendentes(): int
    {
        return $this->proprietariosPendentesCount + $this->quadrasPendentesCount;
    }

    #[Computed]
    public function proprietarios()
    {
        return User::query()
            ->with('endereco')
            ->where('role', UserRole::DonoQuadra)
            ->when($this->filtroStatus, fn (Builder $query) => $query->where('status_cadastro', $this->filtroStatus))
            ->when($this->busca, function (Builder $query) {
                $termo = '%'.$this->busca.'%';
                $documento = preg_replace('/\D/', '', $this->busca);

                $query->where(function (Builder $query) use ($termo, $documento) {
                    $query->where('name', 'like', $termo)
                        ->orWhere('nome_estabelecimento', 'like', $termo)
                        ->orWhereHas('endereco', fn ($q) => $q->where('cidade', 'like', $termo))
                        ->when($documento, fn ($q) => $q->orWhere('cpf', $documento)->orWhere('cnpj', $documento));
                });
            })
            ->tap(fn (Builder $query) => $this->ordenarPendentesPrimeiro($query))
            ->paginate(10);
    }

    #[Computed]
    public function quadras()
    {
        return Quadra::query()
            ->with('dono')
            ->when($this->filtroStatus, fn (Builder $query) => $query->where('status_cadastro', $this->filtroStatus))
            ->when($this->busca, function (Builder $query) {
                $termo = '%'.$this->busca.'%';

                $query->where(fn ($q) => $q->where('nome', 'like', $termo)
                    ->orWhere('cidade', 'like', $termo)
                    ->orWhereHas('dono', fn ($q) => $q->where('name', 'like', $termo)));
            })
            ->tap(fn (Builder $query) => $this->ordenarPendentesPrimeiro($query))
            ->paginate(10);
    }

    /**
     * Pendentes e em análise primeiro (o que espera o admin), depois ajustes
     * solicitados e o resto; dentro de cada grupo, quem espera há mais tempo.
     */
    protected function ordenarPendentesPrimeiro(Builder $query): void
    {
        $query->orderByRaw(
            'case status_cadastro when ? then 0 when ? then 0 when ? then 1 else 2 end',
            [StatusCadastro::Pendente->value, StatusCadastro::EmAnalise->value, StatusCadastro::AjustesSolicitados->value],
        )->orderBy('updated_at');
    }

    public function render()
    {
        return view('livewire.admin.aprovacoes.fila', [
            'statusDisponiveis' => self::STATUS_FILA,
        ]);
    }
}
