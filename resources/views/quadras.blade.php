@extends('layouts.bootstrap')

@section('titulo', 'Todas as Quadras')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/quadras.css') }}">
@endpush

@section('conteudo')
    @include('partials.sub-nav')

    <section class="courts-page" aria-labelledby="courts-title">
        <div class="courts-page__inner">
            <div class="visually-hidden">
                <h1 id="courts-title">Todas as Quadras</h1>
            </div>
            <livewire:quadras.listagem />
        </div>
    </section>
@endsection
