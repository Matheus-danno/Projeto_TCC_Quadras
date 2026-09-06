<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Financeiro') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Acompanhe o faturamento das suas quadras e da sua loja.') }}</flux:text>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Faturamento Reservas') }}</flux:text>
            <flux:heading size="xl">R$ {{ number_format($this->resumoGeral['faturamentoReservas'], 2, ',', '.') }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Faturamento Loja') }}</flux:text>
            <flux:heading size="xl">R$ {{ number_format($this->resumoGeral['faturamentoLoja'], 2, ',', '.') }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Comissão Retida (5%)') }}</flux:text>
            <flux:heading size="xl" class="text-red-500">- R$ {{ number_format($this->resumoGeral['comissaoTotal'], 2, ',', '.') }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1 rounded-2xl p-5">
            <flux:text class="text-zinc-400">{{ __('Valor Líquido (mês)') }}</flux:text>
            <flux:heading size="xl" class="text-green-600">R$ {{ number_format($this->resumoGeral['liquidoTotal'], 2, ',', '.') }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <flux:card class="flex flex-col gap-3 rounded-2xl">
            <flux:heading size="lg">{{ __('Faturamento por quadra (mês)') }}</flux:heading>

            @if ($this->faturamentoPorQuadra->isEmpty())
                <flux:text class="text-zinc-500">{{ __('Nenhum faturamento confirmado este mês.') }}</flux:text>
            @else
                <div class="flex flex-col gap-3">
                    @foreach ($this->faturamentoPorQuadra as $linha)
                        <div wire:key="quadra-{{ $linha['quadra']->id }}">
                            <div class="flex items-center justify-between gap-2 text-sm">
                                <span class="font-medium text-zinc-900">{{ $linha['quadra']->nome }}</span>
                                <span class="text-zinc-500">R$ {{ number_format($linha['faturamento'], 2, ',', '.') }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100">
                                <div class="h-full rounded-full bg-orange-500" style="width: {{ $linha['percentual'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </flux:card>

        <flux:card class="flex flex-col gap-3 rounded-2xl">
            <flux:heading size="lg">{{ __('Faturamento por produto (mês)') }}</flux:heading>

            @if ($this->faturamentoPorProduto->isEmpty())
                <flux:text class="text-zinc-500">{{ __('Nenhuma venda de produto este mês.') }}</flux:text>
            @else
                <div class="flex flex-col gap-3">
                    @foreach ($this->faturamentoPorProduto as $linha)
                        <div wire:key="produto-{{ $linha['produto']->id }}">
                            <div class="flex items-center justify-between gap-2 text-sm">
                                <span class="font-medium text-zinc-900">{{ $linha['produto']->nome }}</span>
                                <span class="text-zinc-500">R$ {{ number_format($linha['faturamento'], 2, ',', '.') }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100">
                                <div class="h-full rounded-full bg-orange-500" style="width: {{ $linha['percentual'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </flux:card>
    </div>

    <flux:card class="flex flex-col gap-4 rounded-2xl">
        <div>
            <flux:heading size="lg">{{ __('Conta para recebimento') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('Chave Pix vinculada para os repasses.') }}</flux:text>
        </div>

        @if ($editandoChavePix)
            <form wire:submit="salvarChavePix" class="flex flex-col gap-3">
                <flux:input wire:model="chavePixRecebimento" :label="__('Chave Pix')" class="rounded-full" placeholder="email@exemplo.com" autofocus />

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary" color="orange" size="sm" class="rounded-full">{{ __('Salvar') }}</flux:button>
                    <flux:button type="button" wire:click="cancelarEdicaoChavePix" variant="ghost" size="sm" class="rounded-full">{{ __('Cancelar') }}</flux:button>
                </div>
            </form>
        @else
            <div class="flex items-center justify-between gap-2 rounded-full bg-zinc-50 px-4 py-2">
                <flux:text>{{ $this->chavePixMascarada() ?? __('Nenhuma chave Pix cadastrada') }}</flux:text>
                <button type="button" wire:click="editarChavePix" class="text-sm font-semibold text-orange-500 hover:text-orange-600">
                    {{ __('Alterar') }}
                </button>
            </div>
        @endif

        <div>
            <flux:text class="text-zinc-500">{{ __('Próximo a liberar (reservas)') }}</flux:text>
            <div class="mt-1 flex items-center justify-between">
                @if ($this->proximaLiberacao)
                    <flux:heading size="lg">
                        {{ $this->proximaLiberacao['dias'] === 0 ? __('Hoje') : __(':dias dias', ['dias' => $this->proximaLiberacao['dias']]) }}
                    </flux:heading>
                    <flux:heading size="lg">R$ {{ number_format($this->proximaLiberacao['valor'], 2, ',', '.') }}</flux:heading>
                @else
                    <flux:text class="text-zinc-500">{{ __('Nenhum repasse previsto.') }}</flux:text>
                @endif
            </div>
        </div>
    </flux:card>

    <flux:card class="flex flex-col gap-4 rounded-2xl">
        <div class="flex items-center justify-between gap-2">
            <flux:heading size="lg">{{ __('Faturamento nos últimos 6 meses') }}</flux:heading>
            <flux:text class="font-semibold text-zinc-900">R$ {{ number_format($this->faturamentoUltimosMeses[5]['faturamento'], 2, ',', '.') }}</flux:text>
        </div>

        <div class="flex h-32 items-end gap-3">
            @foreach ($this->faturamentoUltimosMeses as $linha)
                <div
                    class="flex h-full flex-1 flex-col items-center justify-end gap-1.5"
                    title="{{ ucfirst($linha['mes']->translatedFormat('F/Y')) }}: R$ {{ number_format($linha['faturamento'], 2, ',', '.') }}"
                >
                    <div
                        class="w-full rounded-t-md {{ $linha['atual'] ? 'bg-orange-500' : 'bg-orange-200' }}"
                        style="height: {{ $linha['percentual'] }}%; min-height: 2px"
                    ></div>
                    <span class="text-xs capitalize {{ $linha['atual'] ? 'font-semibold text-zinc-900' : 'text-zinc-500' }}">{{ $linha['label'] }}</span>
                </div>
            @endforeach
        </div>
    </flux:card>

    <flux:card class="flex flex-col gap-4 rounded-2xl p-0">
        <div class="flex flex-col gap-3 p-6 pb-0 md:flex-row md:items-center md:justify-between">
            <flux:heading size="lg">{{ __('Transações recentes') }}</flux:heading>

            <div class="flex flex-col gap-2 sm:flex-row">
                <flux:input wire:model.live.debounce.400ms="busca" placeholder="{{ __('Buscar cliente, quadra ou retirada...') }}" class="rounded-full sm:w-56" />

                <flux:select wire:model.live="filtroTipo" class="rounded-full sm:w-36">
                    <flux:select.option value="todos">{{ __('Tudo') }}</flux:select.option>
                    <flux:select.option value="reservas">{{ __('Só reservas') }}</flux:select.option>
                    <flux:select.option value="produtos">{{ __('Só produtos') }}</flux:select.option>
                </flux:select>

                @if ($filtroTipo !== 'produtos')
                    <flux:select wire:model.live="filtroQuadraId" class="rounded-full sm:w-40">
                        <flux:select.option value="">{{ __('Todas as quadras') }}</flux:select.option>
                        @foreach ($this->quadras as $quadra)
                            <flux:select.option value="{{ $quadra->id }}">{{ $quadra->nome }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="filtroStatus" class="rounded-full sm:w-36">
                        <flux:select.option value="">{{ __('Todos os status') }}</flux:select.option>
                        <flux:select.option value="pago">{{ __('Pago') }}</flux:select.option>
                        <flux:select.option value="pendente">{{ __('Pendente') }}</flux:select.option>
                        <flux:select.option value="isento">{{ __('Isento') }}</flux:select.option>
                        <flux:select.option value="reembolsado">{{ __('Reembolsado') }}</flux:select.option>
                    </flux:select>
                @endif

                <flux:button wire:click="exportarCsv" variant="outline" class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50 dark:!border-orange-400/30 dark:!text-orange-400 dark:hover:!bg-orange-400/10">
                    {{ __('Exportar CSV') }}
                </flux:button>
            </div>
        </div>

        @if ($this->transacoes->isEmpty())
            <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                <flux:icon.banknotes class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhuma transação encontrada') }}</flux:heading>
                <flux:text>{{ __('Ajuste a busca ou os filtros selecionados.') }}</flux:text>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="ps-6!">{{ __('Tipo') }}</flux:table.column>
                    <flux:table.column>{{ __('Data') }}</flux:table.column>
                    <flux:table.column>{{ __('Descrição') }}</flux:table.column>
                    <flux:table.column>{{ __('Cliente') }}</flux:table.column>
                    <flux:table.column>{{ __('Valor') }}</flux:table.column>
                    <flux:table.column>{{ __('Comissão') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->transacoes as $transacao)
                        <flux:table.row wire:key="transacao-{{ $transacao['chave'] }}">
                            <flux:table.cell class="ps-6!">
                                <flux:badge color="{{ $transacao['tipo'] === 'reserva' ? 'zinc' : 'orange' }}" size="sm">
                                    {{ $transacao['tipo'] === 'reserva' ? __('Quadra') : __('Produto') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $transacao['data']->format('d/m') }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $transacao['descricao'] }}</flux:table.cell>
                            <flux:table.cell class="font-semibold text-zinc-900">{{ $transacao['cliente'] }}</flux:table.cell>
                            <flux:table.cell class="font-medium text-zinc-900">R$ {{ number_format($transacao['valor'], 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell class="text-red-500">- R$ {{ number_format($transacao['comissao'], 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ $transacao['statusColor'] }}" size="sm">{{ $transacao['statusLabel'] }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="p-4">
                <flux:pagination :paginator="$this->transacoes" />
            </div>
        @endif
    </flux:card>
</div>
