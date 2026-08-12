@extends('layouts.bootstrap')

@section('titulo', 'Crie sua conta')

@section('conteudo')

<style>
    .cadastro-card {
        max-width: 560px;
        width: 100%;
        border: 1px solid #FFB366;
        border-radius: 24px;
        padding: 2.5rem 2.75rem;
    }
    .cadastro-titulo {
        color: #FF8C00;
        font-weight: 700;
        text-align: center;
        text-decoration: underline;
        text-underline-offset: 8px;
        margin-bottom: 2rem;
    }
    .cadastro-label {
        font-size: 0.95rem;
        color: #212529;
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
        border-color: #FF8C00;
        box-shadow: 0 0 0 0.15rem rgba(255, 140, 0, 0.15);
    }
    .cadastro-sexo {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #DDE1E5;
        border-radius: 50rem;
        padding: 0.5rem 1.1rem;
        flex: 1;
        font-size: 0.9rem;
        color: #212529;
        cursor: pointer;
    }
    .cadastro-sexo input {
        margin: 0;
    }
    .cadastro-field {
        margin-bottom: 1.4rem;
    }
    .btn-cadastro-cancelar {
        border: 1px solid #FF8C00;
        color: #FF8C00;
        background: #fff;
        border-radius: 50rem;
        font-weight: 600;
        padding: 0.65rem;
    }
    .btn-cadastro-criar {
        background: #FF8C00;
        color: #fff;
        border: none;
        border-radius: 50rem;
        font-weight: 600;
        padding: 0.65rem;
    }
    .btn-cadastro-criar:hover { background: #e67e00; color: #fff; }
</style>

<div class="container d-flex justify-content-center my-5">
    <livewire:auth.registrar />
</div>
@endsection
