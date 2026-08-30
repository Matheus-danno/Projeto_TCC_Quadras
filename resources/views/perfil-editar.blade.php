@extends('layouts.bootstrap')

@section('titulo', 'Editar Perfil')

@section('conteudo')

<div class="container my-5" style="max-width: 700px;">
    <a href="{{ route('perfil') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1 mb-3 fw-bold">
        <i class="bi bi-chevron-left"></i> Voltar ao perfil
    </a>

    <livewire:perfil.editar />
</div>

@endsection
