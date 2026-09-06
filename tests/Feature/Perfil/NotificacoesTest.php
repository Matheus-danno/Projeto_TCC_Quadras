<?php

use App\Livewire\Perfil\Notificacoes;
use App\Models\User;
use Livewire\Livewire;

test('notificacoes reais aparecem na lista e sao marcadas como lidas ao abrir a aba', function () {
    $user = User::factory()->create();

    $user->notify(new class extends \Illuminate\Notifications\Notification
    {
        public function via($notifiable): array
        {
            return ['database'];
        }

        public function toDatabase($notifiable): array
        {
            return [
                'tipo' => 'pedido_retirado',
                'icone' => 'bi-bag-check-fill',
                'titulo' => 'Pedido retirado',
                'mensagem' => 'Seu pedido ABC123 foi retirado.',
                'link' => '/perfil#pedidos',
                'linkTexto' => 'Ver meus pedidos',
            ];
        }
    });

    expect($user->fresh()->unreadNotifications)->toHaveCount(1);

    Livewire::actingAs($user)
        ->test(Notificacoes::class)
        ->assertSee('Pedido retirado')
        ->assertSee('Seu pedido ABC123 foi retirado.');

    expect($user->fresh()->unreadNotifications)->toHaveCount(0);
});

test('badge de notificacoes nao lidas some do header depois de abrir a aba de notificacoes', function () {
    $user = User::factory()->create();

    $user->notify(new class extends \Illuminate\Notifications\Notification
    {
        public function via($notifiable): array
        {
            return ['database'];
        }

        public function toDatabase($notifiable): array
        {
            return ['tipo' => 'aviso', 'icone' => 'bi-bell', 'titulo' => 'Aviso', 'mensagem' => 'Teste', 'link' => '#', 'linkTexto' => 'Ver'];
        }
    });

    // Primeira visita: o header (renderizado antes do componente de
    // notificações na página) ainda mostra a contagem de não lidas.
    $this->actingAs($user)->get(route('perfil'))->assertSee('navbar_icone-badge');

    // A própria visita acima já montou o componente de notificações e
    // marcou tudo como lido; uma segunda visita não mostra mais o badge.
    // actingAs() fixa a MESMA instância de User no guard (com relações já
    // carregadas em cache), então precisa de uma instância nova (fresh())
    // para refletir o read_at que acabou de ser gravado no banco.
    $this->actingAs($user->fresh())->get(route('perfil'))->assertDontSee('navbar_icone-badge');
});
