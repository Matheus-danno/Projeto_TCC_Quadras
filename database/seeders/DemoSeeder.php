<?php

namespace Database\Seeders;

use App\Enums\Esporte;
use App\Enums\ReservaStatus;
use App\Enums\UserRole;
use App\Models\Produto;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        Reserva::query()->delete();
        Sala::query()->delete();
        Quadra::query()->delete();
        Produto::query()->delete();
        User::query()->delete();

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

        User::factory()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@demo.com',
            'role' => UserRole::Admin,
        ]);

        $jogador = User::factory()->create([
            'name' => 'Ana Beatriz Souza',
            'email' => 'jogador@demo.com',
            'role' => UserRole::Jogador,
        ]);

        $outrosJogadores = User::factory()->count(4)->create();

        $quadras = collect([
            ['nome' => 'Arena Vila Nova', 'endereco' => 'Rua das Palmeiras, 120', 'cidade' => 'Recife', 'bairro' => 'Boa Viagem', 'latitude' => -8.1225, 'longitude' => -34.9020, 'esporte' => Esporte::Futebol, 'valor_hora' => 90, 'cobertura' => false, 'descricao' => 'Gramado sintético, vestiário e estacionamento.'],
            ['nome' => 'Quadra Central Futsal', 'endereco' => 'Av. Norte, 450', 'cidade' => 'Recife', 'bairro' => 'Casa Forte', 'latitude' => -8.0304, 'longitude' => -34.9137, 'esporte' => Esporte::Futsal, 'valor_hora' => 70, 'cobertura' => true, 'descricao' => 'Piso emborrachado, coberta, boa para jogos à noite.'],
            ['nome' => 'Espaço Bela Vista Vôlei', 'endereco' => 'Rua da Praia, 88', 'cidade' => 'Olinda', 'bairro' => 'Bairro Novo', 'latitude' => -7.9964, 'longitude' => -34.8388, 'esporte' => Esporte::Volei, 'valor_hora' => 60, 'cobertura' => false, 'descricao' => 'Quadra de areia a poucos metros da praia.'],
            ['nome' => 'Clube Recreativo Vôlei de Areia', 'endereco' => 'Rua dos Girassóis, 200', 'cidade' => 'Recife', 'bairro' => 'Madalena', 'latitude' => -8.0578, 'longitude' => -34.9134, 'esporte' => Esporte::VoleiPraia, 'valor_hora' => 65, 'cobertura' => false, 'descricao' => 'Caixa de areia oficial e chuveiro externo.'],
            ['nome' => 'Tênis Clube Jardins', 'endereco' => 'Av. dos Ipês, 900', 'cidade' => 'Jaboatão dos Guararapes', 'bairro' => 'Piedade', 'latitude' => -8.1780, 'longitude' => -34.9280, 'esporte' => Esporte::Tenis, 'valor_hora' => 110, 'cobertura' => false, 'descricao' => 'Piso rápido, iluminação para jogos à noite.'],
            ['nome' => 'Beach Arena Paiva', 'endereco' => 'Av. Beira Mar, 15', 'cidade' => 'Jaboatão dos Guararapes', 'bairro' => 'Candeias', 'latitude' => -8.1740, 'longitude' => -34.9060, 'esporte' => Esporte::BeachTennis, 'valor_hora' => 80, 'cobertura' => false, 'descricao' => 'Duas quadras de areia, bar no local.'],
        ])->map(fn (array $dados) => Quadra::create([
            'dono_id' => $dono->id,
            'nome' => $dados['nome'],
            'endereco' => $dados['endereco'],
            'cidade' => $dados['cidade'],
            'bairro' => $dados['bairro'],
            'latitude' => $dados['latitude'],
            'longitude' => $dados['longitude'],
            'esporte' => $dados['esporte']->value,
            'valor_hora' => $dados['valor_hora'],
            'cobertura' => $dados['cobertura'],
            'descricao' => $dados['descricao'],
        ]));

        $quadrasDono2 = collect([
            ['nome' => 'Quadra Boa Vista Society', 'endereco' => 'Rua Treze de Maio, 340', 'cidade' => 'Caruaru', 'bairro' => 'Boa Vista', 'latitude' => -8.2850, 'longitude' => -35.9700, 'esporte' => Esporte::Futebol, 'valor_hora' => 75, 'cobertura' => false, 'descricao' => 'Gramado sintético novo, próximo ao centro.'],
            ['nome' => 'Arena Estrela Tênis', 'endereco' => 'Av. Agamenon Magalhães, 510', 'cidade' => 'Caruaru', 'bairro' => 'Indianópolis', 'latitude' => -8.2700, 'longitude' => -35.9600, 'esporte' => Esporte::Tenis, 'valor_hora' => 60, 'cobertura' => true, 'descricao' => 'Quadra coberta com marcação oficial.'],
        ])->map(fn (array $dados) => Quadra::create([
            'dono_id' => $outroDono->id,
            'nome' => $dados['nome'],
            'endereco' => $dados['endereco'],
            'cidade' => $dados['cidade'],
            'bairro' => $dados['bairro'],
            'latitude' => $dados['latitude'],
            'longitude' => $dados['longitude'],
            'esporte' => $dados['esporte']->value,
            'valor_hora' => $dados['valor_hora'],
            'cobertura' => $dados['cobertura'],
            'descricao' => $dados['descricao'],
        ]));

        // Quadras em Bauru/SP, para testar a busca por geolocalização a partir daí.
        $quadrasBauru = collect([
            ['nome' => 'Arena Bauru Centro', 'endereco' => 'Rua Batista de Carvalho, 500', 'cidade' => 'Bauru', 'bairro' => 'Centro', 'latitude' => -22.3155, 'longitude' => -49.0619, 'esporte' => Esporte::Futebol, 'valor_hora' => 85, 'cobertura' => false, 'descricao' => 'Gramado sintético no coração da cidade.'],
            ['nome' => 'Ginásio Vila Falcão', 'endereco' => 'Av. Nações Unidas, 1200', 'cidade' => 'Bauru', 'bairro' => 'Vila Falcão', 'latitude' => -22.3389, 'longitude' => -49.0562, 'esporte' => Esporte::Futsal, 'valor_hora' => 68, 'cobertura' => true, 'descricao' => 'Quadra coberta com arquibancada.'],
            ['nome' => 'Quadra Jardim Redentor', 'endereco' => 'Rua Aristides Marson, 300', 'cidade' => 'Bauru', 'bairro' => 'Jardim Redentor', 'latitude' => -22.2963, 'longitude' => -49.0329, 'esporte' => Esporte::Volei, 'valor_hora' => 55, 'cobertura' => false, 'descricao' => 'Piso emborrachado, bebedouro no local.'],
            ['nome' => 'Clube Vila Universitária', 'endereco' => 'Av. Eng. Luiz Edmundo C. Coube, 890', 'cidade' => 'Bauru', 'bairro' => 'Vila Universitária', 'latitude' => -22.3548, 'longitude' => -49.0288, 'esporte' => Esporte::Tenis, 'valor_hora' => 95, 'cobertura' => false, 'descricao' => 'Perto da Unesp, iluminação noturna.'],
            ['nome' => 'Espaço Altos da Cidade', 'endereco' => 'Rua Rio Branco, 1450', 'cidade' => 'Bauru', 'bairro' => 'Altos da Cidade', 'latitude' => -22.3097, 'longitude' => -49.0669, 'esporte' => Esporte::BeachTennis, 'valor_hora' => 72, 'cobertura' => false, 'descricao' => 'Caixa de areia nova, estacionamento próprio.'],
        ])->map(fn (array $dados) => Quadra::create([
            'dono_id' => $dono->id,
            'nome' => $dados['nome'],
            'endereco' => $dados['endereco'],
            'cidade' => $dados['cidade'],
            'bairro' => $dados['bairro'],
            'latitude' => $dados['latitude'],
            'longitude' => $dados['longitude'],
            'esporte' => $dados['esporte']->value,
            'valor_hora' => $dados['valor_hora'],
            'cobertura' => $dados['cobertura'],
            'descricao' => $dados['descricao'],
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

        collect([
            ['nome' => 'Bola de Futebol Society', 'descricao' => 'Bola oficial para gramado sintético.', 'categoria' => 'Acessórios', 'preco' => 89.90, 'estoque' => 25],
            ['nome' => 'Camisa Dry-Fit AlugaQuadra', 'descricao' => 'Tecido leve, ideal para dias quentes.', 'categoria' => 'Vestuário', 'preco' => 79.90, 'estoque' => 40],
            ['nome' => 'Luvas de Goleiro Profissional', 'descricao' => 'Aderência reforçada, tamanhos P ao GG.', 'categoria' => 'Acessórios', 'preco' => 149.90, 'estoque' => 0],
            ['nome' => 'Joelheira de Vôlei', 'descricao' => 'Par com proteção acolchoada.', 'categoria' => 'Acessórios', 'preco' => 49.90, 'estoque' => 18],
            ['nome' => 'Squeeze Térmica 1L', 'descricao' => 'Mantém a bebida gelada por até 12h.', 'categoria' => 'Hidratação', 'preco' => 59.90, 'estoque' => 32],
            ['nome' => 'Kit Coletes Numerados (10 un.)', 'descricao' => 'Ideal para organizar os times na sala.', 'categoria' => 'Acessórios', 'preco' => 199.90, 'estoque' => 12],
            ['nome' => 'Chuteira Society Turf', 'descricao' => 'Solado com travas curtas para gramado sintético.', 'categoria' => 'Calçados', 'preco' => 219.90, 'estoque' => 15],
            ['nome' => 'Tênis de Vôlei Antiderrapante', 'descricao' => 'Solado emborrachado com maior aderência em quadra.', 'categoria' => 'Calçados', 'preco' => 259.90, 'estoque' => 9],
        ])->each(fn (array $dados) => Produto::create($dados));

        $this->command?->info('Dados de demonstração criados.');
        $this->command?->table(['Papel', 'E-mail', 'Senha'], [
            ['Jogador', 'jogador@demo.com', 'password'],
            ['Dono de quadra', 'dono@demo.com', 'password'],
            ['Dono de quadra (2)', 'dono2@demo.com', 'password'],
            ['Admin', 'admin@demo.com', 'password'],
        ]);
    }
}
