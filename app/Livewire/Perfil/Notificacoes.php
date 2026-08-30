<?php

namespace App\Livewire\Perfil;

use App\Enums\SalaStatus;
use App\Models\Quadra;
use App\Models\Sala;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Notificacoes extends Component
{
    /**
     * Percentual abaixo da média de valor/hora das quadras ativas para uma
     * quadra ser considerada "barata" e virar notificação.
     */
    private const LIMIAR_ABAIXO_DA_MEDIA = 0.8;

    /**
     * Minutos até o início da partida a partir dos quais avisamos o organizador
     * que a sala está perto de fechar (5 horas, mesmo prazo de cancelamento).
     */
    private const MINUTOS_SALA_FECHANDO = 300;

    #[Computed]
    public function notificacoes(): Collection
    {
        return $this->salasFechando()->concat($this->quadrasAbaixoDaMedia());
    }

    /**
     * Salas que o usuário organiza, ainda com vagas, a menos de 5h do início:
     * lembrete para fechar a sala e garantir a partida pagando a diferença.
     */
    private function salasFechando(): Collection
    {
        return Sala::query()
            ->where('criador_id', Auth::id())
            ->where('status', SalaStatus::Aberta)
            ->with('quadra')
            ->get()
            ->filter(function (Sala $sala) {
                $minutos = $sala->minutosParaComeco();

                return $sala->podeFecharComVagas() && $minutos !== null && $minutos <= self::MINUTOS_SALA_FECHANDO;
            })
            ->map(fn (Sala $sala) => [
                'tipo' => 'sala_fechando',
                'icone' => 'bi-clock-history',
                'titulo' => 'Sua sala fecha em breve',
                'mensagem' => sprintf(
                    'Faltam %s para o início de "%s" e ainda há vagas abertas. Pague R$ %s para garantir a partida com os jogadores confirmados.',
                    $sala->tempoParaComecoFormatado(),
                    $sala->quadra?->nome ?? $sala->esporte->label(),
                    number_format($sala->diferencaParaFechar(), 2, ',', '.')
                ),
                'link' => route('salas.grupo', $sala),
                'linkTexto' => 'Ver sala',
            ]);
    }

    /**
     * Quadras ativas com valor/hora bem abaixo da média das demais quadras
     * ativas: aviso de oportunidade para o jogador.
     */
    private function quadrasAbaixoDaMedia(): Collection
    {
        $media = Quadra::where('ativa', true)->avg('valor_hora');

        if ($media === null) {
            return collect();
        }

        return Quadra::where('ativa', true)
            ->where('valor_hora', '<', $media * self::LIMIAR_ABAIXO_DA_MEDIA)
            ->orderBy('valor_hora')
            ->take(3)
            ->get()
            ->map(fn (Quadra $quadra) => [
                'tipo' => 'quadra_barata',
                'icone' => 'bi-tag',
                'titulo' => 'Quadra com valor abaixo da média',
                'mensagem' => sprintf(
                    'A quadra "%s" em %s está R$ %s/hora, abaixo da média de R$ %s/hora das quadras ativas.',
                    $quadra->nome,
                    $quadra->cidade,
                    number_format($quadra->valor_hora, 2, ',', '.'),
                    number_format($media, 2, ',', '.')
                ),
                'link' => route('quadras.index', ['busca' => $quadra->nome]),
                'linkTexto' => 'Ver quadra',
            ]);
    }

    public function render()
    {
        return view('livewire.perfil.notificacoes');
    }
}
