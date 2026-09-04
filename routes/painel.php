<?php

use App\Enums\UserRole;
use App\Models\Quadra;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:'.UserRole::DonoQuadra->value])
    ->prefix('painel')
    ->name('painel.')
    ->group(function () {
        Route::get('/', function () {
            return view('painel');
        })->name('dashboard');

        Route::get('/quadras', function () {
            return view('painel-quadras');
        })->name('quadras');

        Route::get('/quadras/nova', function () {
            return view('painel-quadras-formulario');
        })->name('quadras.criar');

        Route::get('/quadras/{quadra}/editar', function (Quadra $quadra) {
            return view('painel-quadras-formulario', ['quadra' => $quadra]);
        })->name('quadras.editar');

        Route::get('/quadras/{quadra}', function (Quadra $quadra) {
            return view('painel-quadras-detalhe', ['quadra' => $quadra]);
        })->name('quadras.show');

        Route::get('/reservas', function () {
            return view('painel-reservas');
        })->name('reservas');

        Route::get('/reservas/agenda', function () {
            return view('painel-reservas-agenda');
        })->name('reservas.agenda');

        Route::get('/financeiro', function () {
            return view('painel-financeiro');
        })->name('financeiro');

        Route::get('/mensagens', function () {
            return view('painel-mensagens');
        })->name('mensagens');

        Route::get('/agendamento-manual', function () {
            return view('painel-agendamento-manual');
        })->name('agendamento-manual');

        Route::get('/configuracoes', function () {
            return view('painel-configuracoes');
        })->name('configuracoes');
    });
