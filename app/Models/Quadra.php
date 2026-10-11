<?php

namespace App\Models;

use App\Enums\Esporte;
use App\Enums\ReservaStatus;
use App\Enums\StatusCadastro;
use App\Enums\TipoPiso;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Quadra extends Model
{
    /** @use HasFactory<\Database\Factories\QuadraFactory> */
    use HasFactory;

    /**
     * Fuso horário em que os horários de funcionamento das quadras (ex.: "09:00") são
     * interpretados. A aplicação roda com app.timezone = UTC, mas esses horários são
     * sempre pensados como horário local do Brasil, então não dá pra comparar com
     * now()/Carbon::now() diretamente sem especificar esse fuso.
     */
    private const FUSO_HORARIO = 'America/Sao_Paulo';

    protected $fillable = [
        'dono_id',
        'nome',
        'endereco',
        'cidade',
        'cep',
        'latitude',
        'longitude',
        'bairro',
        'esporte',
        'valor_hora',
        'capacidade_maxima',
        'cobertura',
        'amenidades',
        'ativa',
        'status_cadastro',
        'tipo_piso',
        'descricao',
    ];

    protected function casts(): array
    {
        return [
            'esporte' => Esporte::class,
            'valor_hora' => 'decimal:2',
            'capacidade_maxima' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'cobertura' => 'boolean',
            'amenidades' => 'array',
            'ativa' => 'boolean',
            'status_cadastro' => StatusCadastro::class,
            'tipo_piso' => TipoPiso::class,
            'checklist_aprovacao' => 'array',
        ];
    }

    /**
     * Campos considerados sensíveis: alterá-los numa quadra já aprovada
     * derruba o status de volta para "em análise". Preço, horários,
     * descrição, fotos etc. são alterações simples e não entram aqui.
     *
     * @var list<string>
     */
    public const CAMPOS_SENSIVEIS = ['endereco', 'bairro', 'cidade', 'cep', 'dono_id'];

    public function dono(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dono_id');
    }

    /**
     * Documentos enviados para esta quadra (comprovante de uso do imóvel).
     */
    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentavel');
    }

    /**
     * Histórico de envios/reenvios e decisões do fluxo de aprovação desta quadra.
     */
    public function historicoCadastro(): MorphMany
    {
        return $this->morphMany(CadastroHistorico::class, 'avaliavel')->latest();
    }

    /**
     * Se algum dos campos sensíveis (endereço, dono) mudaria com os dados
     * informados — usado para decidir se uma edição precisa voltar pra análise.
     *
     * @param  array<string, mixed>  $dadosNovos
     */
    public function camposSensiveisAlterados(array $dadosNovos): bool
    {
        foreach (self::CAMPOS_SENSIVEIS as $campo) {
            if (! array_key_exists($campo, $dadosNovos)) {
                continue;
            }

            if ((string) $this->getOriginal($campo) !== (string) $dadosNovos[$campo]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Quadras aprovadas pelo admin — as únicas que devem aparecer nas
     * listagens públicas (pendentes e suspensas ficam fora do ar).
     */
    public function scopeAprovadas($query)
    {
        return $query->where('status_cadastro', StatusCadastro::Aprovado);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function salas(): HasMany
    {
        return $this->hasMany(Sala::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(QuadraFoto::class)->orderBy('ordem');
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(AvaliacaoQuadra::class);
    }

    /**
     * Nota média das avaliações recebidas pela quadra, arredondada a 1 casa decimal.
     */
    public function notaMedia(): ?float
    {
        $media = $this->avaliacoes()->avg('nota');

        return $media !== null ? round($media, 1) : null;
    }

    public function fotoCapa(): ?QuadraFoto
    {
        return $this->fotos->firstWhere('capa', true) ?? $this->fotos->first();
    }

    public function temReservaFutura(): bool
    {
        return $this->reservas()
            ->whereDate('data', '>=', now()->toDateString())
            ->where('status', '!=', ReservaStatus::Cancelada->value)
            ->exists();
    }

    /**
     * Distância em quilômetros até um ponto (lat/lng), via fórmula de Haversine.
     * Retorna null se a quadra não tiver coordenadas cadastradas.
     */
    public function distanciaKmAte(float $latitude, float $longitude): ?float
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        $raioTerraKm = 6371;

        $deltaLat = deg2rad((float) $this->latitude - $latitude);
        $deltaLng = deg2rad((float) $this->longitude - $longitude);

        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad((float) $this->latitude)) * sin($deltaLng / 2) ** 2;

        $c = 2 * asin(min(1, sqrt($a)));

        return round($raioTerraKm * $c, 1);
    }

    /**
     * Lista de imagens da quadra. Usa as fotos cadastradas ou, na ausência delas,
     * as imagens padrão do esporte (permitindo o carrossel mesmo sem fotos reais).
     *
     * @return list<string>
     */
    public function listaImagens(): array
    {
        return $this->fotos->isNotEmpty()
            ? $this->fotos->map(fn (QuadraFoto $foto) => $foto->url())->all()
            : array_values(array_filter([$this->esporte->imagem(), $this->esporte->imagemBola()]));
    }

    /**
     * Lista de características da quadra (cobertura + comodidades cadastradas).
     *
     * @return list<string>
     */
    public function listaAmenidades(): array
    {
        return array_filter([
            $this->cobertura ? 'Coberta' : 'Descoberta',
            ...($this->amenidades ?? []),
        ]);
    }

    /**
     * Se a quadra está livre nesse intervalo: dentro do horário de funcionamento do dono
     * nessa data (considerando pausa e exceções cadastradas) e sem nenhuma reserva não
     * cancelada que sobreponha o intervalo.
     */
    public function horarioDisponivel(string $data, string $horaInicio, string $horaFim, ?int $ignorarReservaId = null): bool
    {
        if (Carbon::parse($data.' '.$horaInicio, self::FUSO_HORARIO)->isPast()) {
            return false;
        }

        $funcionamento = $this->dono?->horarioFuncionamentoEm($data);

        if (! $funcionamento) {
            return false;
        }

        if ($horaInicio < $funcionamento['abertura'].':00' || $horaFim > $funcionamento['fechamento'].':00') {
            return false;
        }

        return ! $this->reservas()
            ->whereDate('data', $data)
            ->where('status', '!=', ReservaStatus::Cancelada->value)
            ->where('hora_inicio', '<', $horaFim)
            ->where('hora_fim', '>', $horaInicio)
            ->when($ignorarReservaId, fn ($query) => $query->where('id', '!=', $ignorarReservaId))
            ->exists();
    }

    /**
     * Lista os horários de início (formato "HH:MM", de hora em hora a partir da abertura)
     * em que esta quadra está livre nesta data para uma partida com a duração informada,
     * considerando o horário de funcionamento do dono e as reservas já existentes.
     *
     * @return list<string>
     */
    public function horariosLivres(string $data, int $duracaoMinutos = 60): array
    {
        $funcionamento = $this->dono?->horarioFuncionamentoEm($data);

        if (! $funcionamento) {
            return [];
        }

        [$horaAbertura, $minAbertura] = array_pad(array_map('intval', explode(':', $funcionamento['abertura'])), 2, 0);
        [$horaFechamento, $minFechamento] = array_pad(array_map('intval', explode(':', $funcionamento['fechamento'])), 2, 0);

        $inicioMinutos = $horaAbertura * 60 + $minAbertura;
        $fimMinutos = $horaFechamento * 60 + $minFechamento;

        $candidatos = [];
        for ($minutos = $inicioMinutos; $minutos + $duracaoMinutos <= $fimMinutos; $minutos += 60) {
            $candidatos[] = sprintf('%02d:%02d', intdiv($minutos, 60), $minutos % 60);
        }

        $candidatos = array_values(array_filter(
            $candidatos,
            fn (string $horario) => ! Carbon::parse($data.' '.$horario, self::FUSO_HORARIO)->isPast()
        ));

        if ($candidatos === []) {
            return [];
        }

        $reservas = $this->reservas()
            ->whereDate('data', $data)
            ->where('status', '!=', ReservaStatus::Cancelada->value)
            ->get(['hora_inicio', 'hora_fim']);

        return array_values(array_filter($candidatos, function (string $horario) use ($duracaoMinutos, $reservas) {
            $inicio = $horario.':00';
            $fim = date('H:i:s', strtotime($inicio.' +'.$duracaoMinutos.' minutes'));

            return ! $reservas->contains(
                fn (Reserva $reserva) => $reserva->hora_inicio < $fim && $reserva->hora_fim > $inicio
            );
        }));
    }
}
