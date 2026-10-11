<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Usuários') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Clientes e proprietários cadastrados na plataforma.') }}</flux:text>
    </div>

    <flux:card class="flex flex-col gap-4 rounded-2xl p-0">
        <div class="flex flex-col gap-3 p-6 pb-0 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading size="lg">{{ __('Todos os usuários') }}</flux:heading>

            <div class="flex flex-wrap gap-2">
                <flux:input wire:model.live.debounce.400ms="busca" placeholder="{{ __('Buscar nome ou e-mail...') }}" class="rounded-full sm:w-56" />

                <flux:select wire:model.live="filtroPapel" class="rounded-full sm:w-44">
                    <flux:select.option value="">{{ __('Todos os papéis') }}</flux:select.option>
                    @foreach ($papeisDisponiveis as $papel)
                        <flux:select.option value="{{ $papel->value }}">{{ $papel->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div wire:loading.class="opacity-60" wire:target="busca,filtroPapel,bloquear,desbloquear">
            @if ($this->usuarios->isEmpty())
                <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                    <flux:icon.users class="size-8 text-zinc-300" />
                    <flux:heading size="lg">{{ __('Nenhum usuário encontrado') }}</flux:heading>
                    <flux:text>{{ __('Ajuste a busca ou o filtro selecionado.') }}</flux:text>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="ps-6!">{{ __('Nome') }}</flux:table.column>
                        <flux:table.column>{{ __('E-mail') }}</flux:table.column>
                        <flux:table.column>{{ __('Papel') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->usuarios as $usuario)
                            <flux:table.row wire:key="admin-usuario-{{ $usuario->id }}">
                                <flux:table.cell class="ps-6! font-medium text-zinc-900">{{ $usuario->name }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $usuario->email }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $usuario->role->label() }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="{{ $usuario->estaBloqueado() ? 'red' : 'green' }}">
                                        {{ $usuario->estaBloqueado() ? __('Bloqueado') : __('Ativo') }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex gap-2">
                                        <flux:button size="sm" variant="outline" class="rounded-full" :href="route('admin.usuarios.show', $usuario)" wire:navigate>
                                            {{ __('Histórico') }}
                                        </flux:button>

                                        @if ($usuario->estaBloqueado())
                                            <flux:button size="sm" variant="primary" color="orange" class="rounded-full" wire:click="desbloquear({{ $usuario->id }})">
                                                {{ __('Desbloquear') }}
                                            </flux:button>
                                        @else
                                            <flux:button size="sm" variant="outline" class="rounded-full !border-red-300 !text-red-600 hover:!bg-red-50" wire:click="bloquear({{ $usuario->id }})">
                                                {{ __('Bloquear') }}
                                            </flux:button>
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                <div class="p-4">
                    <flux:pagination :paginator="$this->usuarios" />
                </div>
            @endif
        </div>
    </flux:card>
</div>
