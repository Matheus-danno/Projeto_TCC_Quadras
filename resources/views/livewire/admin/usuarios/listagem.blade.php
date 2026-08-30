<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl">{{ __('Usuários') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Gerencie os usuários da plataforma e o papel de cada um.') }}</flux:text>
    </div>

    <flux:select wire:model.live="filtroRole" :label="__('Papel')" class="max-w-xs rounded-full">
        <flux:select.option value="">{{ __('Todos os papéis') }}</flux:select.option>
        @foreach ($rolesDisponiveis as $role)
            <flux:select.option value="{{ $role->value }}">{{ $role->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    @if ($this->usuarios->isEmpty())
        <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
            <flux:icon.users class="size-8 text-zinc-300" />
            <flux:heading size="lg">{{ __('Nenhum usuário encontrado') }}</flux:heading>
            <flux:text>{{ __('Ajuste o filtro para ver outros usuários.') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="p-0">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nome') }}</flux:table.column>
                    <flux:table.column>{{ __('E-mail') }}</flux:table.column>
                    <flux:table.column>{{ __('Nível') }}</flux:table.column>
                    <flux:table.column>{{ __('Nota') }}</flux:table.column>
                    <flux:table.column>{{ __('Créditos') }}</flux:table.column>
                    <flux:table.column>{{ __('Papel') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->usuarios as $usuario)
                        <flux:table.row wire:key="usuario-{{ $usuario->id }}">
                            <flux:table.cell class="font-semibold text-zinc-900">{{ $usuario->name }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $usuario->email }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $usuario->nivel?->label() ?? '—' }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $usuario->notaMedia() ?? '—' }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">R$ {{ number_format($usuario->saldo_creditos, 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($usuario->id === auth()->id())
                                    <flux:badge color="orange" size="sm">{{ $usuario->role->label() }} ({{ __('você') }})</flux:badge>
                                @else
                                    <flux:select
                                        size="sm"
                                        class="max-w-48 rounded-full"
                                        wire:change="prepararTrocaRole({{ $usuario->id }}, $event.target.value)"
                                    >
                                        @foreach ($rolesDisponiveis as $role)
                                            <flux:select.option value="{{ $role->value }}" :selected="$role === $usuario->role">
                                                {{ $role->label() }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif

    <flux:modal name="confirmar-role" class="w-full md:w-96">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Confirmar alteração de papel') }}</flux:heading>

            @if ($this->usuarioSelecionado)
                <flux:text>
                    {{ __('Tem certeza que deseja alterar o papel de') }}
                    <strong>{{ $this->usuarioSelecionado->name }}</strong>
                    {{ __('de') }}
                    <strong>{{ $this->usuarioSelecionado->role->label() }}</strong>
                    {{ __('para') }}
                    <strong>{{ $novoRole ? \App\Enums\UserRole::from($novoRole)->label() : '' }}</strong>?
                </flux:text>
            @endif

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="outline" class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50">
                        {{ __('Cancelar') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button variant="primary" color="orange" class="rounded-full" wire:click="confirmarTrocaRole">
                    {{ __('Confirmar') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
