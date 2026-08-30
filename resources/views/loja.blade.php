@extends('layouts.bootstrap')

@section('titulo', 'Loja AlugaQuadra')

@section('conteudo')

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
    <livewire:loja.listagem />
</div>

@endsection
