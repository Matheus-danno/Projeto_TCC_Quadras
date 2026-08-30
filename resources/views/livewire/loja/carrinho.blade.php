<div>
    @if ($this->itens->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-cart-x text-secondary" style="font-size: 2.5rem;"></i>
            <h5 class="fw-bold text-secondary mt-3 mb-1">Seu carrinho está vazio</h5>
            <p class="text-muted small mb-4">Adicione produtos da loja para continuar.</p>
            <a href="{{ route('loja') }}" class="btn produto-btn-add px-4">Ir para a loja</a>
        </div>
    @else
        <div class="d-flex flex-column gap-3 mb-4">
            @foreach ($this->itens as $item)
                <div class="card border-0 shadow-sm" style="border-radius: 15px;" wire:key="carrinho-item-{{ $item->produto->id }}">
                    <div class="card-body d-flex flex-wrap align-items-center gap-3 p-3">
                        <div class="produto-img-container flex-shrink-0 rounded-3" style="width: 80px; height: 80px;">
                            @if ($item->produto->imagem)
                                <img src="{{ asset($item->produto->imagem) }}" alt="{{ $item->produto->nome }}" class="produto-img">
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <i class="bi bi-bag text-secondary"></i>
                                </div>
                            @endif
                        </div>

                        <div class="flex-grow-1" style="min-width: 180px;">
                            <h6 class="fw-bold mb-1">{{ $item->produto->nome }}</h6>
                            <p class="text-muted small mb-0">R$ {{ number_format($item->produto->preco, 2, ',', '.') }} / un.</p>
                        </div>

                        <div>
                            <label class="form-label small fw-bold mb-1">Quantidade</label>
                            <input
                                type="number"
                                class="form-control form-control-sm border-orange"
                                style="width: 90px;"
                                wire:model.live="quantidades.{{ $item->produto->id }}"
                                min="1"
                                max="{{ $item->produto->estoque }}"
                            >
                        </div>

                        <div class="text-end" style="min-width: 110px;">
                            <span class="fw-bold text-laranja-loja">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</span>
                        </div>

                        <button type="button" class="btn btn-link text-danger p-0" wire:click="remover({{ $item->produto->id }})" title="Remover">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 20px;">
            <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <span class="text-muted small d-block">Total</span>
                    <span class="fw-bold fs-4 text-laranja-loja">R$ {{ number_format($this->total, 2, ',', '.') }}</span>
                </div>

                <button type="button" class="btn produto-btn-add px-4" wire:click="finalizarPedido" wire:loading.attr="disabled">
                    Finalizar Pedido
                </button>
            </div>
        </div>
    @endif
</div>
