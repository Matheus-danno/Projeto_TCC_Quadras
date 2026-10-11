<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Fila de aprovações') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Proprietários e quadras aguardando análise.') }}</flux:text>
    </div>

    <div class="flex gap-2">
        <flux:button size="sm" :variant="$aba === 'proprietarios' ? 'primary' : 'outline'" :color="$aba === 'proprietarios' ? 'orange' : null" class="rounded-full" wire:click="mudarAba('proprietarios')">
            {{ __('Proprietários') }}
            @if ($this->proprietariosPendentesCount > 0)
                <flux:badge size="sm" color="{{ $aba === 'proprietarios' ? 'zinc' : 'orange' }}" class="ms-1">{{ $this->proprietariosPendentesCount }}</flux:badge>
            @endif
        </flux:button>
        <flux:button size="sm" :variant="$aba === 'quadras' ? 'primary' : 'outline'" :color="$aba === 'quadras' ? 'orange' : null" class="rounded-full" wire:click="mudarAba('quadras')">
            {{ __('Quadras') }}
            @if ($this->quadrasPendentesCount > 0)
                <flux:badge size="sm" color="{{ $aba === 'quadras' ? 'zinc' : 'orange' }}" class="ms-1">{{ $this->quadrasPendentesCount }}</flux:badge>
            @endif
        </flux:button>
    </div>

    <flux:card class="flex flex-col gap-4 rounded-2xl p-0">
        <div class="flex flex-col gap-3 p-6 pb-0 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading size="lg">{{ $aba === 'proprietarios' ? __('Proprietários') : __('Quadras') }}</flux:heading>

            <div class="flex flex-wrap gap-2">
                <flux:input wire:model.live.debounce.400ms="busca" placeholder="{{ $aba === 'proprietarios' ? __('Nome, CPF/CNPJ ou cidade...') : __('Nome da quadra, dono ou cidade...') }}" class="rounded-full sm:w-64" />

                <flux:select wire:model.live="filtroStatus" class="rounded-full sm:w-48">
                    <flux:select.option value="">{{ __('Todos os status') }}</flux:select.option>
                    @foreach ($statusDisponiveis as $status)
                        <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div wire:loading.class="opacity-60" wire:target="busca,filtroStatus,mudarAba">
            @if ($aba === 'proprietarios')
                @if ($this->proprietarios->isEmpty())
                    <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                        <flux:icon.inbox class="size-8 text-zinc-300" />
                        <flux:heading size="lg">{{ __('Nada por aqui') }}</flux:heading>
                        <flux:text>{{ __('Ajuste a busca ou o filtro selecionado.') }}</flux:text>
                    </div>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="ps-6!">{{ __('Nome') }}</flux:table.column>
                            <flux:table.column>{{ __('CPF/CNPJ') }}</flux:table.column>
                            <flux:table.column>{{ __('Cidade') }}</flux:table.column>
                            <flux:table.column>{{ __('Enviado em') }}</flux:table.column>
                            <flux:table.column>{{ __('Aguardando') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                            <flux:table.column></flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->proprietarios as $proprietario)
                                <flux:table.row wire:key="fila-proprietario-{{ $proprietario->id }}">
                                    <flux:table.cell class="ps-6! font-medium text-zinc-900">{{ $proprietario->nome_estabelecimento ?? $proprietario->name }}</flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">{{ $proprietario->cnpj ?? $proprietario->cpf ?? '—' }}</flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">{{ $proprietario->cidade ?? '—' }}</flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">{{ $proprietario->created_at->format('d/m/Y') }}</flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">{{ $proprietario->updated_at->diffForHumans() }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm" color="{{ $proprietario->status_cadastro->cor() }}">{{ $proprietario->status_cadastro->label() }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:button size="sm" variant="outline" class="rounded-full" :href="route('admin.aprovacoes.proprietario', $proprietario)" wire:navigate>
                                            {{ __('Analisar') }}
                                        </flux:button>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>

                    <div class="p-4">
                        <flux:pagination :paginator="$this->proprietarios" />
                    </div>
                @endif
            @else
                @if ($this->quadras->isEmpty())
                    <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                        <flux:icon.inbox class="size-8 text-zinc-300" />
                        <flux:heading size="lg">{{ __('Nada por aqui') }}</flux:heading>
                        <flux:text>{{ __('Ajuste a busca ou o filtro selecionado.') }}</flux:text>
                    </div>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="ps-6!">{{ __('Quadra') }}</flux:table.column>
                            <flux:table.column>{{ __('Proprietário') }}</flux:table.column>
                            <flux:table.column>{{ __('Cidade') }}</flux:table.column>
                            <flux:table.column>{{ __('Enviado em') }}</flux:table.column>
                            <flux:table.column>{{ __('Aguardando') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                            <flux:table.column></flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->quadras as $quadra)
                                <flux:table.row wire:key="fila-quadra-{{ $quadra->id }}">
                                    <flux:table.cell class="ps-6! font-medium text-zinc-900">{{ $quadra->nome }}</flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">{{ $quadra->dono?->name ?? '—' }}</flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">{{ $quadra->cidade }}</flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">{{ $quadra->created_at->format('d/m/Y') }}</flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">{{ $quadra->updated_at->diffForHumans() }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm" color="{{ $quadra->status_cadastro->cor() }}">{{ $quadra->status_cadastro->label() }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:button size="sm" variant="outline" class="rounded-full" :href="route('admin.aprovacoes.quadra', $quadra)" wire:navigate>
                                            {{ __('Analisar') }}
                                        </flux:button>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>

                    <div class="p-4">
                        <flux:pagination :paginator="$this->quadras" />
                    </div>
                @endif
            @endif
        </div>
    </flux:card>
</div>
