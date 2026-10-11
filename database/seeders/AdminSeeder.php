<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ComissaoConfiguracao;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Garante que existe um usuário administrador e a configuração inicial de
 * comissão. Idempotente: pode ser chamado de qualquer outro seeder sem
 * duplicar dados (php artisan db:seed --class=AdminSeeder).
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'admin@demo.com')->doesntExist()) {
            User::factory()->create([
                'name' => 'Administrador AlugaQuadra',
                'email' => 'admin@demo.com',
                'role' => UserRole::Admin,
            ]);
        }

        if (ComissaoConfiguracao::query()->doesntExist()) {
            ComissaoConfiguracao::definirPercentual((float) config('comissao.percentual_padrao'), null);
        }

        $this->command?->table(['Papel', 'E-mail', 'Senha'], [
            ['Administrador', 'admin@demo.com', 'password'],
        ]);
    }
}
