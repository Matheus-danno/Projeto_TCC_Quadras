@extends('layouts.bootstrap')

@section('titulo', 'Avaliar Partida — '.$sala->nome)

@section('conteudo')
    @include('partials.sub-nav')

    <livewire:salas.avaliar :sala="$sala" />
@endsection
