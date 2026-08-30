<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/registro', function () {
    return view('auth.registro');
})->middleware('guest')->name('registro');

Route::get('/quadras', function () {
    return view('quadras');
})->name('quadras.index');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::get('/perfil', function () {
    return view('perfil');
})->middleware('auth')->name('perfil');
Route::get('/encontre-um-time', function () {
    return view('encontre_time');
})->name('encontre_time');
Route::get('/loja', function () {
    return view('loja');
})->name('loja');
Route::get('/criar-sala', function () {
    return view('criar_sala');
})->name('criar_sala');

Route::get('/admin/quadras/nova', function () {
    return view('admin.quadras.create');
})->middleware(['auth', 'verified'])->name('admin.quadras.create');

Route::get('/admin/configuracoes', function () {
    return view('admin.configuracoes');
})->middleware(['auth', 'verified'])->name('admin.configuracoes');

