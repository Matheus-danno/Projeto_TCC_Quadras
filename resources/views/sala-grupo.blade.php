@extends('layouts.bootstrap')

@section('titulo', 'Grupo — '.$sala->nome)

@section('conteudo')
    @include('partials.sub-nav')

    <livewire:salas.grupo :sala="$sala" />
@endsection
