@extends('layouts.bootstrap')

@section('titulo', 'Nota de Compra #'.$pedido->numero_retirada.' — Loja AlugaQuadra')

@section('conteudo')

<div class="d-print-none">
    @include('partials.sub-nav')
</div>

<style>
    @media print {
        .d-print-none { display: none !important; }
        .container { max-width: 100% !important; }
    }
</style>

<div class="container my-5" style="max-width: 640px;">
    <div class="d-flex justify-content-between align-items-start mb-4 d-print-none">
        <a href="{{ route('loja.pedido.confirmacao', $pedido) }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
        <button type="button" class="btn btn-sm produto-btn-add px-3" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Imprimir / Salvar PDF
        </button>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Nota de Compra</h4>
                    <p class="text-muted small mb-0">Pedido #{{ $pedido->id }} · {{ $pedido->created_at->format('d/m/Y \à\s H:i') }}</p>
                </div>
                <span class="badge rounded-pill px-3 py-2 {{ match ($pedido->status) {
                    App\Enums\PedidoStatus::Retirado => 'bg-success',
                    App\Enums\PedidoStatus::Aguardando => 'bg-warning text-dark',
                    App\Enums\PedidoStatus::Cancelado => 'bg-secondary',
                } }} text-white">{{ $pedido->status->label() }}</span>
            </div>

            <div class="row gy-4 mb-4">
                <div class="col-sm-6">
                    <p class="text-uppercase text-muted small fw-bold mb-1" style="letter-spacing: 0.05em;">Vendido por</p>
                    <p class="fw-bold mb-0">{{ $pedido->dono->nome_estabelecimento ?? $pedido->dono->name }}</p>
                    @if ($pedido->dono->cnpj)
                        <p class="text-muted small mb-0">CNPJ {{ $pedido->dono->cnpjFormatado() }}</p>
                    @endif
                    <p class="text-muted small mb-0">{{ $pedido->dono->endereco?->linha() }}, {{ $pedido->dono->endereco?->cidade }} - {{ $pedido->dono->endereco?->estado }}</p>
                </div>
                <div class="col-sm-6">
                    <p class="text-uppercase text-muted small fw-bold mb-1" style="letter-spacing: 0.05em;">Comprador</p>
                    <p class="fw-bold mb-0">{{ $pedido->user->name }}</p>
                    <p class="text-muted small mb-0">{{ $pedido->user->email }}</p>
                </div>
            </div>

            @unless ($pedido->status === App\Enums\PedidoStatus::Cancelado)
                <div class="rounded-4 p-3 mb-4 text-center" style="background: #FFF4E5;">
                    <p class="text-muted small mb-1">
                        <i class="bi bi-shop me-1"></i> Retirada presencial na loja — este pedido não tem entrega/envio
                    </p>
                    <p class="fw-bold mb-0" style="font-size: 1.5rem; letter-spacing: 0.15em; color: #FF8C00;">
                        {{ $pedido->numero_retirada }}
                    </p>
                </div>
            @endunless

            <table class="table">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th style="letter-spacing: 0.05em;">Item</th>
                        <th class="text-center" style="letter-spacing: 0.05em;">Qtd.</th>
                        <th class="text-end" style="letter-spacing: 0.05em;">Preço un.</th>
                        <th class="text-end" style="letter-spacing: 0.05em;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedido->itens as $item)
                        <tr>
                            <td>{{ $item->produto->nome }}</td>
                            <td class="text-center">{{ $item->quantidade }}</td>
                            <td class="text-end">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                            <td class="text-end">R$ {{ number_format($item->quantidade * $item->preco_unitario, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold border-0">Total</td>
                        <td class="text-end fw-bold fs-5 border-0" style="color: #FF8C00;">R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            <p class="text-muted small mb-0 mt-4">
                Comprovante gerado pela plataforma AlugaQuadra. Em caso de dúvidas sobre o produto, entre em contato diretamente com {{ $pedido->dono->nome_estabelecimento ?? $pedido->dono->name }}.
            </p>
        </div>
    </div>
</div>

@endsection
