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
        padding: 0.6rem 1rem;
        width: 100%;
        font-size: 0.95rem;
    }
    .cadastro-input:focus, .cadastro-select:focus {
        outline: none;
        border-color: #FF8C00;
        box-shadow: 0 0 0 0.15rem rgba(255, 140, 0, 0.15);
    }
    .cadastro-select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        padding-right: 2.5rem;
        background-repeat: no-repeat;
        background-position: right 1.1rem center;
        background-size: 14px 10px;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23FF8C00' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    }
    .cadastro-sexo {
        display: flex;
        align-items: center;
        gap: 4px;
        border: 1px solid #DDE1E5;
        border-radius: 50rem;
        padding: 0.5rem 0.55rem;
        flex: 0 1 auto;
        min-width: 0;
        font-size: 0.9rem;
        color: #212529;
        cursor: pointer;
    }
    .cadastro-sexo .pill-radio-label {
        font-size: 0.9rem;
        font-weight: 400;
        color: #212529;
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
