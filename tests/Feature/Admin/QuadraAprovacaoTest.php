<?php

use App\Enums\StatusCadastro;
use App\Livewire\Admin\Quadras\Listagem;
use App\Livewire\Quadras\Listagem as ListagemPublica;
use App\Models\Quadra;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('quadra criada sem status explícito nasce pendente de aprovação por padrão no banco', function () {
    $dono = User::factory()->donoQuadra()->create();

    // Insert direto, sem passar pelo model/factory, pra testar o default real da coluna.
    $id = DB::table('quadras')->insertGetId([
        'dono_id' => $dono->id,
        'nome' => 'Quadra Sem Status Explícito',
        'endereco' => 'Rua Teste, 1',
        'cidade' => 'Recife',
        'bairro' => 'Centro',
        'esporte' => 'futebol',
        'valor_hora' => 50,
        'cobertura' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(Quadra::find($id)->status_cadastro)->toBe(StatusCadastro::Pendente);
});

test('admin consegue suspender uma quadra aprovada e ela some da listagem pública', function () {
    $admin = User::factory()->admin()->create();
    $quadra = Quadra::factory()->create(['ativa' => true]);

    Livewire::actingAs($admin)->test(Listagem::class)
        ->call('suspender', $quadra->id);

    expect($quadra->refresh()->status_cadastro)->toBe(StatusCadastro::Suspenso);

    Livewire::test(ListagemPublica::class)
        ->assertDontSee($quadra->nome);
});

test('admin consegue reativar uma quadra suspensa e ela volta a aparecer na listagem pública', function () {
    $admin = User::factory()->admin()->create();
    $quadra = Quadra::factory()->create(['status_cadastro' => StatusCadastro::Suspenso]);

    Livewire::actingAs($admin)->test(Listagem::class)
        ->call('reativar', $quadra->id);

    expect($quadra->refresh()->status_cadastro)->toBe(StatusCadastro::Aprovado);

    Livewire::test(ListagemPublica::class)
        ->assertSee($quadra->nome);
});

test('dono de quadra não pode suspender ou reativar quadras pela tela do admin', function () {
    $dono = User::factory()->donoQuadra()->create();

    $this->actingAs($dono)->get(route('admin.quadras'))->assertForbidden();
});
