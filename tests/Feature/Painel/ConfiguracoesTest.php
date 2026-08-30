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
        'nome_estabelecimento' => 'Arena Sports Bauru',
        'telefone' => '14997112233',
        'endereco' => 'Rua Correia Júnior, 357',
        'cidade' => 'Bauru',
        'estado' => 'SP',
        'cnpj' => '12345678000190',
    ]);

    Livewire::actingAs($dono)
        ->test(Configuracoes::class)
        ->assertSet('nomeEstabelecimento', 'Arena Sports Bauru')
        ->assertSet('telefone', '14997112233')
        ->assertSet('endereco', 'Rua Correia Júnior, 357')
        ->assertSet('cidade', 'Bauru')
        ->assertSet('estado', 'SP');
});

test('cnpj é exibido formatado e desabilitado no formulário', function () {
    $dono = User::factory()->donoQuadra()->create(['cnpj' => '12345678000190']);

    $this->actingAs($dono)
        ->get(route('painel.configuracoes'))
        ->assertOk()
        ->assertSee('12.345.678/0001-90')
        ->assertSee('O CNPJ não pode ser alterado após o cadastro.');
});

test('dono atualiza nome do estabelecimento, telefone, endereço, cidade e estado', function () {
    $dono = User::factory()->donoQuadra()->create([
        'nome_estabelecimento' => 'Nome Antigo',
        'telefone' => '11900000000',
        'endereco' => 'Endereço Antigo',
        'cidade' => 'Cidade Antiga',
        'estado' => 'SP',
    ]);

    Livewire::actingAs($dono)
        ->test(Configuracoes::class)
        ->set('nomeEstabelecimento', 'Arena Nova')
        ->set('telefone', '(14) 99711-2233')
        ->set('endereco', 'Rua Nova, 123')
        ->set('cidade', 'Bauru')
        ->set('estado', 'sp')
        ->call('salvar')
        ->assertHasNoErrors();

    $dono->refresh();

    expect($dono->nome_estabelecimento)->toBe('Arena Nova')
        ->and($dono->telefone)->toBe('14997112233')
        ->and($dono->endereco)->toBe('Rua Nova, 123')
        ->and($dono->cidade)->toBe('Bauru')
        ->and($dono->estado)->toBe('SP');
});

test('campos obrigatórios são exigidos ao salvar', function () {
    $dono = User::factory()->donoQuadra()->create();

    Livewire::actingAs($dono)
        ->test(Configuracoes::class)
        ->set('nomeEstabelecimento', '')
        ->set('telefone', '')
        ->set('endereco', '')
        ->set('cidade', '')
        ->set('estado', '')
        ->call('salvar')
        ->assertHasErrors(['nomeEstabelecimento', 'telefone', 'endereco', 'cidade', 'estado']);
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
