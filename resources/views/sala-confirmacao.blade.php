@extends('layouts.bootstrap')

@section('titulo', 'Vaga confirmada — '.$sala->nome)

@section('conteudo')
    @include('partials.sub-nav')

    <div class="container my-5" style="max-width: 700px;">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background-color: #dcf5e3;">
                <i class="bi bi-check-lg" style="font-size: 2.5rem; color: #1ab000;"></i>
            </div>
            <h1 class="fw-bold texto-escuro mb-2">Vaga confirmada!</h1>
            <p class="text-muted">
                Você garantiu sua vaga na partida de
                {{ $sala->esporte->label() }} · {{ $sala->nivel_desejado?->label() ?? 'Todos os níveis' }}.
                Bom jogo!
            </p>
        </div>

        <div class="card border-0 shadow-sm card-arredondado p-4 mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-geo-alt text-warning"></i>
                <span>
                    @if ($sala->quadra)
                        {{ $sala->quadra->nome }} - {{ $sala->quadra->endereco }} - {{ $sala->quadra->cidade }}
                    @else
                        Local a definir
                    @endif
                </span>
            </div>

            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-clock text-warning"></i>
                <span>
                    @if ($sala->data && $sala->horario_inicio)
                        {{ $sala->data->isToday() ? 'Hoje' : $sala->data->format('d/m/Y') }},
                        {{ $sala->horario_inicio->format('H:i') }}
                        - {{ $sala->horario_fim?->format('H:i') }}
                        @if ($sala->duracaoFormatada())
                            ({{ $sala->duracaoFormatada() }})
                        @endif
                    @else
                        Horário a combinar
                    @endif
                </span>
            </div>

            <div class="d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-receipt text-warning"></i>
                <span>
                    Pago via {{ \App\Enums\FormaPagamento::from($participacao->pivot->forma_pagamento)->label() }}
                    · R$ {{ number_format($participacao->pivot->valor_pago, 2, ',', '.') }}
                </span>
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Jogadores confirmados</span>
                <span class="fw-bold">{{ $sala->participantes->count() }} de {{ $sala->max_participantes }}</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div
                    class="progress-bar"
                    role="progressbar"
                    style="width: {{ min(100, round($sala->participantes->count() / max($sala->max_participantes, 1) * 100)) }}%; background-color: #FF8C00;"
                ></div>
            </div>
        </div>

        <div class="d-flex gap-3">
            <button
                type="button"
                class="btn btn-outline-laranja fw-bold px-4 py-3 rounded-4"
                onclick="navigator.share ? navigator.share({ title: 'AlugaQuadra', url: '{{ route('salas.detalhes', $sala) }}' }) : navigator.clipboard.writeText('{{ route('salas.detalhes', $sala) }}')"
            >
                <i class="bi bi-share me-1"></i> Compartilhar
            </button>
            <a href="{{ route('salas.detalhes', $sala) }}" class="btn btn-laranja fw-bold px-4 py-3 rounded-4 flex-grow-1 text-center">
                Ver minha vaga
            </a>
        </div>
    </div>
@endsection
