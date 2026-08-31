<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuporteMensagem extends Model
{
    /** @use HasFactory<\Database\Factories\SuporteMensagemFactory> */
    use HasFactory;

    protected $table = 'suporte_mensagens';

    protected $fillable = [
        'user_id',
        'assunto',
        'mensagem',
        'respondida_em',
    ];

    protected function casts(): array
    {
        return [
            'respondida_em' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
