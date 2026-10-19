<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticacaoUsuario extends Model
{
    /** @use HasFactory<\Database\Factories\NoticacaoUsuarioFactory> */
    use HasFactory;

    protected $table = 'noticacao_usuarios';

    protected $fillable = [
        'notif_confirmacao_reserva',
        'notif_lembrete_horario',
        'notif_novo_jogador_sala',
        'notif_mensagens_grupo',
        'notif_ofertas_novidades',
        'notif_dono_dias_uteis',
        'notif_dono_cancelamento',
        'notif_dono_mensagens_clientes',
    ];

    protected function casts(): array
    {
        return [
            'notif_confirmacao_reserva' => 'boolean',
            'notif_lembrete_horario' => 'boolean',
            'notif_novo_jogador_sala' => 'boolean',
            'notif_mensagens_grupo' => 'boolean',
            'notif_ofertas_novidades' => 'boolean',
            'notif_dono_dias_uteis' => 'boolean',
            'notif_dono_cancelamento' => 'boolean',
            'notif_dono_mensagens_clientes' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
