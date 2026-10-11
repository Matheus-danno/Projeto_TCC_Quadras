<?php

namespace App\Models;

use App\Enums\StatusCadastro;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CadastroHistorico extends Model
{
    protected $fillable = [
        'avaliavel_type',
        'avaliavel_id',
        'evento',
        'status_anterior',
        'status_novo',
        'motivo',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'status_anterior' => StatusCadastro::class,
            'status_novo' => StatusCadastro::class,
        ];
    }

    public function avaliavel(): MorphTo
    {
        return $this->morphTo();
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Rótulo legível do evento, para a linha do tempo exibida na tela de análise.
     */
    public function rotuloEvento(): string
    {
        return match ($this->evento) {
            'enviado' => 'Cadastro enviado',
            'reenviado' => 'Cadastro reenviado',
            'em_analise' => 'Análise iniciada',
            'ajustes_solicitados' => 'Ajustes solicitados',
            'aprovado' => 'Aprovado',
            'rejeitado' => 'Rejeitado',
            'suspenso' => 'Suspenso',
            'reativado' => 'Reativado',
            default => $this->evento,
        };
    }
}
