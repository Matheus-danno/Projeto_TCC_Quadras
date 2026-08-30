<?php

namespace App\Livewire\Salas;

use App\Enums\AceitacaoNivel;
use App\Enums\Esporte;
use App\Enums\NivelHabilidade;
use App\Enums\ReservaStatus;
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

    public int $maxParticipantes = 10;

    public string $data = '';

    public string $horaInicio = '';

    public int $duracaoMinutos = 90;

    public int $quantidadeHoras = 2;

    public string $nivelDesejado = '';

    public string $aceitacaoNiveis = 'nenhum';

    public bool $privada = false;

    public bool $aprovacaoManual = false;

    public string $regrasAdicionais = '';

    public string $buscaQuadra = '';

    /**
     * Quantidade de horas escolhida em cada card de quadra, independente por quadra
     * (`quadra_id => horas`), para que ajustar uma não afete as demais.
     *
     * @var array<int, int>
     */
    public array $horasPorQuadra = [];

    public function mount(): void
    {
        $this->data = now()->toDateString();
        $this->horaInicio = $this->proximoHorarioDisponivel();
        $this->nivelDesejado = NivelHabilidade::Intermediario->value;
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
            'duracaoMinutos' => ['required', 'integer', 'min:30', 'max:240'],
            'maxParticipantes' => ['required', 'integer', 'min:2', 'max:50'],
            'nivelDesejado' => ['required', 'in:'.implode(',', array_column(NivelHabilidade::cases(), 'value'))],
            'aceitacaoNiveis' => ['required', 'in:'.implode(',', array_column(AceitacaoNivel::cases(), 'value'))],
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
     * Durações disponíveis para a partida, em minutos => rótulo.
     *
     * @return array<int, string>
     */
    public function duracoesDisponiveis(): array
    {
        return [
            60 => '1h',
            90 => '1h 30min',
            120 => '2h',
            150 => '2h 30min',
            180 => '3h',
            210 => '3h 30min',
            240 => '4h',
        ];
    }

    #[Computed]
    public function quadras(): Collection
    {
        $quadras = Quadra::query()
            ->where('ativa', true)
            ->when($this->buscaQuadra, fn ($query) => $query->where('nome', 'like', '%'.$this->buscaQuadra.'%'))
            ->with('fotos')
            ->orderBy('nome')
            ->get();

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
        $valorTotal = $valorHora * $this->quantidadeHoras;
        $valorPorPessoa = $this->maxParticipantes > 0 ? $valorTotal / $this->maxParticipantes : 0;

        return [
            'esporteNivel' => trim(($esporte?->label() ?? '—').($nivel ? ' - '.$nivel->label() : '')),
            'quadraEndereco' => $quadra ? "{$quadra->nome} - {$quadra->endereco}, {$quadra->bairro} - {$quadra->cidade}" : 'Quadra não selecionada',
            'dataHora' => $this->horaInicio ? "{$dataFormatada}, {$this->horaInicio} - {$horaFim} ({$this->duracaoMinutos} min)" : $dataFormatada,
            'jogadores' => "{$this->maxParticipantes} jogadores",
            'nivelAceitacao' => 'Nível: '.($nivel?->label() ?? '—')." ({$aceitacao->label()})",
            'privacidadeAprovacao' => ($this->privada ? 'Sala privada' : 'Sala pública').' com '.($this->aprovacaoManual ? 'aprovação manual' : 'entrada automática'),
            'valorTotal' => 'Valor total: R$ '.number_format($valorTotal, 2, ',', '.'),
            'valorPorPessoa' => 'R$ '.number_format($valorPorPessoa, 2, ',', '.').' / pessoa',
        ];
    }

    public function incrementarVagas(): void
    {
        $this->maxParticipantes = min(50, $this->maxParticipantes + 1);
    }

    public function decrementarVagas(): void
    {
        $this->maxParticipantes = max(2, $this->maxParticipantes - 1);
    }

    /**
     * Quantidade de horas escolhida para uma quadra específica (independente das demais).
     */
    public function horasPara(int $quadraId): int
    {
        return $this->horasPorQuadra[$quadraId] ?? max(1, (int) ceil($this->duracaoMinutos / 60));
    }

    public function incrementarHoras(int $quadraId): void
    {
        $this->horasPorQuadra[$quadraId] = min(6, $this->horasPara($quadraId) + 1);
        $this->sincronizarHorasSelecionadas($quadraId);
    }

    public function decrementarHoras(int $quadraId): void
    {
        $this->horasPorQuadra[$quadraId] = max(1, $this->horasPara($quadraId) - 1);
        $this->sincronizarHorasSelecionadas($quadraId);
    }

    /**
     * Quando a quadra ajustada é a que está atualmente selecionada, propaga a
     * quantidade de horas para os campos que definem a duração real da partida.
     */
    private function sincronizarHorasSelecionadas(int $quadraId): void
    {
        if ($quadraId !== $this->quadraId) {
            return;
        }

        $this->quantidadeHoras = $this->horasPorQuadra[$quadraId];
        $this->duracaoMinutos = $this->quantidadeHoras * 60;
    }

    public function updatedDuracaoMinutos(): void
    {
        $this->quantidadeHoras = max(1, (int) ceil($this->duracaoMinutos / 60));

        if ($this->quadraId) {
            $this->horasPorQuadra[$this->quadraId] = $this->quantidadeHoras;
        }
    }

    public function aplicarFormato(string $tipo): void
    {
        if (! $this->esporte) {
            return;
        }

        $formato = $tipo === 'alternativo'
            ? Esporte::from($this->esporte)->formatoAlternativo()
            : Esporte::from($this->esporte)->formatoRecomendado();

        $this->maxParticipantes = min(50, max(2, $formato['jogadores']));
    }

    public function selecionarQuadra(int $quadraId): void
    {
        $quadra = $this->quadras->firstWhere('id', $quadraId);

        if (! $quadra || $quadra->indisponivel) {
            return;
        }

        $this->quadraId = $quadraId;
        $this->quantidadeHoras = $this->horasPara($quadraId);
        $this->duracaoMinutos = $this->quantidadeHoras * 60;
    }

    public function criar(): void
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
            $this->addError('horaInicio', 'Essa quadra já está reservada nesse horário.');

            return;
        }

        $sala = DB::transaction(function () use ($validated, $quadra, $horaInicioSql, $horaFimSql) {
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
                'data' => $validated['data'],
                'hora_inicio' => $horaInicioSql,
                'duracao_minutos' => $validated['duracaoMinutos'],
                'quantidade_horas' => $this->quantidadeHoras,
                'nivel_desejado' => $validated['nivelDesejado'],
                'aceitacao_niveis_adjacentes' => $validated['aceitacaoNiveis'],
                'privada' => $this->privada,
                'aprovacao_manual' => $this->aprovacaoManual,
                'regras_adicionais' => $validated['regrasAdicionais'] ?: null,
                'reserva_id' => $reserva->id,
            ]);

            $sala->participantes()->attach(auth()->id());

            return $sala;
        });

        session()->flash('sala-criada', "Sala \"{$sala->nome}\" criada com sucesso!");

        $this->redirect(route('encontre_time'), navigate: false);
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
        ]);
    }
}
