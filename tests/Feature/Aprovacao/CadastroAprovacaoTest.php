<?php

use App\Enums\StatusCadastro;
use App\Enums\TipoDocumento;
use App\Livewire\Admin\Aprovacoes\AnalisarProprietario;
use App\Livewire\Admin\Aprovacoes\AnalisarQuadra;
use App\Livewire\Painel\Quadras\Formulario;
use App\Livewire\Quadras\Listagem as ListagemPublica;
use App\Models\Quadra;
use App\Models\QuadraFoto;
use App\Models\User;
use App\Services\Aprovacao\CadastroAprovacaoService;
use Livewire\Livewire;

/**
 * Garante que a quadra tem o mínimo de fotos e o comprovante de imóvel já
 * cadastrados, pra editar/salvar pelo Formulario sem precisar simular
 * upload de arquivo nos testes (o ambiente de CI não tem a extensão GD).
 */
function prepararQuadraParaEdicao(Quadra $quadra): void
{
    for ($i = 0; $i < 3; $i++) {
        QuadraFoto::create([
            'quadra_id' => $quadra->id,
            'caminho' => "quadras/{$quadra->id}/foto-{$i}.jpg",
            'ordem' => $i,
            'capa' => $i === 0,
        ]);
    }

    $quadra->documentos()->create([
        'tipo' => TipoDocumento::ComprovanteImovel,
        'caminho' => "documentos/quadras/{$quadra->id}/comprovante.pdf",
        'nome_original' => 'comprovante.pdf',
        'tamanho_bytes' => 1024,
    ]);
}

test('quadra não aprovada não aparece na busca pública nem aceita reserva', function () {
    $quadra = Quadra::factory()->create(['status_cadastro' => StatusCadastro::Pendente, 'nome' => 'Arena Escondida']);
    $jogador = User::factory()->create();

    Livewire::test(ListagemPublica::class)->assertDontSee('Arena Escondida');

    Livewire::actingAs($jogador)->test(ListagemPublica::class)
        ->call('selecionarQuadra', $quadra->id)
        ->set('data', now()->addDay()->toDateString())
        ->set('horaInicio', '10:00')
        ->call('reservar')
        ->assertStatus(404);
});

test('usuário que não é admin não consegue aprovar proprietário nem quadra', function () {
    $dono = User::factory()->donoQuadra()->statusCadastro(StatusCadastro::Pendente)->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'status_cadastro' => StatusCadastro::Pendente]);
    $jogador = User::factory()->create();

    $this->actingAs($jogador)->get(route('admin.aprovacoes.proprietario', $dono))->assertForbidden();
    $this->actingAs($jogador)->get(route('admin.aprovacoes.quadra', $quadra))->assertForbidden();
});

test('rejeição sem motivo é bloqueada tanto no serviço quanto na tela', function () {
    $admin = User::factory()->admin()->create();
    $dono = User::factory()->donoQuadra()->statusCadastro(StatusCadastro::EmAnalise)->create();

    expect(fn () => app(CadastroAprovacaoService::class)->rejeitar($dono, $admin, '   '))
        ->toThrow(InvalidArgumentException::class);

    Livewire::actingAs($admin)->test(AnalisarProprietario::class, ['usuario' => $dono])
        ->call('abrirConfirmacao', 'rejeitar')
        ->set('motivo', '')
        ->call('confirmar')
        ->assertHasErrors(['motivo' => 'required']);

    expect($dono->fresh()->status_cadastro)->toBe(StatusCadastro::EmAnalise);
});

test('fluxo completo: ajustes solicitados, dono reenvia e admin aprova', function () {
    $admin = User::factory()->admin()->create();
    $dono = User::factory()->donoQuadra()->statusCadastro(StatusCadastro::Aprovado)->create();
    $quadra = Quadra::factory()->pendente()->create(['dono_id' => $dono->id]);
    prepararQuadraParaEdicao($quadra);

    // Admin abre a análise (pendente -> em_analise) e pede ajustes.
    Livewire::actingAs($admin)->test(AnalisarQuadra::class, ['quadra' => $quadra])
        ->call('abrirConfirmacao', 'ajustes')
        ->set('motivo', 'Corrija o endereço informado.')
        ->call('confirmar');

    expect($quadra->fresh()->status_cadastro)->toBe(StatusCadastro::AjustesSolicitados);

    // Dono corrige e salva — o Formulario reenvia automaticamente.
    Livewire::actingAs($dono)->test(Formulario::class, ['quadra' => $quadra])
        ->set('endereco', 'Rua Corrigida, 123')
        ->call('salvar');

    expect($quadra->fresh()->status_cadastro)->toBe(StatusCadastro::Pendente);

    // Admin reabre (pendente -> em_analise) e aprova.
    Livewire::actingAs($admin)->test(AnalisarQuadra::class, ['quadra' => $quadra])
        ->call('abrirConfirmacao', 'aprovar')
        ->call('confirmar');

    expect($quadra->fresh()->status_cadastro)->toBe(StatusCadastro::Aprovado);
});

test('quadra não pode ser aprovada enquanto o proprietário não está aprovado', function () {
    $admin = User::factory()->admin()->create();
    $dono = User::factory()->donoQuadra()->statusCadastro(StatusCadastro::Pendente)->create();
    $quadra = Quadra::factory()->pendente()->create(['dono_id' => $dono->id]);

    Livewire::actingAs($admin)->test(AnalisarQuadra::class, ['quadra' => $quadra])
        ->call('abrirConfirmacao', 'aprovar')
        ->call('confirmar');

    expect($quadra->fresh()->status_cadastro)->not->toBe(StatusCadastro::Aprovado);
});

test('editar endereço de uma quadra aprovada volta o status para em análise', function () {
    $dono = User::factory()->donoQuadra()->statusCadastro(StatusCadastro::Aprovado)->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'endereco' => 'Rua Antiga, 100']);
    prepararQuadraParaEdicao($quadra);

    Livewire::actingAs($dono)->test(Formulario::class, ['quadra' => $quadra])
        ->set('endereco', 'Rua Nova, 500')
        ->call('salvar');

    expect($quadra->fresh()->status_cadastro)->toBe(StatusCadastro::EmAnalise);
});

test('editar apenas o preço de uma quadra aprovada não muda o status', function () {
    $dono = User::factory()->donoQuadra()->statusCadastro(StatusCadastro::Aprovado)->create();
    $quadra = Quadra::factory()->create(['dono_id' => $dono->id, 'valor_hora' => 80]);
    prepararQuadraParaEdicao($quadra);

    Livewire::actingAs($dono)->test(Formulario::class, ['quadra' => $quadra])
        ->set('valor_hora', '120')
        ->call('salvar');

    expect($quadra->fresh())
        ->status_cadastro->toBe(StatusCadastro::Aprovado)
        ->valor_hora->toEqual('120.00');
});
