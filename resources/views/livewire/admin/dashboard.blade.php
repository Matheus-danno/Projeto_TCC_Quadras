<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Visão geral da plataforma.') }}</flux:text>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-lg border border-zinc-200 border-s-4 border-s-orange-500 bg-white p-4">
            <flux:text>{{ __('Jogadores') }}</flux:text>
            <div class="mt-1 text-2xl font-bold text-zinc-900 tabular-nums">{{ $this->indicadores['jogadores'] }}</div>
        </div>

        <div class="rounded-lg border border-zinc-200 border-s-4 border-s-green-500 bg-white p-4">
            <flux:text>{{ __('Donos de Quadra') }}</flux:text>
            <div class="mt-1 text-2xl font-bold text-zinc-900 tabular-nums">{{ $this->indicadores['donosQuadra'] }}</div>
        </div>

        <div class="rounded-lg border border-zinc-200 border-s-4 border-s-orange-500 bg-white p-4">
            <flux:text>{{ __('Administradores') }}</flux:text>
            <div class="mt-1 text-2xl font-bold text-zinc-900 tabular-nums">{{ $this->indicadores['admins'] }}</div>
        </div>

        <div class="rounded-lg border border-zinc-200 border-s-4 border-s-green-500 bg-white p-4">
            <flux:text>{{ __('Quadras Cadastradas') }}</flux:text>
            <div class="mt-1 text-2xl font-bold text-zinc-900 tabular-nums">{{ $this->indicadores['quadras'] }}</div>
        </div>

        <div class="rounded-lg border border-zinc-200 border-s-4 border-s-orange-500 bg-white p-4">
            <flux:text>{{ __('Reservas Confirmadas (Mês)') }}</flux:text>
            <div class="mt-1 text-2xl font-bold text-zinc-900 tabular-nums">{{ $this->indicadores['reservasConfirmadasMes'] }}</div>
        </div>

        <div class="rounded-lg border border-zinc-200 border-s-4 border-s-amber-500 bg-white p-4">
            <flux:text>{{ __('Reservas Pendentes') }}</flux:text>
            <div class="mt-1 text-2xl font-bold text-zinc-900 tabular-nums">{{ $this->indicadores['reservasPendentes'] }}</div>
        </div>
    </div>
</div>
