<?php

use App\Enums\UserRole;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:'.UserRole::Admin->value])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', function () {
            return view('admin');
        })->name('dashboard');

        Route::get('/quadras', function () {
            return view('admin-quadras');
        })->name('quadras');

        Route::get('/usuarios', function () {
            return view('admin-usuarios');
        })->name('usuarios');

        Route::get('/reservas', function () {
            return view('admin-reservas');
        })->name('reservas');

        Route::get('/mensagens', function () {
            return view('admin-mensagens');
        })->name('mensagens');
    });
