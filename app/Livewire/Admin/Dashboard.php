<?php

namespace App\Livewire\Admin;

use App\Enums\ReservaStatus;
use App\Enums\UserRole;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function indicadores(): array
    {
        return [
            'jogadores' => User::query()->where('role', UserRole::Jogador->value)->count(),
            'donosQuadra' => User::query()->where('role', UserRole::DonoQuadra->value)->count(),
            'admins' => User::query()->where('role', UserRole::Admin->value)->count(),
            'quadras' => Quadra::query()->count(),
            'reservasConfirmadasMes' => Reserva::query()
                ->where('status', ReservaStatus::Confirmada->value)
                ->whereYear('data', now()->year)
                ->whereMonth('data', now()->month)
                ->count(),
            'reservasPendentes' => Reserva::query()->where('status', ReservaStatus::Pendente->value)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
