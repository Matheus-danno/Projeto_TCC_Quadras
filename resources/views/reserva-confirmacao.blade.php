@extends('layouts.bootstrap')

@section('titulo', 'Reserva Confirmada')

@section('conteudo')
    @include('partials.sub-nav')

    <div class="container mb-5" style="max-width: 700px;">
        <div class="card border-0 shadow-sm card-arredondado mt-4">
            <div class="card-body p-5 text-center">
                <div class="confirmacao-icone-sucesso mx-auto mb-3">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h3 class="fw-bold mb-2">Reserva confirmada!</h3>
                <p class="text-muted mb-4">Sua reserva foi confirmada e o pagamento foi processado com sucesso.</p>

                <div class="card border-0 bg-light card-arredondado text-start">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="detalhes-subtitulo mb-0">Código da reserva</span>
                            <span class="fw-semibold">{{ $reserva->codigoReserva() }}</span>
                        </div>
                        <hr class="detalhes-linha">

                        <p class="fw-bold texto-jogo mb-0">{{ $reserva->quadra->nome }}</p>
                        <p class="text-muted small mb-3">{{ $reserva->quadra->esporte->label() }} · {{ $reserva->quadra->cobertura ? 'Coberta' : 'Descoberta' }}</p>

                        <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-2"></i>{{ $reserva->quadra->endereco }} - {{ $reserva->quadra->bairro }}, {{ $reserva->quadra->cidade }}</p>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-clock me-2"></i>
                            {{ $reserva->data->isToday() ? 'Hoje' : $reserva->data->format('d/m/Y') }},
                            {{ \Illuminate\Support\Carbon::parse($reserva->hora_inicio)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($reserva->hora_fim)->format('H:i') }}
                            @if ($reserva->duracaoFormatada())
                                ({{ $reserva->duracaoFormatada() }})
                            @endif
                        </p>
                        <p class="text-muted small mb-3">
                            @if ($reserva->metodo_pagamento === 'pix')
                                <i class="bi bi-qr-code me-2"></i>Pago via Pix
                            @elseif ($reserva->metodo_pagamento === 'credito')
                                <i class="bi bi-wallet2 me-2"></i>Pago com créditos
                            @else
                                <i class="bi bi-credit-card me-2"></i>Pago via Cartão de Crédito
                            @endif
                        </p>

                        <hr class="detalhes-linha">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="detalhes-subtitulo mb-0">Valor pago</span>
                            <span class="fw-bold text-orange fs-4">R$ {{ number_format($reserva->quadra->valor_hora ?? 0, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('quadras.index') }}" class="btn btn-laranja fw-bold w-100 py-2 mt-4">Voltar para Quadras</a>
            </div>
        </div>
    </div>
@endsection
