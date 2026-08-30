<div>
    <h5 class="fw-bold text-secondary mb-1">Histórico de Pedidos</h5>
    <p class="text-muted small mb-4">Confira os produtos que você já comprou na loja</p>

    <div class="d-flex flex-column gap-3">
        @forelse ($this->pedidos as $pedido)
            <div class="card border border-light-subtle shadow-none" style="border-radius: 15px;" wire:key="pedido-{{ $pedido->id }}">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div>
                            <h6 class="fw-bold mb-1" style="color: #2D3748;">Pedido #{{ $pedido->id }}</h6>
                            <span class="text-muted small">
                                <i class="bi bi-calendar-check me-1 text-warning"></i> {{ $pedido->created_at->format('d/m/Y \à\s H:i') }}
                            </span>
                        </div>
                        <span class="badge rounded-pill px-3 {{ match ($pedido->status->value) {
                            'confirmado' => 'bg-success',
                            'pendente' => 'bg-warning text-dark',
                            'cancelado' => 'bg-secondary',
                        } }} text-white">{{ $pedido->status->label() }}</span>
                    </div>

                    <div class="d-flex flex-column gap-2 mb-3">
                        @foreach ($pedido->itens as $item)
                            <div class="d-flex justify-content-between align-items-center small">
                                <span class="text-muted">
                                    {{ $item->quantidade }}x {{ $item->produto->nome }}
                                    <span class="mx-1">·</span>
                                    R$ {{ number_format($item->preco_unitario, 2, ',', '.') }} / un.
                                </span>
                                <span class="fw-bold">
                                    R$ {{ number_format($item->quantidade * $item->preco_unitario, 2, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <span class="fw-bold small">Total</span>
                        <span class="fw-bold fs-5" style="color: #FF8C00;">R$ {{ number_format($pedido->total, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="bi bi-bag text-secondary" style="font-size: 2rem;"></i>
                <h6 class="fw-bold text-secondary mt-3 mb-1">Você ainda não fez nenhum pedido</h6>
                <p class="text-muted small mb-4">Que tal dar uma olhada na loja?</p>
                <a href="{{ route('loja') }}" class="btn produto-btn-add px-4">Ir para a loja</a>
            </div>
        @endforelse
    </div>
</div>
