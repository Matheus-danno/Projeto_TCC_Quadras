<?php

namespace Database\Seeders;

use App\Enums\Esporte;
use App\Enums\PedidoStatus;
use App\Enums\ReservaStatus;
use App\Enums\StatusCadastro;
use App\Enums\StatusPagamento;
use App\Enums\TipoDocumento;
use App\Enums\UserRole;
use App\Models\CadastroHistorico;
use App\Models\ComissaoConfiguracao;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Recria o banco com dados realistas para demonstração/apresentação.
 *
 * php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('participacao_salas')->delete();
        DB::table('documentos')->delete();
        DB::table('cadastro_historicos')->delete();
        ItemPedido::query()->delete();
        Pedido::query()->delete();
        ComissaoConfiguracao::query()->delete();
        Reserva::query()->delete();
        Sala::query()->delete();
        Quadra::query()->delete();
        Produto::query()->delete();
        User::query()->delete();

        $this->call(AdminSeeder::class);

        $dono = User::factory()->create([
            'name' => 'Carlos Andrade',
            'email' => 'dono@demo.com',
            'role' => UserRole::DonoQuadra,
        ]);

        $outroDono = User::factory()->create([
            'name' => 'Fernanda Lima',
            'email' => 'dono2@demo.com',
            'role' => UserRole::DonoQuadra,
        ]);

        $jogador = User::factory()->create([
            'name' => 'Ana Beatriz Souza',
            'email' => 'jogador@demo.com',
            'role' => UserRole::Jogador,
        ]);

        $outrosJogadores = User::factory()->count(4)->create();

        $quadras = collect([
            ['nome' => 'Arena Vila Nova', 'endereco' => 'Rua das Palmeiras, 120', 'cidade' => 'Recife', 'bairro' => 'Boa Viagem', 'cep' => '51020-010', 'capacidade_maxima' => 22, 'latitude' => -8.1225, 'longitude' => -34.9020, 'esporte' => Esporte::Futebol, 'valor_hora' => 90, 'cobertura' => false, 'descricao' => 'Gramado sintético, vestiário e estacionamento.'],
            ['nome' => 'Quadra Central Futsal', 'endereco' => 'Av. Norte, 450', 'cidade' => 'Recife', 'bairro' => 'Casa Forte', 'cep' => '52061-000', 'capacidade_maxima' => 10, 'latitude' => -8.0304, 'longitude' => -34.9137, 'esporte' => Esporte::Futsal, 'valor_hora' => 70, 'cobertura' => true, 'descricao' => 'Piso emborrachado, coberta, boa para jogos à noite.'],
            ['nome' => 'Espaço Bela Vista Vôlei', 'endereco' => 'Rua da Praia, 88', 'cidade' => 'Olinda', 'bairro' => 'Bairro Novo', 'cep' => '53030-260', 'capacidade_maxima' => 12, 'latitude' => -7.9964, 'longitude' => -34.8388, 'esporte' => Esporte::Volei, 'valor_hora' => 60, 'cobertura' => false, 'descricao' => 'Quadra de areia a poucos metros da praia.'],
            ['nome' => 'Clube Recreativo Vôlei de Areia', 'endereco' => 'Rua dos Girassóis, 200', 'cidade' => 'Recife', 'bairro' => 'Madalena', 'cep' => '50710-060', 'capacidade_maxima' => 8, 'latitude' => -8.0578, 'longitude' => -34.9134, 'esporte' => Esporte::VoleiPraia, 'valor_hora' => 65, 'cobertura' => false, 'descricao' => 'Caixa de areia oficial e chuveiro externo.'],
            ['nome' => 'Tênis Clube Jardins', 'endereco' => 'Av. dos Ipês, 900', 'cidade' => 'Jaboatão dos Guararapes', 'bairro' => 'Piedade', 'cep' => '54410-010', 'capacidade_maxima' => 4, 'latitude' => -8.1780, 'longitude' => -34.9280, 'esporte' => Esporte::Tenis, 'valor_hora' => 110, 'cobertura' => false, 'descricao' => 'Piso rápido, iluminação para jogos à noite.'],
            ['nome' => 'Beach Arena Paiva', 'endereco' => 'Av. Beira Mar, 15', 'cidade' => 'Jaboatão dos Guararapes', 'bairro' => 'Candeias', 'cep' => '54470-060', 'capacidade_maxima' => 4, 'latitude' => -8.1740, 'longitude' => -34.9060, 'esporte' => Esporte::BeachTennis, 'valor_hora' => 80, 'cobertura' => false, 'descricao' => 'Duas quadras de areia, bar no local.'],
        ])->map(fn (array $dados) => Quadra::create([
            'dono_id' => $dono->id,
            'nome' => $dados['nome'],
            'endereco' => $dados['endereco'],
            'cidade' => $dados['cidade'],
            'bairro' => $dados['bairro'],
            'cep' => $dados['cep'],
            'capacidade_maxima' => $dados['capacidade_maxima'],
            'latitude' => $dados['latitude'],
            'longitude' => $dados['longitude'],
            'esporte' => $dados['esporte']->value,
            'valor_hora' => $dados['valor_hora'],
            'cobertura' => $dados['cobertura'],
            'descricao' => $dados['descricao'],
            'status_cadastro' => StatusCadastro::Aprovado,
        ]));

        $quadrasDono2 = collect([
            ['nome' => 'Quadra Boa Vista Society', 'endereco' => 'Rua Treze de Maio, 340', 'cidade' => 'Caruaru', 'bairro' => 'Boa Vista', 'cep' => '55010-140', 'capacidade_maxima' => 14, 'latitude' => -8.2850, 'longitude' => -35.9700, 'esporte' => Esporte::Futebol, 'valor_hora' => 75, 'cobertura' => false, 'descricao' => 'Gramado sintético novo, próximo ao centro.'],
            ['nome' => 'Arena Estrela Tênis', 'endereco' => 'Av. Agamenon Magalhães, 510', 'cidade' => 'Caruaru', 'bairro' => 'Indianópolis', 'cep' => '55030-580', 'capacidade_maxima' => 4, 'latitude' => -8.2700, 'longitude' => -35.9600, 'esporte' => Esporte::Tenis, 'valor_hora' => 60, 'cobertura' => true, 'descricao' => 'Quadra coberta com marcação oficial.'],
        ])->map(fn (array $dados) => Quadra::create([
            'dono_id' => $outroDono->id,
            'nome' => $dados['nome'],
            'endereco' => $dados['endereco'],
            'cidade' => $dados['cidade'],
            'bairro' => $dados['bairro'],
            'cep' => $dados['cep'],
            'capacidade_maxima' => $dados['capacidade_maxima'],
            'latitude' => $dados['latitude'],
            'longitude' => $dados['longitude'],
            'esporte' => $dados['esporte']->value,
            'valor_hora' => $dados['valor_hora'],
            'cobertura' => $dados['cobertura'],
            'descricao' => $dados['descricao'],
            'status_cadastro' => StatusCadastro::Aprovado,
        ]));

        // Quadras em Bauru/SP, para testar a busca por geolocalização a partir daí.
        $quadrasBauru = collect([
            ['nome' => 'Arena Bauru Centro', 'endereco' => 'Rua Batista de Carvalho, 500', 'cidade' => 'Bauru', 'bairro' => 'Centro', 'cep' => '17010-100', 'capacidade_maxima' => 22, 'latitude' => -22.3155, 'longitude' => -49.0619, 'esporte' => Esporte::Futebol, 'valor_hora' => 85, 'cobertura' => false, 'descricao' => 'Gramado sintético no coração da cidade.'],
            ['nome' => 'Ginásio Vila Falcão', 'endereco' => 'Av. Nações Unidas, 1200', 'cidade' => 'Bauru', 'bairro' => 'Vila Falcão', 'cep' => '17012-500', 'capacidade_maxima' => 10, 'latitude' => -22.3389, 'longitude' => -49.0562, 'esporte' => Esporte::Futsal, 'valor_hora' => 68, 'cobertura' => true, 'descricao' => 'Quadra coberta com arquibancada.'],
            ['nome' => 'Quadra Jardim Redentor', 'endereco' => 'Rua Aristides Marson, 300', 'cidade' => 'Bauru', 'bairro' => 'Jardim Redentor', 'cep' => '17033-360', 'capacidade_maxima' => 12, 'latitude' => -22.2963, 'longitude' => -49.0329, 'esporte' => Esporte::Volei, 'valor_hora' => 55, 'cobertura' => false, 'descricao' => 'Piso emborrachado, bebedouro no local.'],
            ['nome' => 'Clube Vila Universitária', 'endereco' => 'Av. Eng. Luiz Edmundo C. Coube, 890', 'cidade' => 'Bauru', 'bairro' => 'Vila Universitária', 'cep' => '17033-360', 'capacidade_maxima' => 4, 'latitude' => -22.3548, 'longitude' => -49.0288, 'esporte' => Esporte::Tenis, 'valor_hora' => 95, 'cobertura' => false, 'descricao' => 'Perto da Unesp, iluminação noturna.'],
            ['nome' => 'Espaço Altos da Cidade', 'endereco' => 'Rua Rio Branco, 1450', 'cidade' => 'Bauru', 'bairro' => 'Altos da Cidade', 'cep' => '17012-070', 'capacidade_maxima' => 4, 'latitude' => -22.3097, 'longitude' => -49.0669, 'esporte' => Esporte::BeachTennis, 'valor_hora' => 72, 'cobertura' => false, 'descricao' => 'Caixa de areia nova, estacionamento próprio.'],
        ])->map(fn (array $dados) => Quadra::create([
            'dono_id' => $dono->id,
            'nome' => $dados['nome'],
            'endereco' => $dados['endereco'],
            'cidade' => $dados['cidade'],
            'bairro' => $dados['bairro'],
            'cep' => $dados['cep'],
            'capacidade_maxima' => $dados['capacidade_maxima'],
            'latitude' => $dados['latitude'],
            'longitude' => $dados['longitude'],
            'esporte' => $dados['esporte']->value,
            'valor_hora' => $dados['valor_hora'],
            'cobertura' => $dados['cobertura'],
            'descricao' => $dados['descricao'],
            'status_cadastro' => StatusCadastro::Aprovado,
        ]));

        Sala::create([
            'nome' => 'Racha do Centro',
            'esporte' => Esporte::Futebol->value,
            'quadra_id' => $quadrasBauru[0]->id,
            'criador_id' => $outrosJogadores->random()->id,
            'max_participantes' => 14,
        ])->participantes()->attach($jogador->id);

        foreach ([
            ['nome' => 'Racha de quinta', 'esporte' => Esporte::Futebol, 'quadra' => 0, 'max' => 14],
            ['nome' => 'Futsal do trabalho', 'esporte' => Esporte::Futsal, 'quadra' => 1, 'max' => 10],
            ['nome' => 'Vôlei de praia iniciantes', 'esporte' => Esporte::Volei, 'quadra' => 2, 'max' => 8],
            ['nome' => 'Vôlei de areia 3x3', 'esporte' => Esporte::VoleiPraia, 'quadra' => 3, 'max' => 6],
        ] as $dados) {
            $sala = Sala::create([
                'nome' => $dados['nome'],
                'esporte' => $dados['esporte']->value,
                'quadra_id' => $quadras[$dados['quadra']]->id,
                'criador_id' => $outrosJogadores->random()->id,
                'max_participantes' => $dados['max'],
            ]);

            $sala->participantes()->attach($jogador->id);
            $sala->participantes()->attach($outrosJogadores->random(2)->pluck('id'));
        }

        Reserva::create([
            'quadra_id' => $quadras[0]->id,
            'user_id' => $jogador->id,
            'data' => now()->addDays(2)->toDateString(),
            'hora_inicio' => '19:00:00',
            'hora_fim' => '20:00:00',
            'status' => ReservaStatus::Confirmada,
        ]);

        Reserva::create([
            'quadra_id' => $quadras[2]->id,
            'user_id' => $jogador->id,
            'data' => now()->subDays(5)->toDateString(),
            'hora_inicio' => '17:00:00',
            'hora_fim' => '18:00:00',
            'status' => ReservaStatus::Confirmada,
        ]);

        Reserva::create([
            'quadra_id' => $quadras[1]->id,
            'user_id' => $outrosJogadores->random()->id,
            'data' => now()->addDays(3)->toDateString(),
            'hora_inicio' => '20:00:00',
            'hora_fim' => '21:00:00',
            'status' => ReservaStatus::Pendente,
        ]);

        Reserva::create([
            'quadra_id' => $quadras[4]->id,
            'user_id' => $outrosJogadores->random()->id,
            'data' => now()->subDays(10)->toDateString(),
            'hora_inicio' => '09:00:00',
            'hora_fim' => '10:00:00',
            'status' => ReservaStatus::Cancelada,
        ]);

        Reserva::create([
            'quadra_id' => $quadrasDono2[0]->id,
            'user_id' => $jogador->id,
            'data' => now()->addDays(1)->toDateString(),
            'hora_inicio' => '18:00:00',
            'hora_fim' => '19:00:00',
            'status' => ReservaStatus::Pendente,
        ]);

        Reserva::create([
            'quadra_id' => $quadrasDono2[1]->id,
            'user_id' => $outrosJogadores->random()->id,
            'data' => now()->subDays(2)->toDateString(),
            'hora_inicio' => '16:00:00',
            'hora_fim' => '17:00:00',
            'status' => ReservaStatus::Confirmada,
        ]);

        $this->criarDadosAdminDemo($jogador, $outrosJogadores, $quadras, $quadrasDono2, $quadrasBauru);
        $this->criarDadosAprovacaoDemo($dono);

        $produtos = collect([
            ['nome' => 'Bola de Futebol Society', 'descricao' => 'Bola oficial para gramado sintético.', 'categoria' => 'Acessórios', 'preco' => 89.90, 'estoque' => 25, 'dono_id' => $dono->id],
            ['nome' => 'Camisa Dry-Fit AlugaQuadra', 'descricao' => 'Tecido leve, ideal para dias quentes.', 'categoria' => 'Vestuário', 'preco' => 79.90, 'estoque' => 40, 'dono_id' => $dono->id],
            ['nome' => 'Luvas de Goleiro Profissional', 'descricao' => 'Aderência reforçada, tamanhos P ao GG.', 'categoria' => 'Acessórios', 'preco' => 149.90, 'estoque' => 0, 'dono_id' => $dono->id],
            ['nome' => 'Joelheira de Vôlei', 'descricao' => 'Par com proteção acolchoada.', 'categoria' => 'Acessórios', 'preco' => 49.90, 'estoque' => 18, 'dono_id' => $dono->id],
            ['nome' => 'Squeeze Térmica 1L', 'descricao' => 'Mantém a bebida gelada por até 12h.', 'categoria' => 'Hidratação', 'preco' => 59.90, 'estoque' => 32, 'dono_id' => $outroDono->id],
            ['nome' => 'Kit Coletes Numerados (10 un.)', 'descricao' => 'Ideal para organizar os times na sala.', 'categoria' => 'Acessórios', 'preco' => 199.90, 'estoque' => 12, 'dono_id' => $outroDono->id],
            ['nome' => 'Chuteira Society Turf', 'descricao' => 'Solado com travas curtas para gramado sintético.', 'categoria' => 'Calçados', 'preco' => 219.90, 'estoque' => 15, 'dono_id' => $outroDono->id],
            ['nome' => 'Tênis de Vôlei Antiderrapante', 'descricao' => 'Solado emborrachado com maior aderência em quadra.', 'categoria' => 'Calçados', 'preco' => 259.90, 'estoque' => 9, 'dono_id' => $outroDono->id],
        ])->map(fn (array $dados) => Produto::create($dados));

        $this->criarPedidosDemo($jogador, $produtos);

        $this->command?->info('Dados de demonstração criados.');
        $this->command?->table(['Papel', 'E-mail', 'Senha'], [
            ['Jogador', 'jogador@demo.com', 'password'],
            ['Dono de quadra', 'dono@demo.com', 'password'],
            ['Dono de quadra (2)', 'dono2@demo.com', 'password'],
            ['Administrador', 'admin@demo.com', 'password'],
        ]);
    }

    /**
     * Popula dados para a área do admin não nascer vazia na demonstração:
     * reservas confirmadas e pagas espalhadas pelos últimos ~4 meses (para
     * o gráfico e os rankings do dashboard), uma reserva paga e depois
     * cancelada com reembolso (para mostrar o estorno de comissão), uma
     * quadra pendente de aprovação, uma suspensa e um jogador bloqueado.
     */
    private function criarDadosAdminDemo(
        User $jogador,
        Collection $outrosJogadores,
        Collection $quadras,
        Collection $quadrasDono2,
        Collection $quadrasBauru,
    ): void {
        $quadrasComissao = $quadras->concat($quadrasBauru)->concat($quadrasDono2)->values();
        $clientes = $outrosJogadores->concat([$jogador])->values();

        $diasAtras = [2, 5, 9, 14, 18, 23, 30, 37, 45, 52, 60, 68, 75, 83, 90, 98, 105, 112, 118, 125];
        $horarios = ['08:00', '09:00', '18:00', '19:00', '19:00', '20:00', '20:00', '21:00', '10:00', '17:00'];

        foreach ($diasAtras as $indice => $dias) {
            $quadra = $quadrasComissao[$indice % $quadrasComissao->count()];
            $horaInicio = $horarios[$indice % count($horarios)];
            $horaFim = date('H:i', strtotime($horaInicio.' +1 hour'));

            Reserva::create([
                'quadra_id' => $quadra->id,
                'user_id' => $clientes->random()->id,
                'data' => now()->subDays($dias)->toDateString(),
                'hora_inicio' => $horaInicio.':00',
                'hora_fim' => $horaFim.':00',
                'status' => ReservaStatus::Confirmada,
                'status_pagamento' => StatusPagamento::Pago,
                'metodo_pagamento' => 'pix',
                'valor' => $quadra->valor_hora,
            ]);
        }

        $reservaComEstorno = Reserva::create([
            'quadra_id' => $quadras[0]->id,
            'user_id' => $jogador->id,
            'data' => now()->subDays(20)->toDateString(),
            'hora_inicio' => '20:00:00',
            'hora_fim' => '21:00:00',
            'status' => ReservaStatus::Confirmada,
            'status_pagamento' => StatusPagamento::Pago,
            'metodo_pagamento' => 'pix',
            'valor' => $quadras[0]->valor_hora,
        ]);

        $jogador->increment('saldo_creditos', (float) $reservaComEstorno->valor);

        $reservaComEstorno->update([
            'status' => ReservaStatus::Cancelada,
            'cancelamento_tipo' => 'credito',
            'motivo_cancelamento' => 'Chuva forte no horário marcado.',
        ]);

        $quadrasBauru[4]->update(['status_cadastro' => StatusCadastro::Pendente]);
        $quadrasDono2[1]->update(['status_cadastro' => StatusCadastro::Suspenso]);

        $outrosJogadores->first()->update(['bloqueado_em' => now()->subDays(3)]);
    }

    /**
     * Cria proprietários e quadras em cada status do fluxo de aprovação
     * (pendente, em análise, ajustes solicitados, aprovado e rejeitado),
     * com documento de identidade/comprovante e histórico de exemplo, para
     * testar a Fila de aprovações do admin e a tela de status do dono.
     */
    private function criarDadosAprovacaoDemo(User $donoAprovado): void
    {
        $admin = User::where('email', 'admin@demo.com')->first();

        $criarDono = function (string $nome, string $email, StatusCadastro $status, int $diasDesdeCadastro) {
            $dono = User::factory()->create([
                'name' => $nome,
                'nome_estabelecimento' => $nome.' Esportes',
                'email' => $email,
                'role' => UserRole::DonoQuadra,
                'cnpj' => fake()->unique()->numerify('##############'),
                'cpf' => null,
                'status_cadastro' => $status,
                'chave_pix_recebimento' => $email,
                'titular_recebimento_nome' => $nome,
                'titular_recebimento_documento' => null,
                'comissao_aceita_em' => now(),
            ]);

            $dono->forceFill(['created_at' => now()->subDays($diasDesdeCadastro)])->save();

            return $dono;
        };

        $anexarDocumento = function (User|Quadra $dono, TipoDocumento $tipo, string $nomeArquivo) {
            $caminho = 'documentos/demo/'.Str::uuid().'-'.$nomeArquivo;
            Storage::disk('local')->put($caminho, "Documento de demonstração ({$tipo->label()}).");

            $dono->documentos()->create([
                'tipo' => $tipo,
                'caminho' => $caminho,
                'nome_original' => $nomeArquivo,
                'tamanho_bytes' => Storage::disk('local')->size($caminho),
            ]);
        };

        $registrarEvento = function (User|Quadra $entidade, string $evento, ?StatusCadastro $statusAnterior, StatusCadastro $statusNovo, ?string $motivo, ?User $admin, int $diasAtras) {
            CadastroHistorico::create([
                'avaliavel_type' => $entidade::class,
                'avaliavel_id' => $entidade->id,
                'evento' => $evento,
                'status_anterior' => $statusAnterior?->value,
                'status_novo' => $statusNovo->value,
                'motivo' => $motivo,
                'admin_id' => $admin?->id,
                'created_at' => now()->subDays($diasAtras),
                'updated_at' => now()->subDays($diasAtras),
            ]);
        };

        // Pendente: cadastro novo, nunca foi aberto por um admin.
        $donoPendente = $criarDono('Roberto Mendes', 'dono.pendente@demo.com', StatusCadastro::Pendente, 2);
        $anexarDocumento($donoPendente, TipoDocumento::DocumentoIdentidade, 'rg-roberto.jpg');
        $registrarEvento($donoPendente, 'enviado', null, StatusCadastro::Pendente, null, null, 2);

        $quadraDoPendente = Quadra::factory()->pendente()->create([
            'dono_id' => $donoPendente->id,
            'nome' => 'Arena Roberto Mendes',
            'cidade' => 'Recife',
        ]);
        $anexarDocumento($quadraDoPendente, TipoDocumento::ComprovanteImovel, 'contrato-locacao.pdf');
        $registrarEvento($quadraDoPendente, 'enviado', null, StatusCadastro::Pendente, null, null, 2);

        // Em análise: um admin já abriu e está olhando.
        $donoEmAnalise = $criarDono('Patrícia Gomes', 'dono.analise@demo.com', StatusCadastro::EmAnalise, 5);
        $anexarDocumento($donoEmAnalise, TipoDocumento::DocumentoIdentidade, 'cnh-patricia.jpg');
        $registrarEvento($donoEmAnalise, 'enviado', null, StatusCadastro::Pendente, null, null, 5);
        $registrarEvento($donoEmAnalise, 'em_analise', StatusCadastro::Pendente, StatusCadastro::EmAnalise, null, $admin, 1);

        $quadraEmAnalise = Quadra::factory()->pendente()->create([
            'dono_id' => $donoAprovado->id,
            'nome' => 'Quadra Nova em Análise',
            'status_cadastro' => StatusCadastro::EmAnalise,
        ]);
        $anexarDocumento($quadraEmAnalise, TipoDocumento::ComprovanteImovel, 'escritura-quadra-nova.pdf');
        $registrarEvento($quadraEmAnalise, 'enviado', null, StatusCadastro::Pendente, null, null, 1);
        $registrarEvento($quadraEmAnalise, 'em_analise', StatusCadastro::Pendente, StatusCadastro::EmAnalise, null, $admin, 0);

        // Ajustes solicitados: já recebeu uma decisão, aguardando reenvio do dono.
        $donoAjustes = $criarDono('Marcos Teixeira', 'dono.ajustes@demo.com', StatusCadastro::AjustesSolicitados, 8);
        $anexarDocumento($donoAjustes, TipoDocumento::DocumentoIdentidade, 'rg-marcos.jpg');
        $registrarEvento($donoAjustes, 'enviado', null, StatusCadastro::Pendente, null, null, 8);
        $registrarEvento($donoAjustes, 'em_analise', StatusCadastro::Pendente, StatusCadastro::EmAnalise, null, $admin, 4);
        $registrarEvento($donoAjustes, 'ajustes_solicitados', StatusCadastro::EmAnalise, StatusCadastro::AjustesSolicitados, 'O documento de identidade está ilegível. Envie uma foto mais nítida, com todos os cantos visíveis.', $admin, 3);
        $donoAjustes->forceFill(['updated_at' => now()->subDays(3)])->save();

        $quadraAjustes = Quadra::factory()->pendente()->create([
            'dono_id' => $donoAprovado->id,
            'nome' => 'Quadra com Ajustes Pendentes',
            'status_cadastro' => StatusCadastro::AjustesSolicitados,
        ]);
        $anexarDocumento($quadraAjustes, TipoDocumento::ComprovanteImovel, 'contrato-quadra-ajustes.pdf');
        $registrarEvento($quadraAjustes, 'enviado', null, StatusCadastro::Pendente, null, null, 3);
        $registrarEvento($quadraAjustes, 'ajustes_solicitados', StatusCadastro::Pendente, StatusCadastro::AjustesSolicitados, 'As fotos enviadas não mostram bem a condição do piso. Envie fotos mais claras, com boa iluminação.', $admin, 2);
        $quadraAjustes->forceFill(['updated_at' => now()->subDays(2)])->save();

        // Rejeitado.
        $donoRejeitado = $criarDono('Juliana Prado', 'dono.rejeitado@demo.com', StatusCadastro::Rejeitado, 10);
        $anexarDocumento($donoRejeitado, TipoDocumento::DocumentoIdentidade, 'rg-juliana.jpg');
        $registrarEvento($donoRejeitado, 'enviado', null, StatusCadastro::Pendente, null, null, 10);
        $registrarEvento($donoRejeitado, 'em_analise', StatusCadastro::Pendente, StatusCadastro::EmAnalise, null, $admin, 7);
        $registrarEvento($donoRejeitado, 'rejeitado', StatusCadastro::EmAnalise, StatusCadastro::Rejeitado, 'O CNPJ informado não corresponde a uma empresa ativa na Receita Federal.', $admin, 6);
        $donoRejeitado->forceFill(['updated_at' => now()->subDays(6)])->save();

        $quadraRejeitada = Quadra::factory()->pendente()->create([
            'dono_id' => $donoAprovado->id,
            'nome' => 'Quadra Rejeitada',
            'status_cadastro' => StatusCadastro::Rejeitado,
        ]);
        $anexarDocumento($quadraRejeitada, TipoDocumento::ComprovanteImovel, 'comprovante-vencido.pdf');
        $registrarEvento($quadraRejeitada, 'enviado', null, StatusCadastro::Pendente, null, null, 4);
        $registrarEvento($quadraRejeitada, 'rejeitado', StatusCadastro::Pendente, StatusCadastro::Rejeitado, 'O comprovante de uso do imóvel está vencido. Envie um documento válido.', $admin, 2);
        $quadraRejeitada->forceFill(['updated_at' => now()->subDays(2)])->save();

        // Aprovado pelo fluxo completo (além dos donos antigos, migrados direto
        // como aprovados) — enriquece o "tempo médio de análise" do dashboard.
        $donoAprovadoPeloFluxo = $criarDono('Eduardo Ramos', 'dono.fluxo-completo@demo.com', StatusCadastro::Aprovado, 7);
        $anexarDocumento($donoAprovadoPeloFluxo, TipoDocumento::DocumentoIdentidade, 'rg-eduardo.jpg');
        $registrarEvento($donoAprovadoPeloFluxo, 'enviado', null, StatusCadastro::Pendente, null, null, 7);
        $registrarEvento($donoAprovadoPeloFluxo, 'em_analise', StatusCadastro::Pendente, StatusCadastro::EmAnalise, null, $admin, 6);
        $registrarEvento($donoAprovadoPeloFluxo, 'aprovado', StatusCadastro::EmAnalise, StatusCadastro::Aprovado, null, $admin, 5);
        $donoAprovadoPeloFluxo->forceFill(['updated_at' => now()->subDays(5)])->save();

        $this->command?->table(['Cadastro (proprietário)', 'E-mail', 'Status'], [
            ['Roberto Mendes', 'dono.pendente@demo.com', 'Pendente'],
            ['Patrícia Gomes', 'dono.analise@demo.com', 'Em análise'],
            ['Marcos Teixeira', 'dono.ajustes@demo.com', 'Ajustes solicitados'],
            ['Juliana Prado', 'dono.rejeitado@demo.com', 'Rejeitado'],
            ['Eduardo Ramos', 'dono.fluxo-completo@demo.com', 'Aprovado (via fluxo)'],
        ]);
    }

    /**
     * Cria pedidos de exemplo (jogador comprando produtos do dono) para a
     * tela de Pedidos do painel não nascer vazia na demonstração.
     */
    private function criarPedidosDemo(User $jogador, Collection $produtos): void
    {
        $bola = $produtos->firstWhere('nome', 'Bola de Futebol Society');
        $camisa = $produtos->firstWhere('nome', 'Camisa Dry-Fit AlugaQuadra');

        $pedidoAguardando = Pedido::create([
            'user_id' => $jogador->id,
            'dono_id' => $bola->dono_id,
            'status' => PedidoStatus::Aguardando,
            'total' => $bola->preco,
            'numero_retirada' => Pedido::gerarNumeroRetirada(),
            'lote_compra' => (string) Str::uuid(),
            'comissao_percentual' => 5.00,
            'comissao_valor' => round($bola->preco * 0.05, 2),
        ]);

        ItemPedido::create([
            'pedido_id' => $pedidoAguardando->id,
            'produto_id' => $bola->id,
            'quantidade' => 1,
            'preco_unitario' => $bola->preco,
        ]);

        $totalCamisas = $camisa->preco * 2;

        $pedidoRetirado = Pedido::create([
            'user_id' => $jogador->id,
            'dono_id' => $camisa->dono_id,
            'status' => PedidoStatus::Retirado,
            'total' => $totalCamisas,
            'numero_retirada' => Pedido::gerarNumeroRetirada(),
            'lote_compra' => (string) Str::uuid(),
            'comissao_percentual' => 5.00,
            'comissao_valor' => round($totalCamisas * 0.05, 2),
        ]);
        $pedidoRetirado->forceFill(['created_at' => now()->subDays(4)])->save();

        ItemPedido::create([
            'pedido_id' => $pedidoRetirado->id,
            'produto_id' => $camisa->id,
            'quantidade' => 2,
            'preco_unitario' => $camisa->preco,
        ]);
    }
}
