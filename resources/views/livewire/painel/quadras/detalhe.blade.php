<div class="flex w-full flex-col gap-6">
    <div class="flex flex-col gap-3">
        <flux:link :href="route('painel.quadras')" class="inline-flex w-fit items-center gap-1 text-sm font-semibold text-orange-600! no-underline hover:underline" wire:navigate>
            {{ __('← Voltar para minhas quadras') }}
        </flux:link>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ $quadra->nome }}</flux:heading>
                <flux:badge color="{{ $quadra->ativa ? 'green' : 'red' }}" size="sm">
                    {{ $quadra->ativa ? __('Ativa') : __('Inativa') }}
                </flux:badge>
            </div>

            <div class="flex items-center gap-2">
                @if ($quadra->ativa)
                    <flux:button
                        variant="outline"
                        class="rounded-xl !border-red-300 !text-red-600 hover:!bg-red-50"
                        wire:click="cancelar"
                    >
                        {{ __('Desativar') }}
                    </flux:button>
                @else
                    <flux:button
                        variant="outline"
                        class="rounded-xl !border-green-300 !text-green-600 hover:!bg-green-50"
                        wire:click="ativar"
                    >
                        {{ __('Ativar') }}
                    </flux:button>
                @endif

                <flux:button :href="route('painel.quadras.editar', $quadra)" variant="primary" color="orange" class="rounded-xl" wire:navigate>
                    {{ __('Editar Quadra') }}
                </flux:button>
            </div>
        </div>
        <flux:text class="text-base text-zinc-400">
            {{ $quadra->esporte->label() }} - {{ $quadra->cobertura ? __('Coberta') : __('Descoberta') }}
        </flux:text>
    </div>

    @if ($quadra->fotos->isNotEmpty())
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($quadra->fotos->take(5) as $indice => $foto)
                <div class="relative aspect-square overflow-hidden rounded-2xl bg-zinc-100">
                    <img src="{{ $foto->url() }}" alt="{{ $quadra->nome }}" class="size-full object-cover">

                    @if ($indice === 4 && $quadra->fotos->count() > 5)
                        <div class="absolute inset-0 flex items-center justify-center bg-black/60 text-lg font-semibold text-white">
                            +{{ $quadra->fotos->count() - 5 }} {{ __('fotos') }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <flux:card class="rounded-2xl">
            <flux:heading size="lg">{{ __('Sobre a quadra') }}</flux:heading>
            <flux:text class="mt-1">{{ $quadra->endereco }} - {{ $quadra->bairro }}, {{ $quadra->cidade }}</flux:text>
            @if ($quadra->capacidade_maxima)
                <flux:text>{{ __('Capacidade para até :n jogadores', ['n' => $quadra->capacidade_maxima]) }}</flux:text>
            @endif
            <flux:text>R$ {{ number_format($quadra->valor_hora, 2, ',', '.') }} {{ __('por hora') }}</flux:text>

            @if ($quadra->descricao)
                <flux:text class="mt-3 text-zinc-500">{{ $quadra->descricao }}</flux:text>
            @endif

            <flux:text class="mt-4 text-xs text-zinc-400">{{ __('Comodidades') }}</flux:text>
            <div class="mt-1 flex flex-wrap gap-2">
                @foreach ($quadra->listaAmenidades() as $amenidade)
                    <flux:badge color="zinc" size="sm">{{ $amenidade }}</flux:badge>
                @endforeach
            </div>
        </flux:card>

        <flux:card class="flex flex-col divide-y divide-zinc-100 rounded-2xl p-0">
            <div class="flex items-center justify-between px-5 py-3">
                <flux:text class="text-zinc-500">{{ __('Total de reservas') }}</flux:text>
                <flux:heading size="md">{{ $this->indicadores['totalReservas'] }}</flux:heading>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
                <flux:text class="text-zinc-500">{{ __('Faturamento Gerado') }}</flux:text>
                <flux:heading size="md">R$ {{ number_format($this->indicadores['faturamentoGerado'], 2, ',', '.') }}</flux:heading>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
                <flux:text class="text-zinc-500">{{ __('Avaliação Média') }}</flux:text>
                <flux:heading size="md" class="flex items-center gap-1">
                    {{ number_format($this->indicadores['avaliacaoMedia'], 1, ',', '.') }}
                    <flux:icon.star variant="mini" class="text-amber-400" />
                </flux:heading>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
                <flux:text class="text-zinc-500">{{ __('Taxa de ocupação') }}</flux:text>
                <flux:heading size="md">{{ $this->indicadores['taxaOcupacao'] }}%</flux:heading>
            </div>
        </flux:card>
    </div>

    @if ($this->avaliacoesRecentes->isNotEmpty())
        <div class="flex flex-col gap-3">
            <flux:heading size="lg">{{ __('Avaliações Recentes') }}</flux:heading>

            <flux:card class="flex flex-col divide-y divide-zinc-100 rounded-2xl p-0">
                @foreach ($this->avaliacoesRecentes as $avaliacao)
                    <div class="flex items-start gap-3 px-5 py-4" wire:key="avaliacao-{{ $avaliacao->id }}">
                        <flux:avatar size="sm" :name="$avaliacao->autor->name" :initials="$avaliacao->autor->initials()" color="orange" />

                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <flux:heading size="sm">{{ $avaliacao->autor->name }}</flux:heading>
                                <div class="flex text-amber-400">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <flux:icon.star variant="mini" :class="$i <= $avaliacao->nota ? 'text-amber-400' : 'text-zinc-200'" />
                                    @endfor
                                </div>
                            </div>
                            @if ($avaliacao->comentario)
                                <flux:text class="mt-1 text-zinc-500">{{ $avaliacao->comentario }}</flux:text>
                            @endif
                        </div>
                    </div>
                @endforeach
            </flux:card>
        </div>
    @endif

    <div class="flex flex-col gap-3">
        <flux:heading size="lg">{{ __('Próximas reservas') }}</flux:heading>

        @if ($this->proximasReservas->isEmpty())
            <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
                <flux:icon.calendar-days class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhuma reserva encontrada') }}</flux:heading>
                <flux:text>{{ __('Esta quadra ainda não tem próximas reservas.') }}</flux:text>
            </flux:card>
        @else
            <flux:card class="rounded-2xl p-0">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="ps-6!">{{ __('Cliente') }}</flux:table.column>
                        <flux:table.column>{{ __('Data/Horário') }}</flux:table.column>
                        <flux:table.column>{{ __('Valor') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->proximasReservas as $reserva)
                            <flux:table.row wire:key="reserva-{{ $reserva->id }}">
                                <flux:table.cell class="ps-6! font-semibold text-zinc-900">{{ $reserva->nome_cliente }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">
                                    {{ $reserva->data->format('d/m/Y') }}, {{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fim, 0, 5) }}
                                    <span class="text-zinc-400">({{ $reserva->duracaoFormatada() }})</span>
                                </flux:table.cell>
                                <flux:table.cell class="font-medium text-zinc-900">
                                    @php
                                        $horas = (strtotime($reserva->hora_fim) - strtotime($reserva->hora_inicio)) / 3600;
                                    @endphp
                                    R$ {{ number_format($quadra->valor_hora * $horas, 2, ',', '.') }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="{{ match ($reserva->status) {
                                        App\Enums\ReservaStatus::Confirmada => 'green',
                                        App\Enums\ReservaStatus::Pendente => 'amber',
                                        App\Enums\ReservaStatus::Cancelada => 'red',
                                    } }}" size="sm">
                                        {{ $reserva->status->label() }}
                                    </flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        @endif
    </div>
</div>
