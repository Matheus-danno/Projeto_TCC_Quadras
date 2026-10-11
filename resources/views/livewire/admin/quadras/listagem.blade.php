<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Quadras') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Aprove quadras novas, suspenda ou reative quadras já cadastradas.') }}</flux:text>
    </div>

    <flux:card class="flex flex-col gap-4 rounded-2xl p-0">
        <div class="flex flex-col gap-3 p-6 pb-0 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading size="lg">{{ __('Todas as quadras') }}</flux:heading>

            <div class="flex flex-wrap gap-2">
                <flux:input wire:model.live.debounce.400ms="busca" placeholder="{{ __('Buscar quadra ou proprietário...') }}" class="rounded-full sm:w-56" />

                <flux:select wire:model.live="filtroStatus" class="rounded-full sm:w-44">
                    <flux:select.option value="">{{ __('Todos os status') }}</flux:select.option>
                    @foreach ($statusDisponiveis as $status)
                        <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div wire:loading.class="opacity-60" wire:target="busca,filtroStatus,aprovar,suspender">
            @if ($this->quadras->isEmpty())
                <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                    <flux:icon.building-office-2 class="size-8 text-zinc-300" />
                    <flux:heading size="lg">{{ __('Nenhuma quadra encontrada') }}</flux:heading>
                    <flux:text>{{ __('Ajuste a busca ou o filtro selecionado.') }}</flux:text>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="ps-6!">{{ __('Quadra') }}</flux:table.column>
                        <flux:table.column>{{ __('Proprietário') }}</flux:table.column>
                        <flux:table.column>{{ __('Receita gerada') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->quadras as $quadra)
                            <flux:table.row wire:key="admin-quadra-{{ $quadra->id }}">
                                <flux:table.cell class="ps-6! font-medium text-zinc-900">{{ $quadra->nome }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $quadra->dono?->name ?? '—' }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">R$ {{ number_format($this->receitaGerada($quadra), 2, ',', '.') }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="{{ $quadra->status_aprovacao->cor() }}">{{ $quadra->status_aprovacao->label() }}</flux:badge>
                                    @if (! $quadra->ativa)
                                        <flux:badge size="sm" color="zinc">{{ __('Inativa (dono)') }}</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex gap-2">
                                        @if ($quadra->status_aprovacao === App\Enums\StatusAprovacaoQuadra::Pendente)
                                            <flux:button size="sm" variant="primary" color="orange" class="rounded-full" wire:click="aprovar({{ $quadra->id }})">
                                                {{ __('Aprovar') }}
                                            </flux:button>
                                        @elseif ($quadra->status_aprovacao === App\Enums\StatusAprovacaoQuadra::Suspensa)
                                            <flux:button size="sm" variant="primary" color="orange" class="rounded-full" wire:click="aprovar({{ $quadra->id }})">
                                                {{ __('Reativar') }}
                                            </flux:button>
                                        @else
                                            <flux:button size="sm" variant="outline" class="rounded-full !border-red-300 !text-red-600 hover:!bg-red-50" wire:click="suspender({{ $quadra->id }})">
                                                {{ __('Suspender') }}
                                            </flux:button>
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                <div class="p-4">
                    <flux:pagination :paginator="$this->quadras" />
                </div>
            @endif
        </div>
    </flux:card>
</div>
