@extends('layouts.bootstrap')

@section('titulo', 'Pagamento')

@section('conteudo')
    @include('partials.sub-nav')

    <livewire:quadras.pagamento :reserva="$reserva" />
@endsection
