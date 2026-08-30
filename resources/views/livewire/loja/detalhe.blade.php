<div class="container my-5">
    <a href="{{ route('loja') }}" class="d-inline-flex align-items-center gap-1 text-decoration-none text-laranja-loja mb-4 fw-bold">
        <i class="bi bi-arrow-left"></i> Voltar para a loja
    </a>

    <div class="row g-5">
        <div class="col-md-5">
            <div class="produto-img-container rounded-4 shadow-sm" style="height: 360px;">
                @if ($produto->imagem)
                    <img src="{{ asset($produto->imagem) }}" alt="{{ $produto->nome }}" class="produto-img">
                @else
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <i class="bi bi-bag text-secondary" style="font-size: 4rem;"></i>
                    </div>
                @endif

                @unless ($produto->disponivel)
                    <div class="produto-overlay-esgotado position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <span class="badge bg-secondary fs-6">Esgotado</span>
                    </div>
                @endunless
            </div>
        </div>

        <div class="col-md-7">
            @if ($produto->categoria)
                <span class="badge bg-light text-laranja-loja border border-orange mb-2">{{ $produto->categoria }}</span>
            @endif

            <h1 class="fw-bold mb-3 text-titulo-escuro">{{ $produto->nome }}</h1>

            @if ($produto->descricao)
                <p class="text-muted mb-4">{{ $produto->descricao }}</p>
            @endif

            <div class="fw-bold fs-3 text-laranja-loja mb-4">
                R$ {{ number_format($produto->preco, 2, ',', '.') }}
            </div>

            @if ($produto->disponivel)
                @if ($mensagemSucesso)
                    <div class="alert alert-success" role="alert">
                        {{ $mensagemSucesso }}
                    </div>
                @endif

                @if ($precisaLogin)
                    <p class="small mb-3">Você precisa <a href="{{ route('login') }}">entrar</a> para adicionar produtos ao carrinho.</p>
                @endif

                <div class="d-flex align-items-end gap-3 mb-2">
                    <div>
                        <label class="form-label small fw-bold">Quantidade</label>
                        <input
                            type="number"
                            class="form-control border-orange"
                            style="width: 100px;"
                            wire:model="quantidade"
                            min="1"
                            max="{{ $produto->estoque }}"
                        >
                    </div>

                    <button type="button" class="btn produto-btn-add px-4" wire:click="adicionarAoCarrinho">
                        <i class="bi bi-cart-plus me-1"></i> Adicionar ao carrinho
                    </button>
                </div>

                <p class="text-muted small mb-0">{{ $produto->estoque }} em estoque</p>
            @else
                <button type="button" class="btn btn-secondary produto-btn-esgotado px-4" disabled>
                    Esgotado
                </button>
            @endif
        </div>
    </div>
</div>
