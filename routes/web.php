<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home'); 
})->name('home');

Route::get('/registro', function () {
    return view('auth.registro'); 
})->name('registro');

Route::get('/quadras', function () {
    return view('quadras'); 
})->name('quadras.index');

Route::get('/login', function () {
    return view('auth.login'); 
})->name('login');
Route::get('/perfil', function () {
    return view('perfil'); 
})->name('perfil');
Route::get('/encontre-um-time', function () {
    return view('encontre_time');
})->name('encontre_time');