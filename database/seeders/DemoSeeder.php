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
            ['nome' => 'Arena Vila Nova', 'endereco' => 'Rua das Palmeiras, 120', 'cidade' => 'Recife', 'bairro' => 'Boa Viagem', 'esporte' => Esporte::Futebol, 'valor_hora' => 90, 'cobertura' => false, 'descricao' => 'Gramado sintético, vestiário e estacionamento.'],
            ['nome' => 'Quadra Central Futsal', 'endereco' => 'Av. Norte, 450', 'cidade' => 'Recife', 'bairro' => 'Casa Forte', 'esporte' => Esporte::Futsal, 'valor_hora' => 70, 'cobertura' => true, 'descricao' => 'Piso emborrachado, coberta, boa para jogos à noite.'],
            ['nome' => 'Espaço Bela Vista Vôlei', 'endereco' => 'Rua da Praia, 88', 'cidade' => 'Olinda', 'bairro' => 'Bairro Novo', 'esporte' => Esporte::Volei, 'valor_hora' => 60, 'cobertura' => false, 'descricao' => 'Quadra de areia a poucos metros da praia.'],
            ['nome' => 'Clube Recreativo Basquete', 'endereco' => 'Rua dos Girassóis, 200', 'cidade' => 'Recife', 'bairro' => 'Madalena', 'esporte' => Esporte::Basquete, 'valor_hora' => 65, 'cobertura' => true, 'descricao' => 'Tabelas oficiais e arquibancada pequena.'],
            ['nome' => 'Tênis Clube Jardins', 'endereco' => 'Av. dos Ipês, 900', 'cidade' => 'Jaboatão dos Guararapes', 'bairro' => 'Piedade', 'esporte' => Esporte::Tenis, 'valor_hora' => 110, 'cobertura' => false, 'descricao' => 'Piso rápido, iluminação para jogos à noite.'],
            ['nome' => 'Beach Arena Paiva', 'endereco' => 'Av. Beira Mar, 15', 'cidade' => 'Jaboatão dos Guararapes', 'bairro' => 'Candeias', 'esporte' => Esporte::BeachTennis, 'valor_hora' => 80, 'cobertura' => false, 'descricao' => 'Duas quadras de areia, bar no local.'],
        ])->map(fn (array $dados) => Quadra::create([
            'dono_id' => $dono->id,
            'nome' => $dados['nome'],
            'endereco' => $dados['endereco'],
            'cidade' => $dados['cidade'],
            'bairro' => $dados['bairro'],
            'esporte' => $dados['esporte']->value,
            'valor_hora' => $dados['valor_hora'],
            'cobertura' => $dados['cobertura'],
            'descricao' => $dados['descricao'],
        ]));

        foreach ([
            ['nome' => 'Racha de quinta', 'esporte' => Esporte::Futebol, 'quadra' => 0, 'max' => 14],
            ['nome' => 'Futsal do trabalho', 'esporte' => Esporte::Futsal, 'quadra' => 1, 'max' => 10],
            ['nome' => 'Vôlei de praia iniciantes', 'esporte' => Esporte::Volei, 'quadra' => 2, 'max' => 8],
            ['nome' => 'Basquete 3x3', 'esporte' => Esporte::Basquete, 'quadra' => 3, 'max' => 6],
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

        collect([
            ['nome' => 'Bola de Futebol Society', 'descricao' => 'Bola oficial para gramado sintético.', 'preco' => 89.90],
            ['nome' => 'Camisa Dry-Fit AlugaQuadra', 'descricao' => 'Tecido leve, ideal para dias quentes.', 'preco' => 79.90],
            ['nome' => 'Luvas de Goleiro Profissional', 'descricao' => 'Aderência reforçada, tamanhos P ao GG.', 'preco' => 149.90],
            ['nome' => 'Joelheira de Vôlei', 'descricao' => 'Par com proteção acolchoada.', 'preco' => 49.90],
            ['nome' => 'Squeeze Térmica 1L', 'descricao' => 'Mantém a bebida gelada por até 12h.', 'preco' => 59.90],
            ['nome' => 'Kit Coletes Numerados (10 un.)', 'descricao' => 'Ideal para organizar os times na sala.', 'preco' => 199.90],
        ])->each(fn (array $dados) => Produto::create($dados));

        $this->command?->info('Dados de demonstração criados.');
        $this->command?->table(['Papel', 'E-mail', 'Senha'], [
            ['Jogador', 'jogador@demo.com', 'password'],
            ['Dono de quadra', 'dono@demo.com', 'password'],
            ['Admin', 'admin@demo.com', 'password'],
        ]);
    }
}
