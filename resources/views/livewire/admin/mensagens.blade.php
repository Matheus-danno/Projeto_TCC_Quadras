<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl">{{ __('Mensagens') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Conversas entre jogadores organizadores e donos de quadra sobre reservas.') }}</flux:text>
    </div>

    @if ($conversaSelecionada === null)
        @if ($this->conversas->isEmpty())
            <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
                <flux:icon.chat-bubble-left-right class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhuma conversa ainda') }}</flux:heading>
                <flux:text>{{ __('Quando um jogador enviar uma mensagem para o dono de uma quadra, a conversa aparece aqui.') }}</flux:text>
            </flux:card>
        @else
            <flux:card class="divide-y divide-zinc-100 rounded-2xl p-0">
                @foreach ($this->conversas as $conversa)
                    <button
                        type="button"
                        wire:click="selecionarConversa({{ $conversa->id }})"
                        wire:key="conversa-{{ $conversa->id }}"
                        class="flex w-full items-center justify-between gap-3 p-4 text-left hover:bg-zinc-50"
                    >
                        <div>
                            <p class="font-semibold text-zinc-900">
                                {{ $conversa->jogador->name }} &rarr; {{ $conversa->quadra->dono?->name ?? '—' }}
                            </p>
                            <p class="text-sm text-zinc-500">{{ $conversa->quadra->nome }}</p>
                            @if ($ultima = $conversa->ultimaMensagem())
                                <p class="mt-1 text-sm text-zinc-400">{{ Str::limit($ultima->texto, 60) }} · {{ $ultima->tempoDecorrido() }} atrás</p>
                            @endif
                        </div>
                        <flux:icon.chevron-right class="size-4 text-zinc-300" />
                    </button>
                @endforeach
            </flux:card>
        @endif
    @else
        @php $conversa = $this->conversaAtual; @endphp
        <div>
            <button type="button" wire:click="voltar" class="mb-3 flex items-center gap-1 text-sm font-semibold text-zinc-500 hover:text-zinc-700">
                <flux:icon.chevron-left class="size-4" /> {{ __('Voltar') }}
            </button>

            <flux:card class="rounded-2xl">
                <flux:heading size="lg">
                    {{ $conversa->jogador->name }} &rarr; {{ $conversa->quadra->dono?->name ?? '—' }}
                </flux:heading>
                <flux:text class="mb-4 text-zinc-400">{{ $conversa->quadra->nome }}</flux:text>

                <div class="flex max-h-[28rem] flex-col gap-3 overflow-y-auto">
                    @forelse ($conversa->mensagens as $mensagem)
                        <div class="flex flex-col {{ $mensagem->user_id === $conversa->jogador_id ? 'items-start' : 'items-end' }}" wire:key="mensagem-{{ $mensagem->id }}">
                            <span class="text-xs font-semibold text-zinc-400">{{ $mensagem->user?->name ?? '—' }}</span>
                            <div class="max-w-[75%] rounded-2xl px-4 py-2 {{ $mensagem->user_id === $conversa->jogador_id ? 'bg-zinc-100 text-zinc-900' : 'bg-orange-500 text-white' }}">
                                <p class="text-sm">{{ $mensagem->texto }}</p>
                            </div>
                            <span class="mt-1 text-xs text-zinc-400">{{ $mensagem->tempoDecorrido() }} atrás</span>
                        </div>
                    @empty
                        <flux:text class="text-center">{{ __('Nenhuma mensagem ainda.') }}</flux:text>
                    @endforelse
                </div>
            </flux:card>
        </div>
    @endif
</div>
