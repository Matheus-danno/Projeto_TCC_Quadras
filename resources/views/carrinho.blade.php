@extends('layouts.bootstrap')

@section('titulo', 'Carrinho — Loja AlugaQuadra')

@section('conteudo')

@include('partials.sub-nav')

<div class="container my-5">
    <h1 class="fw-bold mb-4">Seu Carrinho</h1>

    <livewire:loja.carrinho />
</div>

@endsection
