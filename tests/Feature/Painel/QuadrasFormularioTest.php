<?php

use App\Enums\Esporte;
use App\Livewire\Painel\Quadras\Formulario;
use App\Models\Quadra;
use App\Models\QuadraFoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('rota painel.quadras.criar renderiza o formulário de cadastro', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)
        ->get(route('painel.quadras.criar'))
        ->assertOk()
        ->assertSeeLivewire(Formulario::class);
});

test('rota painel.quadras.editar renderiza o formulário preenchido', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Arena Existente']);

    $this->actingAs($dono)
        ->get(route('painel.quadras.editar', $quadra))
        ->assertOk()
        ->assertSeeLivewire(Formulario::class)
        ->assertSee('Arena Existente');
});

test('dono consegue cadastrar uma nova quadra com foto de capa', function () {
    Storage::fake('public');

    $dono = User::factory()->donoQuadra()->create();

    Livewire::actingAs($dono)
        ->test(Formulario::class)
        ->set('nome', 'Arena Teste')
        ->set('endereco', 'Rua Um, 100')
        ->set('bairro', 'Boa Viagem')
        ->set('cidade', 'Recife')
        ->set('cep', '50000-000')
        ->set('esporte', Esporte::Futsal->value)
        ->set('valor_hora', '80.00')
        ->set('capacidade_maxima', '10')
        ->set('cobertura', true)
        ->set('descricao', 'Quadra de teste')
        ->set('novasFotos', [UploadedFile::fake()->image('quadra.jpg')])
        ->call('salvar')
        ->assertHasNoErrors();

    expect(Quadra::count())->toBe(1);

    $quadra = Quadra::first();

    expect($quadra->dono_id)->toBe($dono->id)
        ->and($quadra->nome)->toBe('Arena Teste')
        ->and($quadra->esporte)->toBe(Esporte::Futsal)
        ->and($quadra->cobertura)->toBeTrue()
        ->and($quadra->capacidade_maxima)->toBe(10)
        ->and($quadra->cep)->toBe('50000-000')
        ->and($quadra->fotos)->toHaveCount(1)
        ->and($quadra->fotoCapa()->capa)->toBeTrue();

    Storage::disk('public')->assertExists($quadra->fotos->first()->caminho);
});

test('cadastro de quadra exige campos obrigatórios e valor_hora numérico', function () {
    $dono = User::factory()->donoQuadra()->create();

    Livewire::actingAs($dono)
        ->test(Formulario::class)
        ->set('valor_hora', '-10')
        ->call('salvar')
        ->assertHasErrors(['nome', 'endereco', 'cidade', 'bairro', 'esporte', 'valor_hora']);

    expect(Quadra::count())->toBe(0);
});

test('dono edita a própria quadra', function () {
    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'nome' => 'Nome Antigo']);

    Livewire::actingAs($dono)
        ->test(Formulario::class, ['quadra' => $quadra])
        ->set('nome', 'Nome Novo')
        ->set('valor_hora', '150.00')
        ->call('salvar')
        ->assertHasNoErrors();

    expect($quadra->fresh()->nome)->toBe('Nome Novo')
        ->and((float) $quadra->fresh()->valor_hora)->toBe(150.0)
        ->and($quadra->fresh()->dono_id)->toBe($dono->id);
});

test('dono não consegue editar quadra de outro dono', function () {
    $dono = User::factory()->donoQuadra()->create();
    $outroDono = User::factory()->donoQuadra()->create();
    $quadraAlheia = Quadra::factory()->create(['dono_id' => $outroDono->id]);

    Livewire::actingAs($dono)
        ->test(Formulario::class, ['quadra' => $quadraAlheia])
        ->assertForbidden();
});

test('remover foto existente exclui o arquivo e reordena a capa', function () {
    Storage::fake('public');

    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    $foto1 = QuadraFoto::create(['quadra_id' => $quadra->id, 'caminho' => 'quadras/1/foto1.jpg', 'capa' => true, 'ordem' => 0]);
    $foto2 = QuadraFoto::create(['quadra_id' => $quadra->id, 'caminho' => 'quadras/1/foto2.jpg', 'capa' => false, 'ordem' => 1]);
    Storage::disk('public')->put($foto1->caminho, 'fake');
    Storage::disk('public')->put($foto2->caminho, 'fake');

    Livewire::actingAs($dono)
        ->test(Formulario::class, ['quadra' => $quadra])
        ->call('removerFotoExistente', $foto1->id);

    Storage::disk('public')->assertMissing($foto1->caminho);
    expect(QuadraFoto::find($foto1->id))->toBeNull();

    $restante = $quadra->fresh()->fotos->first();
    expect($restante->id)->toBe($foto2->id)
        ->and($restante->capa)->toBeTrue()
        ->and($restante->ordem)->toBe(0);
});

test('dono não pode ultrapassar o limite de 8 fotos', function () {
    Storage::fake('public');

    $dono = User::factory()->donoQuadra()->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id]);

    for ($i = 0; $i < 8; $i++) {
        QuadraFoto::create(['quadra_id' => $quadra->id, 'caminho' => "quadras/1/foto{$i}.jpg", 'capa' => $i === 0, 'ordem' => $i]);
    }

    Livewire::actingAs($dono)
        ->test(Formulario::class, ['quadra' => $quadra])
        ->set('nome', $quadra->nome)
        ->set('endereco', $quadra->endereco)
        ->set('bairro', $quadra->bairro)
        ->set('cidade', $quadra->cidade)
        ->set('esporte', $quadra->esporte->value)
        ->set('valor_hora', (string) $quadra->valor_hora)
        ->set('novasFotos', [UploadedFile::fake()->image('nova.jpg')])
        ->call('salvar')
        ->assertHasErrors(['novasFotos']);

    expect($quadra->fresh()->fotos)->toHaveCount(8);
});
