<div class="flex w-full flex-col gap-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <flux:avatar size="lg" :name="$usuario->name" :initials="$usuario->initials()" color="zinc" />
            <div>
                <flux:heading size="xl">{{ $usuario->name }}</flux:heading>
                <flux:text class="text-zinc-500">{{ $usuario->email }} · {{ $usuario->role->label() }}</flux:text>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <flux:badge size="sm" color="{{ $usuario->estaBloqueado() ? 'red' : 'green' }}">
                {{ $usuario->estaBloqueado() ? __('Bloqueado') : __('Ativo') }}
            </flux:badge>

            @if ($usuario->estaBloqueado())
                <flux:button size="sm" variant="primary" color="orange" class="rounded-full" wire:click="desbloquear">
                    {{ __('Desbloquear') }}
                </flux:button>
            @else
                <flux:button size="sm" variant="outline" class="rounded-full !border-red-300 !text-red-600 hover:!bg-red-50" wire:click="bloquear">
                    {{ __('Bloquear') }}
                </flux:button>
            @endif

            <flux:button size="sm" variant="ghost" class="rounded-full" :href="route('admin.usuarios')" wire:navigate>
                {{ __('Voltar') }}
            </flux:button>
        </div>
    </div>

    <flux:card class="flex flex-col gap-4 rounded-2xl p-0">
        <flux:heading size="lg" class="p-6 pb-0">{{ __('Reservas recebidas nas quadras') }}</flux:heading>

        @if ($this->reservas->isEmpty())
            <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                <flux:icon.calendar-days class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhuma reserva ainda') }}</flux:heading>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="ps-6!">{{ __('Data') }}</flux:table.column>
                    <flux:table.column>{{ __('Quadra') }}</flux:table.column>
                    <flux:table.column>{{ __('Valor') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->reservas as $reserva)
                        <flux:table.row wire:key="historico-reserva-{{ $reserva->id }}">
                            <flux:table.cell class="ps-6! text-zinc-500">{{ $reserva->data->format('d/m/Y') }} {{ substr($reserva->hora_inicio, 0, 5) }}</flux:table.cell>
                            <flux:table.cell class="font-medium text-zinc-900">{{ $reserva->quadra->nome }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">R$ {{ number_format($reserva->valorEfetivoReais(), 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ match ($reserva->status) {
                                    App\Enums\ReservaStatus::Confirmada => 'green',
                                    App\Enums\ReservaStatus::Cancelada => 'red',
                                    default => 'orange',
                                } }}">{{ $reserva->status->label() }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
