<?php

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Sala;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->role->dashboardRoute());
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/registro', function () {
    return view('auth.registro');
})->middleware('guest')->name('registro');

Route::get('/quadras', function () {
    return view('quadras');
})->name('quadras.index');

Route::get('/quadras-proximas', function () {
    return view('quadras_proximas');
})->name('quadras.proximas');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::get('/entrar-dono', function () {
    return view('auth.login-dono');
})->name('login.dono');
Route::get('/cadastro-dono', function () {
    return view('auth.cadastro-dono');
})->middleware('guest')->name('cadastro.dono');
Route::get('/perfil', function () {
    return view('perfil');
})->middleware('auth')->name('perfil');
Route::get('/encontre-um-time', function () {
    return view('encontre_time');
})->name('encontre_time');
Route::get('/salas/{sala}', function (Sala $sala) {
    return view('sala-detalhes', ['sala' => $sala]);
})->name('salas.detalhes');
Route::get('/salas/{sala}/pagamento', function (Sala $sala) {
    return view('sala-pagamento', ['sala' => $sala]);
})->middleware('auth')->name('salas.pagamento');
Route::get('/salas/{sala}/confirmacao', function (Sala $sala) {
    $sala->load(['quadra', 'participantes']);

    $participacao = $sala->participantes->firstWhere('id', auth()->id());

    abort_unless($participacao, 404);

    return view('sala-confirmacao', ['sala' => $sala, 'participacao' => $participacao]);
})->middleware('auth')->name('salas.confirmacao');
Route::get('/salas/{sala}/grupo', function (Sala $sala) {
    return view('sala-grupo', ['sala' => $sala]);
})->middleware('auth')->name('salas.grupo');
Route::get('/salas/{sala}/pagar-diferenca', function (Sala $sala) {
    return view('sala-pagar-diferenca', ['sala' => $sala]);
})->middleware('auth')->name('salas.pagar-diferenca');
Route::get('/reservas/{reserva}/pagamento', function (\App\Models\Reserva $reserva) {
    return view('reserva-pagamento', ['reserva' => $reserva]);
})->middleware('auth')->name('reservas.pagamento');
Route::get('/reservas/{reserva}/confirmacao', function (\App\Models\Reserva $reserva) {
    abort_unless(auth()->id() === $reserva->user_id, 403);

    return view('reserva-confirmacao', ['reserva' => $reserva]);
})->middleware('auth')->name('reservas.confirmacao');
Route::get('/loja', function () {
    return view('loja');
})->middleware('auth')->name('loja');
Route::get('/loja/{produto}', function (Produto $produto) {
    return view('loja-produto', ['produto' => $produto]);
})->middleware('auth')->name('loja.produto');
Route::get('/carrinho', function () {
    return view('carrinho');
})->middleware('auth')->name('carrinho.index');
Route::get('/loja/pedido/{pedido}', function (Pedido $pedido) {
    abort_unless($pedido->user_id === auth()->id(), 403);

    return view('loja-pedido-confirmacao', ['pedido' => $pedido->load('itens.produto')]);
})->middleware('auth')->name('loja.pedido.confirmacao');
Route::get('/criar-sala', function () {
    return view('criar_sala');
})->name('criar_sala');
