@extends('layouts.app')

@section('titulo', 'Entrar')

@section('conteudo')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card p-4 shadow-sm border-0 rounded-4" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold">Entrar</h3>
            <p class="text-muted">Acesse sua conta para reservar sua quadra</p>
        </div>

        <form>
            <div class="mb-3">
                <label class="form-label fw-bold">E-mail</label>
                <input type="email" class="form-control border-orange" placeholder="seu@email.com">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Senha</label>
                <input type="password" class="form-control border-orange" placeholder="******">
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-orange text-white fw-bold py-2 rounded-pill">Entrar</button>
            </div>
            
            <div class="text-center mt-3">
                <small>Não tem uma conta? <a href="{{ route('registro') }}" class="text-orange fw-bold">Cadastre-se</a></small>
            </div>
        </form>
    </div>
</div>
@endsection