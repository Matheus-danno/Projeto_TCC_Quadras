<?php

use App\Enums\PedidoStatus;
use App\Livewire\Painel\Loja\Formulario;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('rota painel.loja.criar renderiza o formulário de cadastro', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('painel.loja.criar'))
        ->assertOk()
        ->assertSeeLivewire(Formulario::class);
});

test('rota painel.loja.editar renderiza o formulário preenchido', function () {
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'nome' => 'Produto Existente']);

    $this->actingAs($dono)
        ->get(route('painel.loja.editar', $produto))
        ->assertOk()
        ->assertSeeLivewire(Formulario::class)
        ->assertSee('Produto Existente');
});

test('dono consegue cadastrar um novo produto com imagem', function () {
    Storage::fake('public');

    $dono = User::factory()->donoQuadra()->create();

    Livewire::actingAs($dono)
        ->test(Formulario::class)
        ->set('nome', 'Bola de Futevôlei')
        ->set('descricao', 'Bola oficial para areia.')
        ->set('categoria', 'Acessórios')
        ->set('preco', '99.90')
        ->set('estoque', '20')
        ->set('ativo', true)
        ->set('novaImagem', UploadedFile::fake()->image('produto.jpg'))
        ->call('salvar')
        ->assertHasNoErrors();

    expect(Produto::count())->toBe(1);

    $produto = Produto::first();

    expect($produto->dono_id)->toBe($dono->id)
        ->and($produto->nome)->toBe('Bola de Futevôlei')
        ->and((float) $produto->preco)->toBe(99.90)
        ->and($produto->estoque)->toBe(20)
        ->and($produto->ativo)->toBeTrue()
        ->and($produto->imagem)->not->toBeNull();

    Storage::disk('public')->assertExists($produto->imagem);
});

test('cadastro de produto exige campos obrigatórios e preço/estoque válidos', function () {
    $dono = User::factory()->donoQuadra()->create();

    Livewire::actingAs($dono)
        ->test(Formulario::class)
        ->set('preco', '-10')
        ->set('estoque', '-1')
        ->call('salvar')
        ->assertHasErrors(['nome', 'descricao', 'categoria', 'preco', 'estoque']);

    expect(Produto::count())->toBe(0);
});

test('dono edita o próprio produto', function () {
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'nome' => 'Nome Antigo', 'preco' => 10]);

    Livewire::actingAs($dono)
        ->test(Formulario::class, ['produto' => $produto])
        ->set('nome', 'Nome Novo')
        ->set('preco', '150.00')
        ->call('salvar')
        ->assertHasNoErrors();

    expect($produto->fresh()->nome)->toBe('Nome Novo')
        ->and((float) $produto->fresh()->preco)->toBe(150.0)
        ->and($produto->fresh()->dono_id)->toBe($dono->id);
});

test('dono não consegue editar produto de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $produtoAlheio = Produto::factory()->create(['dono_id' => $outroDono->id]);

    Livewire::actingAs($dono)
        ->test(Formulario::class, ['produto' => $produtoAlheio])
        ->assertForbidden();
});

test('trocar a imagem remove a anterior do disco', function () {
    Storage::fake('public');

    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id, 'imagem' => 'produtos/1/antiga.jpg']);
    Storage::disk('public')->put($produto->imagem, 'fake');

    Livewire::actingAs($dono)
        ->test(Formulario::class, ['produto' => $produto])
        ->set('novaImagem', UploadedFile::fake()->image('nova.jpg'))
        ->call('salvar');

    Storage::disk('public')->assertMissing('produtos/1/antiga.jpg');
    Storage::disk('public')->assertExists($produto->fresh()->imagem);
});

test('dono exclui o produto a partir do formulário de edição', function () {
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id]);

    Livewire::actingAs($dono)
        ->test(Formulario::class, ['produto' => $produto])
        ->call('pedirExclusao')
        ->call('excluir')
        ->assertRedirect(route('painel.loja'));

    expect(Produto::query()->find($produto->id))->toBeNull();
});

test('exclusão pelo formulário é bloqueada quando o produto já foi vendido', function () {
    $dono = User::factory()->donoQuadra()->create();
    $produto = Produto::factory()->create(['dono_id' => $dono->id]);

    $pedido = Pedido::create([
        'user_id' => User::factory()->create()->id,
        'status' => PedidoStatus::Aguardando,
        'total' => $produto->preco,
    ]);

    ItemPedido::create([
        'pedido_id' => $pedido->id,
        'produto_id' => $produto->id,
        'quantidade' => 1,
        'preco_unitario' => $produto->preco,
    ]);

    $component = Livewire::actingAs($dono)
        ->test(Formulario::class, ['produto' => $produto])
        ->call('pedirExclusao')
        ->call('excluir');

    expect($component->get('bloqueioExclusao'))->not->toBeNull();
    expect(Produto::query()->find($produto->id))->not->toBeNull();
});

test('dono não consegue excluir produto de outro dono pelo formulário', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $produtoAlheio = Produto::factory()->create(['dono_id' => $outroDono->id]);

    Livewire::actingAs($dono)
        ->test(Formulario::class, ['produto' => $produtoAlheio])
        ->assertForbidden();
});
