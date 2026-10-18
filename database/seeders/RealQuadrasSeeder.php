<?php

namespace Database\Seeders;

use App\Enums\Esporte;
use App\Enums\StatusCadastro;
use App\Enums\TipoPiso;
use App\Enums\UserRole;
use App\Models\Quadra;
use App\Models\QuadraFoto;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Cadastra quadras que existem de verdade em Bauru, Agudos e Piratininga
 * (SP), com nome, endereço e coordenadas reais (geocodificados via
 * Nominatim/OpenStreetMap). Cada quadra tem seu próprio dono, nomeado a
 * partir do próprio estabelecimento (não temos o nome do proprietário
 * real de cada um). Fotos são placeholders gerados localmente — não
 * reproduzimos fotos de terceiros encontradas na internet.
 *
 * php artisan db:seed --class=RealQuadrasSeeder
 */
class RealQuadrasSeeder extends Seeder
{
    public function run(): void
    {
        $quadras = collect([
            [
                'estabelecimento' => 'Aloha Arena Beach',
                'email' => 'aloha.arena@demo.com',
                'nome' => 'Aloha Arena Beach',
                'endereco' => 'Rua Jacy Stevaux Vilaça, 104',
                'bairro' => 'Jardim Contorno',
                'cidade' => 'Bauru',
                'cep' => '17047-350',
                'latitude' => -22.3355197,
                'longitude' => -49.0474379,
                'esporte' => Esporte::BeachTennis,
                'tipo_piso' => TipoPiso::Areia,
                'valor_hora' => 90,
                'capacidade_maxima' => 4,
                'cobertura' => true,
                'descricao' => 'Arena de beach tennis no Jardim Contorno, em Bauru.',
            ],
            [
                'estabelecimento' => 'Aranda Beach Tennis',
                'email' => 'aranda.beach@demo.com',
                'nome' => 'Aranda Beach Tennis',
                'endereco' => 'Rua Severino Lins, 99',
                'bairro' => 'Vila Aviação',
                'cidade' => 'Bauru',
                'cep' => '17018-600',
                'latitude' => -22.3533046,
                'longitude' => -49.0485418,
                'esporte' => Esporte::BeachTennis,
                'tipo_piso' => TipoPiso::Areia,
                'valor_hora' => 95,
                'capacidade_maxima' => 4,
                'cobertura' => true,
                'descricao' => 'Complexo com várias quadras de beach tennis, restaurante e loja especializada.',
            ],
            [
                'estabelecimento' => 'Bate Bola Aluguel de Campo Society',
                'email' => 'batebola.society@demo.com',
                'nome' => 'Bate Bola Campo Society',
                'endereco' => 'Rua Duque de Caxias, 26-80',
                'bairro' => 'Jardim Parque Paulistano',
                'cidade' => 'Bauru',
                'cep' => '17050-370',
                'latitude' => -22.3314923,
                'longitude' => -49.0795861,
                'esporte' => Esporte::Futebol,
                'tipo_piso' => TipoPiso::Sintetico,
                'valor_hora' => 110,
                'capacidade_maxima' => 14,
                'cobertura' => false,
                'descricao' => 'Campo society para aluguel avulso, perto do Viaduto da Rondon.',
            ],
            [
                'estabelecimento' => 'Ginásio Dr. Vicente Evaristo Damante',
                'email' => 'ginasio.agudos@demo.com',
                'nome' => 'Ginásio Dr. Vicente Evaristo Damante',
                'endereco' => 'Vila Professor Simões',
                'bairro' => 'Vila Professor Simões',
                'cidade' => 'Agudos',
                'cep' => '17120-003',
                'latitude' => -22.4700,
                'longitude' => -48.9870,
                'esporte' => Esporte::Futsal,
                'tipo_piso' => TipoPiso::Emborrachado,
                'valor_hora' => 70,
                'capacidade_maxima' => 10,
                'cobertura' => true,
                'descricao' => 'Ginásio poliesportivo municipal coberto, reinaugurado após reforma.',
            ],
            [
                'estabelecimento' => 'Estádio Municipal Achilles Sormani',
                'email' => 'estadio.agudos@demo.com',
                'nome' => 'Campo da Simão (Estádio Achilles Sormani)',
                'endereco' => 'Centro',
                'bairro' => 'Centro',
                'cidade' => 'Agudos',
                'cep' => '17120-003',
                'latitude' => -22.4739,
                'longitude' => -48.9899,
                'esporte' => Esporte::Futebol,
                'tipo_piso' => TipoPiso::Natural,
                'valor_hora' => 80,
                'capacidade_maxima' => 22,
                'cobertura' => false,
                'descricao' => 'Campo de futebol municipal usado para partidas e treinos na cidade.',
            ],
            [
                'estabelecimento' => 'Piratininga Tênis Clube',
                'email' => 'piratininga.tenis@demo.com',
                'nome' => 'Piratininga Tênis Clube',
                'endereco' => 'Rua Dona Jamile Maluf, 59',
                'bairro' => 'Centro',
                'cidade' => 'Piratininga',
                'cep' => '17490-000',
                'latitude' => -22.4107208,
                'longitude' => -49.1361136,
                'esporte' => Esporte::Tenis,
                'tipo_piso' => TipoPiso::Sintetico,
                'valor_hora' => 65,
                'capacidade_maxima' => 4,
                'cobertura' => false,
                'descricao' => 'Clube com quadra de tênis no centro de Piratininga.',
            ],
            [
                'estabelecimento' => 'Ginásio José Cocito',
                'email' => 'ginasio.piratininga@demo.com',
                'nome' => 'Ginásio José Cocito',
                'endereco' => 'Rua Jerônymo Ferreira',
                'bairro' => 'Centro',
                'cidade' => 'Piratininga',
                'cep' => '17490-000',
                'latitude' => -22.4165,
                'longitude' => -49.1340,
                'esporte' => Esporte::Futsal,
                'tipo_piso' => TipoPiso::Emborrachado,
                'valor_hora' => 60,
                'capacidade_maxima' => 10,
                'cobertura' => true,
                'descricao' => 'Ginásio poliesportivo municipal no centro da cidade.',
            ],
        ]);

        $quadras->each(function (array $dados) {
            $dono = User::firstOrCreate(
                ['email' => $dados['email']],
                User::factory()->donoQuadra()->make([
                    'name' => $dados['estabelecimento'],
                    'nome_estabelecimento' => $dados['estabelecimento'],
                    'email' => $dados['email'],
                    'cnpj' => fake()->unique()->numerify('##############'),
                    'cidade' => $dados['cidade'],
                    'estado' => 'SP',
                    'status_cadastro' => StatusCadastro::Aprovado,
                ])->getAttributes()
            );

            $quadra = Quadra::create([
                'dono_id' => $dono->id,
                'nome' => $dados['nome'],
                'endereco' => $dados['endereco'],
                'bairro' => $dados['bairro'],
                'cidade' => $dados['cidade'],
                'cep' => $dados['cep'],
                'latitude' => $dados['latitude'],
                'longitude' => $dados['longitude'],
                'esporte' => $dados['esporte']->value,
                'tipo_piso' => $dados['tipo_piso']->value,
                'valor_hora' => $dados['valor_hora'],
                'capacidade_maxima' => $dados['capacidade_maxima'],
                'cobertura' => $dados['cobertura'],
                'descricao' => $dados['descricao'],
                'status_cadastro' => StatusCadastro::Aprovado,
            ]);

            QuadraFoto::create([
                'quadra_id' => $quadra->id,
                'caminho' => "quadras/{$quadra->id}/capa.jpg",
                'capa' => true,
                'ordem' => 0,
            ]);
        });

        $this->command?->info('Quadras reais de Bauru, Agudos e Piratininga cadastradas.');
    }
}
