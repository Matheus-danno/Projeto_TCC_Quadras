@extends('layouts.bootstrap')

@section('titulo', 'Cadastre seu Estabelecimento')

@section('conteudo')

<style>
    .cadastro-dono-card {
        max-width: 480px;
        width: 100%;
        border: 1px solid var(--cor-principal);
        border-radius: 24px;
        padding: 2rem 2.25rem;
    }
    .cadastro-dono-titulo {
        color: var(--cor-principal);
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .cadastro-dono-subtitulo {
        color: #989898;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }
    .cadastro-label {
        font-size: 0.95rem;
        color: #333333;
        margin-bottom: 0.4rem;
        display: block;
    }
    .cadastro-input, .cadastro-select {
        border-radius: 50rem;
        border: 1px solid #DDE1E5;
        padding: 0.6rem 1.2rem;
        width: 100%;
        font-size: 0.95rem;
    }
    .cadastro-input:focus, .cadastro-select:focus {
        outline: none;
        border-color: var(--cor-principal);
        box-shadow: 0 0 0 0.15rem rgba(255, 125, 20, 0.15);
    }
    .cadastro-field {
        margin-bottom: 1.25rem;
    }
    .btn-cadastro-cancelar {
        border: 1px solid var(--cor-principal);
        color: var(--cor-principal);
        background: #fff;
        border-radius: 50rem;
        font-weight: 600;
        padding: 0.65rem;
    }
    .btn-cadastro-criar {
        background: var(--cor-principal);
        color: #fff;
        border: none;
        border-radius: 50rem;
        font-weight: 600;
        padding: 0.65rem;
    }
    .btn-cadastro-criar:hover { background: #e67e00; color: #fff; }
</style>

<div class="container d-flex justify-content-center my-5">
    <livewire:auth.registrar-dono />
</div>
@endsection
