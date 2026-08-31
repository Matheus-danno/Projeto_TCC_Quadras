<?php

namespace App\Livewire\Salas;

use App\Models\Avaliacao;
use App\Models\AvaliacaoQuadra;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Avaliar extends Component
{
    public Sala $sala;

    /**
     * Alvo em avaliação no momento: 'administrador', 'quadra' ou 'participante-{id}'.
     */
    public ?string $alvoAtivo = null;

    public int $nota = 5;

    public string $comentario = '';

    public ?string $mensagemSucesso = null;

    public function mount(Sala $sala): void
    {
        abort_unless($sala->participantes->contains('id', auth()->id()), 403);
        abort_unless($this->jogoJaAconteceu($sala), 403);

        $this->sala = $sala->load(['quadra', 'criador', 'participantes']);
    }

    private function jogoJaAconteceu(Sala $sala): bool
    {
        return $sala->data !== null && $sala->data->lt(now()->startOfDay());
    }

    #[Computed]
    public function avaliacaoAdministrador(): ?Avaliacao
    {
        return Avaliacao::query()
            ->where('sala_id', $this->sala->id)
            ->where('avaliado_id', $this->sala->criador_id)
            ->where('autor_id', auth()->id())
            ->first();
    }

    #[Computed]
    public function avaliacaoQuadra(): ?AvaliacaoQuadra
    {
        if (! $this->sala->quadra_id) {
            return null;
        }

        return AvaliacaoQuadra::query()
            ->where('sala_id', $this->sala->id)
            ->where('quadra_id', $this->sala->quadra_id)
            ->where('autor_id', auth()->id())
            ->first();
    }

    #[Computed]
    public function avaliacoesParticipantes(): Collection
    {
        return Avaliacao::query()
            ->where('sala_id', $this->sala->id)
            ->where('autor_id', auth()->id())
            ->whereIn('avaliado_id', $this->participantesParaAvaliar()->pluck('id'))
            ->get()
            ->keyBy('avaliado_id');
    }

    /**
     * Demais participantes da sala, exceto o próprio usuário autenticado e o
     * administrador (que já tem seu próprio card de avaliação).
     */
    #[Computed]
    public function participantesParaAvaliar(): Collection
    {
        return $this->sala->participantes
            ->reject(fn (User $participante) => $participante->id === auth()->id() || $participante->id === $this->sala->criador_id)
            ->values();
    }

    public function abrirAvaliacao(string $alvo): void
    {
        $this->alvoAtivo = $alvo;
        $this->mensagemSucesso = null;
        $this->resetErrorBag();

        $avaliacaoExistente = match (true) {
            $alvo === 'administrador' => $this->avaliacaoAdministrador,
            $alvo === 'quadra' => $this->avaliacaoQuadra,
            str_starts_with($alvo, 'participante-') => $this->avaliacoesParticipantes->get((int) str_replace('participante-', '', $alvo)),
            default => null,
        };

        $this->nota = $avaliacaoExistente?->nota ?? 5;
        $this->comentario = $avaliacaoExistente?->comentario ?? '';
    }

    public function cancelarAvaliacao(): void
    {
        $this->alvoAtivo = null;
        $this->reset('nota', 'comentario');
        $this->resetErrorBag();
    }

    protected function rules(): array
    {
        return [
            'nota' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function avaliarAdministrador(): void
    {
        abort_if($this->sala->criador_id === auth()->id(), 403);

        $validated = $this->validate();

        Avaliacao::updateOrCreate(
            [
                'sala_id' => $this->sala->id,
                'avaliado_id' => $this->sala->criador_id,
                'autor_id' => auth()->id(),
            ],
            $validated,
        );

        unset($this->avaliacaoAdministrador);
        $this->finalizarAvaliacao('Avaliação do administrador enviada com sucesso.');
    }

    public function avaliarQuadra(): void
    {
        abort_unless($this->sala->quadra_id, 403);

        $validated = $this->validate();

        AvaliacaoQuadra::updateOrCreate(
            [
                'sala_id' => $this->sala->id,
                'quadra_id' => $this->sala->quadra_id,
                'autor_id' => auth()->id(),
            ],
            $validated,
        );

        unset($this->avaliacaoQuadra);
        $this->finalizarAvaliacao('Avaliação da quadra enviada com sucesso.');
    }

    public function avaliarParticipante(int $participanteId): void
    {
        abort_unless($this->participantesParaAvaliar()->contains('id', $participanteId), 403);

        $validated = $this->validate();

        Avaliacao::updateOrCreate(
            [
                'sala_id' => $this->sala->id,
                'avaliado_id' => $participanteId,
                'autor_id' => auth()->id(),
            ],
            $validated,
        );

        unset($this->avaliacoesParticipantes);
        $this->finalizarAvaliacao('Avaliação enviada com sucesso.');
    }

    private function finalizarAvaliacao(string $mensagem): void
    {
        $this->alvoAtivo = null;
        $this->reset('nota', 'comentario');
        $this->mensagemSucesso = $mensagem;
    }

    public function render()
    {
        return view('livewire.salas.avaliar');
    }
}
