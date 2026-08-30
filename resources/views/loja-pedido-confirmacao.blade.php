@extends('layouts.bootstrap')

@section('titulo', 'Pedido Confirmado — Loja AlugaQuadra')

@section('conteudo')

@include('partials.sub-nav')

<div class="container my-5" style="max-width: 720px;">
    <div class="text-center mb-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
        <h1 class="fw-bold mt-3 mb-1">Pedido confirmado!</h1>
        <p class="text-muted mb-0">Pedido #{{ $pedido->id }} — {{ $pedido->created_at->format('d/m/Y \à\s H:i') }}</p>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Itens do pedido</h6>
                <span class="badge rounded-pill px-3 {{ match ($pedido->status->value) {
                    'confirmado' => 'bg-success',
                    'pendente' => 'bg-warning text-dark',
                    'cancelado' => 'bg-secondary',
                } }} text-white">{{ $pedido->status->label() }}</span>
            </div>

            <div class="d-flex flex-column gap-3 mb-3">
                @foreach ($pedido->itens as $item)
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
                        <div>
                            <h6 class="fw-bold mb-1">{{ $item->produto->nome }}</h6>
                            <p class="text-muted small mb-0">
                                {{ $item->quantidade }} un. × R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}
                            </p>
                        </div>
                        <span class="fw-bold text-laranja-loja">
                            R$ {{ number_format($item->quantidade * $item->preco_unitario, 2, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold">Total</span>
                <span class="fw-bold fs-4 text-laranja-loja">R$ {{ number_format($pedido->total, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('loja') }}" class="btn produto-btn-add px-4">
            <i class="bi bi-shop me-1"></i> Voltar para a loja
        </a>
    </div>
</div>

@endsection
