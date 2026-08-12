@extends('layouts.bootstrap')

@section('titulo', 'Criar Nova Sala')

@section('conteudo')
    @include('partials.sub-nav')

    <livewire:salas.criar />
@endsection
