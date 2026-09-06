<?php

namespace Database\Seeders;

use App\Models\Produto;
use App\Models\Quadra;
use App\Models\Sala;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $donos = User::factory()->donoQuadra()->count(3)->create();

        $jogadores = collect([$testUser])
            ->concat(User::factory()->count(5)->create());

        $quadrasPorDono = $this->distribuir(total: 8, partes: $donos->count());

        $quadras = $donos->flatMap(
            fn (User $dono, int $indice) => Quadra::factory()->count($quadrasPorDono[$indice])->create([
                'dono_id' => $dono->id,
            ])
        );

        $salas = Sala::factory()->count(5)->create([
            'criador_id' => fn () => $jogadores->random()->id,
            'quadra_id' => fn () => $quadras->random()->id,
        ]);

        $salas->each(function (Sala $sala) use ($jogadores) {
            $sala->participantes()->attach(
                $jogadores->random(min(3, $jogadores->count()))->pluck('id')
            );
        });

        $donos->each(fn (User $dono) => Produto::factory()->count(2)->create(['dono_id' => $dono->id]));
    }

    /**
     * Distribui um total em partes o mais iguais possível.
     *
     * @return list<int>
     */
    private function distribuir(int $total, int $partes): array
    {
        $base = intdiv($total, $partes);
        $resto = $total % $partes;

        return array_map(
            fn (int $indice) => $base + ($indice < $resto ? 1 : 0),
            range(0, $partes - 1)
        );
    }
}
