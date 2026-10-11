<?php

use App\Enums\UserRole;
use App\Models\Quadra;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:'.UserRole::Admin->value])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', function () {
            return view('admin-dashboard');
        })->name('dashboard');

        Route::get('/aprovacoes', function () {
            return view('admin-aprovacoes');
        })->name('aprovacoes');

        Route::get('/aprovacoes/proprietarios/{usuario}', function (User $usuario) {
            return view('admin-aprovacoes-proprietario', ['usuario' => $usuario]);
        })->name('aprovacoes.proprietario');

        Route::get('/aprovacoes/quadras/{quadra}', function (Quadra $quadra) {
            return view('admin-aprovacoes-quadra', ['quadra' => $quadra]);
        })->name('aprovacoes.quadra');

        Route::get('/financeiro', function () {
            return view('admin-financeiro');
        })->name('financeiro');

        Route::get('/financeiro/repasses', function () {
            return view('admin-financeiro-repasses');
        })->name('financeiro.repasses');

        Route::get('/quadras', function () {
            return view('admin-quadras');
        })->name('quadras');

        Route::get('/usuarios', function () {
            return view('admin-usuarios');
        })->name('usuarios');

        Route::get('/usuarios/{usuario}', function (User $usuario) {
            return view('admin-usuarios-detalhe', ['usuario' => $usuario]);
        })->name('usuarios.show');

        Route::get('/reservas', function () {
            return view('admin-reservas');
        })->name('reservas');

        Route::get('/configuracoes', function () {
            return view('admin-configuracoes');
        })->name('configuracoes');
    });
