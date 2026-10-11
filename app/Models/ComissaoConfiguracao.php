<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComissaoConfiguracao extends Model
{
    protected $table = 'comissao_configuracoes';

    protected $fillable = [
        'percentual',
        'alterado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'percentual' => 'decimal:2',
        ];
    }

    public function alteradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'alterado_por_id');
    }

    /**
     * Percentual de comissão vigente agora: o da configuração mais recente,
     * ou o valor padrão (config/comissao.php) se nenhuma tiver sido criada ainda.
     */
    public static function percentualAtual(): float
    {
        $percentual = static::query()->latest('id')->value('percentual');

        return $percentual !== null ? (float) $percentual : (float) config('comissao.percentual_padrao');
    }

    /**
     * Registra uma nova porcentagem de comissão, passando a valer a partir
     * de agora (reservas já com comissão calculada não são afetadas).
     */
    public static function definirPercentual(float $percentual, ?User $alteradoPor): self
    {
        return static::create([
            'percentual' => $percentual,
            'alterado_por_id' => $alteradoPor?->id,
        ]);
    }
}
