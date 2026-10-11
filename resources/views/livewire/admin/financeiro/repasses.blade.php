<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Repasses aos proprietários') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Valor que cada proprietário já gerou (95% das reservas confirmadas e pagas) e quanto já foi repassado.') }}</flux:text>
    </div>

    <flux:card class="flex flex-col gap-4 rounded-2xl p-0">
        @if ($this->donos->isEmpty())
            <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                <flux:icon.users class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhum proprietário cadastrado') }}</flux:heading>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="ps-6!">{{ __('Proprietário') }}</flux:table.column>
                    <flux:table.column>{{ __('Gerado (total)') }}</flux:table.column>
                    <flux:table.column>{{ __('Já repassado') }}</flux:table.column>
                    <flux:table.column>{{ __('Pendente') }}</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->donos as $linha)
                        <flux:table.row wire:key="repasse-dono-{{ $linha['dono']->id }}">
                            <flux:table.cell class="ps-6! font-medium text-zinc-900">{{ $linha['dono']->name }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">R$ {{ number_format($linha['gerado'], 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell class="text-green-600">R$ {{ number_format($linha['repassado'], 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell class="font-semibold {{ $linha['pendente'] > 0 ? 'text-orange-600' : 'text-zinc-400' }}">
                                R$ {{ number_format($linha['pendente'], 2, ',', '.') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="primary" color="orange" class="rounded-full" wire:click="abrirMarcarPago({{ $linha['dono']->id }})">
                                    {{ __('Marcar repasse') }}
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    <flux:card class="flex flex-col gap-4 rounded-2xl p-0">
        <flux:heading size="lg" class="p-6 pb-0">{{ __('Últimos repasses registrados') }}</flux:heading>

        @if ($this->ultimosRepasses->isEmpty())
            <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                <flux:text class="text-zinc-500">{{ __('Nenhum repasse registrado ainda.') }}</flux:text>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="ps-6!">{{ __('Data') }}</flux:table.column>
                    <flux:table.column>{{ __('Proprietário') }}</flux:table.column>
                    <flux:table.column>{{ __('Valor') }}</flux:table.column>
                    <flux:table.column>{{ __('Registrado por') }}</flux:table.column>
                    <flux:table.column>{{ __('Observação') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->ultimosRepasses as $repasse)
                        <flux:table.row wire:key="historico-repasse-{{ $repasse->id }}">
                            <flux:table.cell class="ps-6! text-zinc-500">{{ $repasse->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                            <flux:table.cell class="font-medium text-zinc-900">{{ $repasse->dono->name }}</flux:table.cell>
                            <flux:table.cell class="text-green-600">R$ {{ number_format($repasse->valor_centavos / 100, 2, ',', '.') }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $repasse->criadoPor->name }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $repasse->observacao ?? '—' }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    <flux:modal name="marcar-repasse" class="w-full md:w-96">
        <form wire:submit="marcarPago" class="flex flex-col gap-4">
            <div>
                <flux:heading size="lg">{{ __('Marcar repasse como pago') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Registra um repasse feito ao proprietário fora da plataforma.') }}</flux:text>
            </div>

            <flux:input wire:model="valor" type="number" step="0.01" min="0.01" :label="__('Valor (R$)')" />
            <flux:textarea wire:model="observacao" :label="__('Observação (opcional)')" rows="2" />

            <div class="flex gap-2">
                <flux:modal.close>
                    <flux:button type="button" variant="outline" class="w-full rounded-full">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" color="orange" class="w-full rounded-full">{{ __('Confirmar') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
