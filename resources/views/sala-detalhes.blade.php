@extends('layouts.bootstrap')

@section('titulo', $sala->nome.' — Detalhes da Sala')

@section('conteudo')
    @include('partials.sub-nav')

    <livewire:salas.detalhe :sala="$sala" />
@endsection
