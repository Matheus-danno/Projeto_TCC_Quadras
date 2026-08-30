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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Criar extends Component
{
    public string $nome = '';

    public string $esporte = '';

    public ?int $quadraId = null;

    public int $totalJogadores = 10;

    public int $maxParticipantes = 10;

    public string $data = '';

    public string $horaInicio = '';

    public int $duracaoMinutos = 90;

    public string $nivelDesejado = '';

    public string $aceitacaoNiveis = 'nenhum';

    public string $privacidade = 'publica';

    public string $aprovacao = 'automatica';

    public string $regrasAdicionais = '';

    public string $buscaQuadra = '';

    public ?float $userLat = null;

    public ?float $userLng = null;

    public function mount(): void
    {
        $this->data = now()->toDateString();
        $this->horaInicio = $this->proximoHorarioDisponivel();
        $this->nivelDesejado = NivelHabilidade::Intermediario->value;
    }

    public function usarLocalizacao(float $lat, float $lng): void
    {
        $this->userLat = $lat;
        $this->userLng = $lng;
    }

    /**
     * Primeiro horário disponível a partir de agora (quadras abrem 07h, fecham 22h),
     * usado como valor inicial do campo "Hora" para reduzir a chance de o usuário
     * esquecer de preenchê-lo.
     */
    private function proximoHorarioDisponivel(): string
    {
        $hora = max(7, min(21, (int) now()->addHour()->format('H')));

        return sprintf('%02d:00', $hora);
    }

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'esporte' => ['required', 'in:'.implode(',', array_column(Esporte::cases(), 'value'))],
            'quadraId' => ['required', 'integer', 'exists:quadras,id'],
            'data' => ['required', 'date', 'after_or_equal:today'],
            'horaInicio' => ['required', 'in:'.implode(',', $this->horariosDisponiveis())],
            'duracaoMinutos' => ['required', 'in:'.implode(',', $this->duracoesDisponiveis())],
            'totalJogadores' => ['required', 'integer', 'min:2', 'max:50'],
            'maxParticipantes' => ['required', 'integer', 'min:2', 'max:50', 'lte:totalJogadores'],
            'nivelDesejado' => ['required', 'in:'.implode(',', array_column(NivelHabilidade::cases(), 'value'))],
            'aceitacaoNiveis' => ['required', 'in:'.implode(',', array_column(AceitacaoNivel::cases(), 'value'))],
            'privacidade' => ['required', 'in:'.implode(',', array_column(Privacidade::cases(), 'value'))],
            'aprovacao' => ['required', 'in:'.implode(',', array_column(Aprovacao::cases(), 'value'))],
            'regrasAdicionais' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Horários de início disponíveis (quadras abrem 07h, fecham 22h).
     *
     * @return list<string>
     */
    public function horariosDisponiveis(): array
    {
        return collect(range(7, 21))
            ->map(fn (int $hora) => sprintf('%02d:00', $hora))
            ->all();
    }

    /**
     * Durações disponíveis para a partida, em minutos.
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

    #[Computed]
    public function quadras(): Collection
    {
        $quadras = Quadra::query()
            ->where('ativa', true)
            ->when($this->esporte, fn ($query) => $query->where('esporte', $this->esporte))
            ->when($this->buscaQuadra, fn ($query) => $query->where('nome', 'like', '%'.$this->buscaQuadra.'%'))
            ->with('fotos')
            ->orderBy('nome')
            ->get();

        if ($this->userLat !== null && $this->userLng !== null) {
            $quadras->each(function (Quadra $quadra) {
                $quadra->distanciaKm = $quadra->distanciaKmAte($this->userLat, $this->userLng);
            });
        }

        $indisponiveis = [];

        if ($this->data && $this->horaInicio) {
            [$horaInicioSql, $horaFimSql] = $this->janelaHorario();

            $indisponiveis = Reserva::query()
                ->whereIn('quadra_id', $quadras->pluck('id'))
                ->whereDate('data', $this->data)
                ->where('status', '!=', ReservaStatus::Cancelada->value)
                ->where('hora_inicio', '<', $horaFimSql)
                ->where('hora_fim', '>', $horaInicioSql)
                ->pluck('quadra_id')
                ->all();
        }

        $quadras->each(fn (Quadra $quadra) => $quadra->indisponivel = in_array($quadra->id, $indisponiveis, true));

        return $quadras;
    }

    #[Computed]
    public function resumoPartida(): array
    {
        $esporte = $this->esporte ? Esporte::from($this->esporte) : null;
        $nivel = $this->nivelDesejado ? NivelHabilidade::from($this->nivelDesejado) : null;
        $aceitacao = AceitacaoNivel::from($this->aceitacaoNiveis);
        $quadra = $this->quadraId ? $this->quadras->firstWhere('id', $this->quadraId) : null;

        $dataFormatada = $this->data ? Carbon::parse($this->data)->translatedFormat('d \d\e F \d\e Y') : '—';
        $horaFim = $this->horaInicio ? Carbon::parse($this->horaInicio.':00')->addMinutes($this->duracaoMinutos)->format('H:i') : '—';

        $valorHora = (float) ($quadra->valor_hora ?? 0);
        $horas = $this->duracaoMinutos / 60;
        $valorTotal = $valorHora * $horas;
        $valorPorPessoa = $this->maxParticipantes > 0 ? $valorTotal / $this->maxParticipantes : 0;

        return [
            'esporteNivel' => trim(($esporte?->label() ?? '—').($nivel ? ' - '.$nivel->label() : '')),
            'quadraEndereco' => $quadra ? "{$quadra->nome} - {$quadra->endereco}, {$quadra->bairro} - {$quadra->cidade}" : 'Quadra não selecionada',
            'dataHora' => $this->horaInicio ? "{$dataFormatada}, {$this->horaInicio} - {$horaFim} ({$this->duracaoMinutos} min)" : $dataFormatada,
            'jogadores' => "{$this->maxParticipantes} de {$this->totalJogadores} jogadores",
            'nivelAceitacao' => 'Nível: '.($nivel?->label() ?? '—')." ({$aceitacao->label()})",
            'privacidadeAprovacao' => Privacidade::from($this->privacidade)->label().' com '.Aprovacao::from($this->aprovacao)->label(),
            'valorTotal' => 'Valor total: R$ '.number_format($valorTotal, 2, ',', '.'),
            'valorPorPessoa' => 'R$ '.number_format($valorPorPessoa, 2, ',', '.').' / pessoa',
        ];
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

    public function aplicarFormato(string $tipo): void
    {
        if (! $this->esporte) {
            return;
        }

        $formato = $tipo === 'alternativo'
            ? Esporte::from($this->esporte)->formatoAlternativo()
            : Esporte::from($this->esporte)->formatoRecomendado();

        $this->totalJogadores = min(50, max(2, $formato['jogadores']));
        $this->maxParticipantes = $this->totalJogadores;
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
        $quadra = $this->quadras->firstWhere('id', $quadraId);

        if (! $quadra || $quadra->indisponivel) {
            return;
        }

        $this->quadraId = $this->quadraId === $quadraId ? null : $quadraId;
    }

    public function criar()
    {
        abort_unless(auth()->check(), 403);

        $validated = $this->validate();

        $quadra = Quadra::findOrFail($validated['quadraId']);

        [$horaInicioSql, $horaFimSql] = $this->janelaHorario();

        $conflito = Reserva::query()
            ->where('quadra_id', $quadra->id)
            ->whereDate('data', $validated['data'])
            ->where('status', '!=', ReservaStatus::Cancelada->value)
            ->where('hora_inicio', '<', $horaFimSql)
            ->where('hora_fim', '>', $horaInicioSql)
            ->exists();

        if ($conflito) {
            $this->addError('quadraId', 'Essa quadra já está reservada nesse horário.');

            return;
        }

        $horas = $validated['duracaoMinutos'] / 60;
        $valorTotal = (float) $quadra->valor_hora * $horas;
        $precoPessoa = $validated['maxParticipantes'] > 0 ? round($valorTotal / $validated['maxParticipantes'], 2) : null;

        $sala = DB::transaction(function () use ($validated, $quadra, $horaInicioSql, $horaFimSql, $precoPessoa) {
            $reserva = Reserva::create([
                'quadra_id' => $quadra->id,
                'user_id' => auth()->id(),
                'data' => $validated['data'],
                'hora_inicio' => $horaInicioSql,
                'hora_fim' => $horaFimSql,
                'status' => ReservaStatus::Confirmada,
            ]);

            $sala = Sala::create([
                'quadra_id' => $quadra->id,
                'criador_id' => auth()->id(),
                'nome' => $validated['nome'],
                'esporte' => $validated['esporte'],
                'max_participantes' => $validated['maxParticipantes'],
                'total_jogadores' => $validated['totalJogadores'],
                'data' => $validated['data'],
                'horario_inicio' => $horaInicioSql,
                'horario_fim' => $horaFimSql,
                'nivel_desejado' => $validated['nivelDesejado'],
                'aceitacao_niveis_adjacentes' => $validated['aceitacaoNiveis'],
                'privacidade' => $validated['privacidade'],
                'aprovacao' => $validated['aprovacao'],
                'preco_pessoa' => $precoPessoa,
                'regras_adicionais' => $validated['regrasAdicionais'] ?: null,
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

    /**
     * @return array{0: string, 1: string} [horaInicioSql, horaFimSql]
     */
    private function janelaHorario(): array
    {
        $horaInicioSql = $this->horaInicio.':00';
        $horaFimSql = Carbon::parse($horaInicioSql)->addMinutes($this->duracaoMinutos)->format('H:i:s');

        return [$horaInicioSql, $horaFimSql];
    }

    public function render()
    {
        return view('livewire.salas.criar', [
            'esportes' => Esporte::cases(),
            'niveis' => NivelHabilidade::cases(),
            'aceitacoes' => AceitacaoNivel::cases(),
            'privacidades' => Privacidade::cases(),
            'aprovacoes' => Aprovacao::cases(),
        ]);
    }
}
