@extends('layouts.bootstrap')

@section('titulo', $produto->nome.' — Loja AlugaQuadra')

@section('conteudo')

@include('partials.sub-nav')

<livewire:loja.detalhe :produto="$produto" />

@endsection
