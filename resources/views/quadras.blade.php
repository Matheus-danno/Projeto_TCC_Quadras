@extends('layouts.bootstrap')

@section('titulo', 'Todas as Quadras')

@section('conteudo')
    @include('partials.sub-nav')

    <div class="container mt-4">
        <livewire:quadras.listagem />
    </div>
@endsection
