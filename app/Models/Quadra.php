<?php

namespace App\Models;

use App\Enums\Esporte;
use App\Enums\ReservaStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quadra extends Model
{
    /** @use HasFactory<\Database\Factories\QuadraFactory> */
    use HasFactory;

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
        ];
    }

    public function dono(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dono_id');
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
     * Se a quadra está livre nesse intervalo (mesma regra usada na reserva avulsa de quadras):
     * nenhuma reserva não cancelada com sobreposição de horário na mesma data.
     */
    public function horarioDisponivel(string $data, string $horaInicio, string $horaFim, ?int $ignorarReservaId = null): bool
    {
        return ! $this->reservas()
            ->whereDate('data', $data)
            ->where('status', '!=', ReservaStatus::Cancelada->value)
            ->where('hora_inicio', '<', $horaFim)
            ->where('hora_fim', '>', $horaInicio)
            ->when($ignorarReservaId, fn ($query) => $query->where('id', '!=', $ignorarReservaId))
            ->exists();
    }
}
