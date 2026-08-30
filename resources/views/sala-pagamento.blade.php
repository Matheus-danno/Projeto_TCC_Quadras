@extends('layouts.bootstrap')

@section('titulo', 'Pagamento — '.$sala->nome)

@section('conteudo')
    @include('partials.sub-nav')

    <livewire:salas.pagamento :sala="$sala" />
@endsection
