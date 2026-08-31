<?php

use App\Livewire\Perfil\Ajuda;
use App\Models\SuporteMensagem;
use App\Models\User;
use Livewire\Livewire;

test('página de ajuda mostra as perguntas frequentes', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Ajuda::class)
        ->assertSee('Como faço uma reserva de quadra?')
        ->assertSee('Como cancelo uma reserva?')
        ->assertSee('Como funciona o pagamento das salas?');
});

test('usuário consegue enviar uma mensagem para o suporte', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Ajuda::class)
        ->set('assunto', 'Dúvida sobre reembolso')
        ->set('mensagem', 'Cancelei uma reserva e queria saber quando o crédito cai na minha conta.')
        ->call('enviarMensagem')
        ->assertHasNoErrors()
        ->assertSee('Sua mensagem foi enviada ao suporte');

    expect(SuporteMensagem::count())->toBe(1);

    $mensagem = SuporteMensagem::first();

    expect($mensagem->user_id)->toBe($user->id)
        ->and($mensagem->assunto)->toBe('Dúvida sobre reembolso')
        ->and($mensagem->mensagem)->toBe('Cancelei uma reserva e queria saber quando o crédito cai na minha conta.');
});

test('mensagem de suporte exige assunto e mensagem válidos', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Ajuda::class)
        ->set('assunto', 'ab')
        ->set('mensagem', 'curta')
        ->call('enviarMensagem')
        ->assertHasErrors(['assunto', 'mensagem']);

    expect(SuporteMensagem::count())->toBe(0);
});

test('campos do formulário são limpos após o envio', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Ajuda::class)
        ->set('assunto', 'Problema no pagamento')
        ->set('mensagem', 'O pagamento da minha reserva não foi confirmado corretamente.')
        ->call('enviarMensagem')
        ->assertSet('assunto', '')
        ->assertSet('mensagem', '');
});
