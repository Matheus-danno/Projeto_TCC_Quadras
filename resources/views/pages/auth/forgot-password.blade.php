@extends('layouts.bootstrap')

@section('titulo', 'Esqueci minha senha')

@section('conteudo')

<style>
    .senha-card {
        max-width: 480px;
        width: 100%;
        border: 1px solid #ff7d14;
        border-radius: 30px;
        padding: 2.5rem 2.75rem;
    }
    .senha-titulo {
        color: #ff7d14;
        font-weight: 600;
        font-size: 1.5rem;
        margin-bottom: 1.25rem;
    }
    .senha-status {
        background: #d4edda;
        color: #155724;
        border-radius: 10px;
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }
    .senha-descricao {
        color: #515151;
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
    }
    .senha-campo {
        margin-bottom: 1.5rem;
    }
    .senha-label {
        font-size: 0.95rem;
        color: #212529;
        margin-bottom: 0.4rem;
        display: block;
    }
    .senha-input-wrap {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid #515151;
        border-radius: 20px;
        padding: 0.5rem 1rem;
    }
    .senha-input-wrap:focus-within {
        border-color: #ff7d14;
    }
    .senha-input-wrap span {
        color: #989898;
        font-size: 0.95rem;
    }
    .senha-input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 0;
        font-size: 0.95rem;
        background: transparent;
    }
    .btn-senha-enviar {
        background: #ff7d14;
        color: #fff4e5;
        border: none;
        border-radius: 20px;
        font-weight: 700;
        padding: 0.65rem;
        width: 100%;
    }
    .btn-senha-enviar:hover {
        background: #e56c0a;
        color: #fff4e5;
    }
    .senha-cancelar {
        display: block;
        text-align: center;
        color: #989898;
        font-weight: 700;
        text-decoration: none;
        margin-top: 1.25rem;
    }
    .senha-cancelar:hover {
        color: #7a7a7a;
    }
</style>

<div class="container d-flex justify-content-center my-5">
    <div class="senha-card">
        <h2 class="senha-titulo">Esqueci minha senha</h2>

        @if (session('status'))
            <div class="senha-status">Se o e-mail informado estiver cadastrado, enviamos um link de redefinição de senha.</div>
        @endif

        <p class="senha-descricao">
            Para redefinir sua senha, informe o e-mail cadastrado na sua conta para enviarmos um link de redefinição de senha.
        </p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="senha-campo">
                <label class="senha-label" for="email">E-mail</label>
                <div class="senha-input-wrap">
                    <span>@</span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="senha-input" placeholder="seu@email.com" required autofocus>
                </div>
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn-senha-enviar">Enviar</button>

            <a href="{{ route('login') }}" class="senha-cancelar">Cancelar</a>
        </form>
    </div>
</div>
@endsection
