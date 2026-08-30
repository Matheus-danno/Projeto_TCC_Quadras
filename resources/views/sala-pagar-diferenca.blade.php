@extends('layouts.bootstrap')

@section('titulo', 'Pagar diferença — '.$sala->nome)

@section('conteudo')
    @include('partials.sub-nav')

    <livewire:salas.pagar-diferenca :sala="$sala" />
@endsection
