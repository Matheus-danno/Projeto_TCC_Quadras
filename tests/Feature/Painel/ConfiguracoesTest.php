<?php

use App\Livewire\Painel\Configuracoes;
use App\Models\User;
use Livewire\Livewire;

test('rota painel.configuracoes renderiza o componente', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('painel.configuracoes'))
        ->assertOk()
        ->assertSeeLivewire(Configuracoes::class);
});

test('formulário é preenchido com os dados atuais do dono', function () {
    $dono = User::factory()->donoQuadra()->create([
        'name' => 'Carlos Andrade',
        'nome_estabelecimento' => 'Arena Sports Bauru',
        'telefone' => '14997112233',
        'cnpj' => '12345678000190',
    ]);

    Livewire::actingAs($dono)
        ->test(Configuracoes::class)
        ->assertSet('name', 'Carlos Andrade')
        ->assertSet('nomeEstabelecimento', 'Arena Sports Bauru')
        ->assertSet('telefone', '14997112233');
});

test('dono consegue atualizar o nome do responsável, do estabelecimento e o telefone', function () {
    $dono = User::factory()->donoQuadra()->create([
        'name' => 'Nome Antigo',
        'nome_estabelecimento' => 'Nome Antigo',
        'telefone' => '11900000000',
    ]);

    Livewire::actingAs($dono)
        ->test(Configuracoes::class)
        ->set('name', 'Nome Novo')
        ->set('nomeEstabelecimento', 'Arena Nova')
        ->set('telefone', '(14) 99711-2233')
        ->call('salvar')
        ->assertHasNoErrors();

    $dono->refresh();

    expect($dono->name)->toBe('Nome Novo')
        ->and($dono->nome_estabelecimento)->toBe('Arena Nova')
        ->and($dono->telefone)->toBe('14997112233');
});

test('cnpj é exibido formatado e desabilitado no formulário', function () {
    $dono = User::factory()->donoQuadra()->create(['cnpj' => '12345678000190']);

    $this->actingAs($dono)
        ->get(route('painel.configuracoes'))
        ->assertOk()
        ->assertSee('12.345.678/0001-90')
        ->assertSee('O CNPJ não pode ser alterado após o cadastro.');
});

test('campos obrigatórios são exigidos ao salvar', function () {
    $dono = User::factory()->donoQuadra()->create();

    Livewire::actingAs($dono)
        ->test(Configuracoes::class)
        ->set('name', '')
        ->set('nomeEstabelecimento', '')
        ->set('telefone', '')
        ->call('salvar')
        ->assertHasErrors(['name', 'nomeEstabelecimento', 'telefone']);
});

test('cnpj do dono permanece inalterado após salvar outras informações', function () {
    $dono = User::factory()->donoQuadra()->create(['cnpj' => '12345678000190']);

    Livewire::actingAs($dono)
        ->test(Configuracoes::class)
        ->set('nomeEstabelecimento', 'Outro Nome')
        ->call('salvar');

    expect($dono->fresh()->cnpj)->toBe('12345678000190');
});

test('componente não expõe uma propriedade de cnpj editável via wire:model', function () {
    $dono = User::factory()->donoQuadra()->create(['cnpj' => '12345678000190']);

    expect(fn () => Livewire::actingAs($dono)->test(Configuracoes::class)->set('cnpj', '99999999999999'))
        ->toThrow(\Livewire\Exceptions\PublicPropertyNotFoundException::class);

    expect($dono->fresh()->cnpj)->toBe('12345678000190');
});
