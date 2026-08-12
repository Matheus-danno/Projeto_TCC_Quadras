@extends('layouts.bootstrap')

@section('titulo', 'Loja AlugaQuadra')

@section('conteudo')

@php
    // Carrinho e pagamento ainda não existem: por ora a loja é só uma vitrine dos produtos cadastrados.
    $produtos = \App\Models\Produto::orderBy('nome')->get();
@endphp

@include('partials.sub-nav')

<!-- Header da Loja -->
<div class="bg-laranja-loja">
    <!-- Título -->
    <div class="container py-5 text-white">
        <h1 class="fw-bold mb-2">Loja AlugaQuadra</h1>
        <p class="fs-5 opacity-75 mb-0">Equipamentos e acessórios esportivos de alta qualidade</p>
    </div>
</div>

<!-- Container Principal da Loja -->
<div class="container my-5">

    <p class="text-muted mb-4">{{ $produtos->count() }} produtos</p>

    <!-- Grid de Produtos -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">

        @forelse ($produtos as $produto)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm produto-card">

                    <!-- Imagem do Produto -->
                    <div class="produto-img-container d-flex align-items-center justify-content-center">
                        @if ($produto->imagem)
                            <img src="{{ asset($produto->imagem) }}" alt="{{ $produto->nome }}" class="produto-img">
                        @else
                            <i class="bi bi-bag text-secondary" style="font-size: 2.5rem;"></i>
                        @endif
                    </div>

                    <!-- Informações do Produto -->
                    <div class="card-body d-flex flex-column p-3">
                        <h6 class="fw-bold mb-1 produto-titulo">{{ $produto->nome }}</h6>

                        @if ($produto->descricao)
                            <p class="text-muted small mb-2">{{ $produto->descricao }}</p>
                        @endif

                        <div class="mt-auto">
                            <div class="fw-bold fs-5 text-laranja-loja">
                                R$ {{ number_format($produto->preco, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted text-center py-5">Nenhum produto cadastrado no momento.</p>
            </div>
        @endforelse

    </div>
</div>

@endsection
