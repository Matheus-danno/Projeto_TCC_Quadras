@extends('layouts.bootstrap')

@section('titulo', 'Confirme sua senha')

@section('conteudo')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card p-4 shadow-sm border-0 rounded-4" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold">Confirme sua senha</h3>
            <p class="text-muted">Esta é uma área protegida. Confirme sua senha para continuar.</p>
        </div>

        <form method="POST" action="{{ route('password.confirm.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">Senha</label>
                <input type="password" name="password" class="form-control border-orange" placeholder="******" required autofocus autocomplete="current-password">
                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-orange text-white fw-bold py-2 rounded-2">Confirmar</button>
            </div>
        </form>
    </div>
</div>
@endsection
