<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl">{{ __('Quadras') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Todas as quadras cadastradas na plataforma.') }}</flux:text>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <flux:select wire:model.live="filtroCidade" :label="__('Cidade')" class="rounded-full">
            <flux:select.option value="">{{ __('Todas as cidades') }}</flux:select.option>
            @foreach ($this->cidades as $opcao)
                <flux:select.option value="{{ $opcao }}">{{ $opcao }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filtroEsporte" :label="__('Esporte')" class="rounded-full">
            <flux:select.option value="">{{ __('Todos os esportes') }}</flux:select.option>
            @foreach ($esportes as $opcao)
                <flux:select.option value="{{ $opcao->value }}">{{ $opcao->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filtroDonoId" :label="__('Dono')" class="rounded-full">
            <flux:select.option value="">{{ __('Todos os donos') }}</flux:select.option>
            @foreach ($this->donos as $dono)
                <flux:select.option value="{{ $dono->id }}">{{ $dono->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($this->quadras->isEmpty())
        <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
            <flux:icon.map-pin class="size-8 text-zinc-300" />
            <flux:heading size="lg">{{ __('Nenhuma quadra encontrada') }}</flux:heading>
            <flux:text>{{ __('Ajuste os filtros para ver outras quadras.') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="p-0">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Quadra') }}</flux:table.column>
                    <flux:table.column>{{ __('Dono') }}</flux:table.column>
                    <flux:table.column>{{ __('Local') }}</flux:table.column>
                    <flux:table.column>{{ __('Coordenadas') }}</flux:table.column>
                    <flux:table.column>{{ __('Esporte') }}</flux:table.column>
                    <flux:table.column>{{ __('Valor/Hora') }}</flux:table.column>
                    <flux:table.column>{{ __('Ações') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->quadras as $quadra)
                        <flux:table.row wire:key="quadra-{{ $quadra->id }}">
                            <flux:table.cell class="font-semibold text-zinc-900">{{ $quadra->nome }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $quadra->dono->name }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $quadra->bairro }}, {{ $quadra->cidade }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500 text-xs">
                                @if ($quadra->latitude !== null && $quadra->longitude !== null)
                                    {{ $quadra->latitude }}, {{ $quadra->longitude }}
                                @else
                                    —
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="orange" size="sm">{{ $quadra->esporte->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="font-medium text-zinc-900">R$ {{ number_format($quadra->valor_hora, 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-wrap items-center gap-2">
                                    <flux:button
                                        size="sm"
                                        variant="outline"
                                        class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50"
                                        wire:click="editar({{ $quadra->id }})"
                                    >
                                        {{ __('Editar') }}
                                    </flux:button>
                                    <flux:button
                                        size="sm"
                                        variant="outline"
                                        class="rounded-full !border-red-300 !text-red-600 hover:!bg-red-50"
                                        wire:click="pedirExclusao({{ $quadra->id }})"
                                    >
                                        {{ __('Excluir') }}
                                    </flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif

    <flux:modal name="form-quadra" class="w-full md:w-[32rem]">
        <form wire:submit="salvar" class="flex flex-col gap-6">
            <flux:heading size="lg">{{ __('Editar Quadra') }}</flux:heading>

            <flux:input wire:model="nome" :label="__('Nome da Quadra')" class="rounded-full" />

            <flux:input wire:model="endereco" :label="__('Endereço da Quadra')" class="rounded-full" />

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="cidade" :label="__('Cidade')" class="rounded-full" />
                <flux:input wire:model="bairro" :label="__('Bairro')" class="rounded-full" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="esporte" :label="__('Tipo de Esporte')" class="rounded-full">
                    <flux:select.option value="">{{ __('Selecione') }}</flux:select.option>
                    @foreach ($esportes as $opcao)
                        <flux:select.option value="{{ $opcao->value }}">{{ $opcao->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="valor_hora" :label="__('Valor por Hora (R$)')" class="rounded-full" type="number" step="0.01" min="0" />
            </div>

            <flux:checkbox wire:model="cobertura" :label="__('Quadra coberta')" />

            <flux:textarea wire:model="descricao" :label="__('Descrição / Comodidades')" class="rounded-2xl" rows="3" />

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="outline" class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50">
                        {{ __('Cancelar') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary" color="orange" class="rounded-full">{{ __('Salvar Alterações') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="excluir-quadra" class="w-full md:w-96">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Excluir quadra') }}</flux:heading>

            @if ($bloqueioExclusao)
                <flux:text>{{ $bloqueioExclusao }}</flux:text>

                <div class="flex justify-end">
                    <flux:modal.close>
                        <flux:button variant="primary" color="orange" class="rounded-full">{{ __('Entendi') }}</flux:button>
                    </flux:modal.close>
                </div>
            @else
                <flux:text>{{ __('Tem certeza que deseja excluir esta quadra? Essa ação não pode ser desfeita.') }}</flux:text>

                <div class="flex justify-end gap-3">
                    <flux:modal.close>
                        <flux:button variant="outline" class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50">
                            {{ __('Cancelar') }}
                        </flux:button>
                    </flux:modal.close>

                    <flux:button variant="danger" class="rounded-full" wire:click="excluir">{{ __('Excluir') }}</flux:button>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
