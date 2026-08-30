<?php

namespace App\Livewire\Salas;

use App\Enums\AceitacaoNivel;
use App\Enums\Aprovacao;
use App\Enums\Esporte;
use App\Enums\NivelHabilidade;
use App\Enums\Privacidade;
use App\Enums\ReservaStatus;
use App\Models\AtividadeSala;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\Sala;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Criar extends Component
{
    public string $esporte = '';

    public string $data = '';

    public string $horaInicio = '20:00';

    public int $duracaoMinutos = 90;

    public int $totalJogadores = 10;

    public int $maxParticipantes = 10;

    public string $nivel = 'intermediario';

    public string $nivelFlexibilidade = 'todos';

    public ?int $quadraId = null;

    public string $privacidade = 'publica';

    public string $aprovacao = 'automatica';

    public string $regrasAdicionais = '';

    public string $buscaQuadra = '';

    public ?float $userLat = null;

    public ?float $userLng = null;

    public function mount(): void
    {
        $this->data = now()->toDateString();
    }

    public function usarLocalizacao(float $lat, float $lng): void
    {
        $this->userLat = $lat;
        $this->userLng = $lng;
    }

    /**
     * Opções de duração da partida, em minutos.
     *
     * @return list<int>
     */
    public function duracoesDisponiveis(): array
    {
        return [60, 90, 120, 150, 180];
    }

    public function duracaoFormatada(int $minutos): string
    {
        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;

        return $resto > 0 ? "{$horas}h {$resto}min" : "{$horas}h";
    }

    /**
     * Horários de início disponíveis para a partida.
     *
     * @return list<string>
     */
    public function horariosDisponiveis(): array
    {
        return collect(range(7, 21))
            ->map(fn (int $hora) => sprintf('%02d:00', $hora))
            ->all();
    }

    public function updatedEsporte(): void
    {
        if ($this->esporte === '') {
            return;
        }

        $jogadores = Esporte::from($this->esporte)->formatoRecomendado()['jogadores'];
        $this->totalJogadores = $jogadores;
        $this->maxParticipantes = $jogadores;
        $this->quadraId = null;
    }

    public function selecionarFormato(string $tipo): void
    {
        if ($this->esporte === '') {
            return;
        }

        $esporte = Esporte::from($this->esporte);

        $formato = $tipo === 'alternativo'
            ? $esporte->formatoAlternativo()
            : $esporte->formatoRecomendado();

        $this->totalJogadores = $formato['jogadores'];
        $this->maxParticipantes = $formato['jogadores'];
    }

    public function incrementarTotalJogadores(): void
    {
        $this->totalJogadores = min(50, $this->totalJogadores + 1);
    }

    public function decrementarTotalJogadores(): void
    {
        $this->totalJogadores = max(2, $this->totalJogadores - 1);
        $this->maxParticipantes = min($this->maxParticipantes, $this->totalJogadores);
    }

    public function incrementarVagas(): void
    {
        $this->maxParticipantes = min($this->totalJogadores, $this->maxParticipantes + 1);
    }

    public function decrementarVagas(): void
    {
        $this->maxParticipantes = max(2, $this->maxParticipantes - 1);
    }

    public function selecionarQuadra(int $quadraId): void
    {
        $this->quadraId = $this->quadraId === $quadraId ? null : $quadraId;
    }

    /**
     * Fim do intervalo (partida e reserva da quadra), a partir do horário de início e da duração.
     */
    private function calcularHoraFim(): string
    {
        return date('H:i:s', strtotime($this->horaInicio.' +'.$this->duracaoMinutos.' minutes'));
    }

    #[Computed]
    public function quadras(): Collection
    {
        $quadras = Quadra::query()
            ->where('ativa', true)
            ->when($this->esporte, fn ($query) => $query->where('esporte', $this->esporte))
            ->when($this->buscaQuadra, fn ($query) => $query->where('nome', 'like', '%'.$this->buscaQuadra.'%'))
            ->orderBy('nome')
            ->get();

        if ($this->userLat !== null && $this->userLng !== null) {
            $quadras->each(function (Quadra $quadra) {
                $quadra->distanciaKm = $quadra->distanciaKmAte($this->userLat, $this->userLng);
            });
        }

        if ($this->data === '' || $this->horaInicio === '') {
            return $quadras;
        }

        $horaFim = $this->calcularHoraFim();

        return $quadras->map(function (Quadra $quadra) use ($horaFim) {
            $quadra->disponivel = $quadra->horarioDisponivel($this->data, $this->horaInicio.':00', $horaFim);

            return $quadra;
        });
    }

    #[Computed]
    public function quadraSelecionada(): ?Quadra
    {
        return $this->quadraId ? $this->quadras->firstWhere('id', $this->quadraId) : null;
    }

    public function precoPessoa(): ?float
    {
        if (! $this->quadraSelecionada || $this->maxParticipantes <= 0) {
            return null;
        }

        $horas = $this->duracaoMinutos / 60;

        return round(((float) $this->quadraSelecionada->valor_hora * $horas) / $this->maxParticipantes, 2);
    }

    public function totalArrecadar(): ?float
    {
        $precoPessoa = $this->precoPessoa();

        return $precoPessoa !== null ? round($precoPessoa * $this->maxParticipantes, 2) : null;
    }

    public function criar()
    {
        abort_unless(auth()->check(), 403);

        $validated = $this->validate([
            'esporte' => ['required', 'in:'.implode(',', array_column(Esporte::cases(), 'value'))],
            'data' => ['required', 'date', 'after_or_equal:today'],
            'horaInicio' => ['required', 'in:'.implode(',', $this->horariosDisponiveis())],
            'duracaoMinutos' => ['required', 'in:'.implode(',', $this->duracoesDisponiveis())],
            'totalJogadores' => ['required', 'integer', 'min:2', 'max:50'],
            'maxParticipantes' => ['required', 'integer', 'min:2', 'max:50', 'lte:totalJogadores'],
            'nivel' => ['required', 'in:'.implode(',', array_column(NivelHabilidade::cases(), 'value'))],
            'nivelFlexibilidade' => ['required', 'in:'.implode(',', array_column(AceitacaoNivel::cases(), 'value'))],
            'quadraId' => ['required', 'exists:quadras,id'],
            'privacidade' => ['required', 'in:'.implode(',', array_column(Privacidade::cases(), 'value'))],
            'aprovacao' => ['required', 'in:'.implode(',', array_column(Aprovacao::cases(), 'value'))],
            'regrasAdicionais' => ['nullable', 'string', 'max:1000'],
        ]);

        $quadra = Quadra::findOrFail($validated['quadraId']);

        $horaInicio = $validated['horaInicio'].':00';
        $horaFim = $this->calcularHoraFim();

        if (! $quadra->horarioDisponivel($validated['data'], $horaInicio, $horaFim)) {
            $this->addError('quadraId', 'Essa quadra não está mais disponível nesse horário.');

            return null;
        }

        $esporte = Esporte::from($validated['esporte']);
        $nivel = NivelHabilidade::from($validated['nivel']);
        $precoPessoa = $this->precoPessoa();

        $sala = DB::transaction(function () use ($validated, $quadra, $horaInicio, $horaFim, $esporte, $nivel, $precoPessoa) {
            $reserva = Reserva::create([
                'quadra_id' => $quadra->id,
                'user_id' => auth()->id(),
                'data' => $validated['data'],
                'hora_inicio' => $horaInicio,
                'hora_fim' => $horaFim,
                'status' => ReservaStatus::Confirmada,
            ]);

            $sala = Sala::create([
                'nome' => "{$esporte->label()} - {$nivel->label()}",
                'esporte' => $validated['esporte'],
                'nivel_desejado' => $validated['nivel'],
                'aceitacao_niveis_adjacentes' => $validated['nivelFlexibilidade'],
                'quadra_id' => $quadra->id,
                'criador_id' => auth()->id(),
                'max_participantes' => $validated['maxParticipantes'],
                'total_jogadores' => $validated['totalJogadores'],
                'data' => $validated['data'],
                'horario_inicio' => $horaInicio,
                'horario_fim' => $horaFim,
                'privacidade' => $validated['privacidade'],
                'aprovacao' => $validated['aprovacao'],
                'regras_adicionais' => $validated['regrasAdicionais'] ?: null,
                'preco_pessoa' => $precoPessoa,
                'reserva_id' => $reserva->id,
            ]);

            AtividadeSala::create([
                'sala_id' => $sala->id,
                'user_id' => auth()->id(),
                'descricao' => 'Sala criada por '.auth()->user()->name,
            ]);

            return $sala;
        });

        return $this->redirect(route('salas.pagamento', $sala), navigate: false);
    }

    public function render()
    {
        return view('livewire.salas.criar', [
            'esportes' => array_values(array_filter(Esporte::cases(), fn (Esporte $e) => $e !== Esporte::Tenis)),
            'niveis' => NivelHabilidade::cases(),
            'niveisFlexibilidade' => AceitacaoNivel::cases(),
        ]);
    }
}
