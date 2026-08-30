@extends('layouts.bootstrap')

@section('titulo', 'Quadras Próximas')

@section('conteudo')
    @include('partials.sub-nav')

    <div class="container mt-4">
        <livewire:quadras.proximas />
    </div>
@endsection
