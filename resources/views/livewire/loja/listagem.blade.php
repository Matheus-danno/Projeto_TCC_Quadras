<div>
    {{-- Seção de Filtros --}}
    <div class="row mb-4 align-items-center g-2">
        <div class="col-auto"><strong>FILTROS</strong></div>
        <div class="col">
            <select class="form-select border-orange" wire:model.live="categoria">
                <option value="">Categoria</option>
                @foreach ($this->categorias as $opcao)
                    <option value="{{ $opcao }}">{{ $opcao }}</option>
                @endforeach
            </select>
        </div>
        <div class="col">
            <input
                type="text"
                class="form-control border-orange"
                wire:model.live.debounce.300ms="busca"
                placeholder="Buscar produto..."
            >
        </div>
    </div>

    <p class="text-muted mb-4">{{ $this->produtos->count() }} produtos</p>

    {{-- Grid de Produtos --}}
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">

        @forelse ($this->produtos as $produto)
            <div class="col" wire:key="produto-{{ $produto->id }}">
                <div class="card h-100 border-0 shadow-sm produto-card">

                    <!-- Imagem do Produto -->
                    <div class="produto-img-container d-flex align-items-center justify-content-center">
                        @if ($produto->imagem)
                            <img src="{{ asset($produto->imagem) }}" alt="{{ $produto->nome }}" class="produto-img">
                        @else
                            <i class="bi bi-bag text-secondary" style="font-size: 2.5rem;"></i>
                        @endif

                        @unless ($produto->disponivel)
                            <div class="produto-overlay-esgotado position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                <span class="badge bg-secondary">Esgotado</span>
                            </div>
                        @endunless
                    </div>

                    <!-- Informações do Produto -->
                    <div class="card-body d-flex flex-column p-3">
                        <h6 class="fw-bold mb-1 produto-titulo">{{ $produto->nome }}</h6>

                        @if ($produto->descricao)
                            <p class="text-muted small mb-2">{{ $produto->descricao }}</p>
                        @endif

                        <div class="mt-auto">
                            <div class="fw-bold fs-5 text-laranja-loja mb-2">
                                R$ {{ number_format($produto->preco, 2, ',', '.') }}
                            </div>

                            @if ($produto->disponivel)
                                <a href="{{ route('loja.produto', $produto) }}" class="btn produto-btn-add w-100">
                                    Ver produto
                                </a>
                            @else
                                <button type="button" class="btn btn-secondary produto-btn-esgotado w-100" disabled>
                                    Esgotado
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted text-center py-5">Nenhum produto encontrado com esses filtros.</p>
            </div>
        @endforelse

    </div>
</div>
