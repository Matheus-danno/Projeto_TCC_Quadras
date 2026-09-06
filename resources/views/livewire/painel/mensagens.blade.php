<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl" class="text-3xl! sm:text-4xl!">{{ __('Mensagens') }}</flux:heading>
        <flux:text class="mt-1 text-base text-zinc-400">{{ __('Converse com jogadores que alugaram suas quadras.') }}</flux:text>
    </div>

    @if ($conversaSelecionada === null)
        <flux:input wire:model.live.debounce.300ms="busca" :placeholder="__('Buscar por jogador ou quadra...')" class="rounded-full" icon="magnifying-glass" />

        @if ($this->conversas->isEmpty())
            <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
                <flux:icon.chat-bubble-left-right class="size-8 text-zinc-300" />
                <flux:heading size="lg">{{ __('Nenhuma mensagem ainda') }}</flux:heading>
                <flux:text>
                    {{ $busca
                        ? __('Nenhuma conversa encontrada para ":busca".', ['busca' => $busca])
                        : __('Quando um jogador enviar uma mensagem sobre alguma das suas quadras, ela aparece aqui.') }}
                </flux:text>
            </flux:card>
        @else
            <flux:card class="divide-y divide-zinc-100 rounded-2xl p-0">
                @foreach ($this->conversas as $conversa)
                    @php $naoLidas = $conversa->mensagensNaoLidasPara(auth()->id()); @endphp
                    <button
                        type="button"
                        wire:click="selecionarConversa({{ $conversa->id }})"
                        wire:key="conversa-{{ $conversa->id }}"
                        class="flex w-full items-center justify-between gap-3 p-4 text-left hover:bg-zinc-50"
                    >
                        <div>
                            <p class="font-semibold text-zinc-900">{{ $conversa->jogador->name }}</p>
                            <p class="text-sm text-zinc-500">{{ $conversa->quadra->nome }}</p>
                            @if ($ultima = $conversa->ultimaMensagem())
                                <p class="mt-1 text-sm {{ $naoLidas > 0 ? 'font-medium text-zinc-700' : 'text-zinc-400' }}">{{ Str::limit($ultima->texto, 60) }} · {{ $ultima->tempoDecorrido() }} atrás</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($naoLidas > 0)
                                <flux:badge color="orange" size="sm">{{ $naoLidas }}</flux:badge>
                            @endif
                            <flux:icon.chevron-right class="size-4 text-zinc-300" />
                        </div>
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
                <flux:heading size="lg">{{ $conversa->jogador->name }}</flux:heading>
                <flux:text class="mb-4 text-zinc-400">{{ $conversa->quadra->nome }}</flux:text>

                <div class="mb-4 flex max-h-80 flex-col gap-3 overflow-y-auto" wire:poll.5s x-init="$el.scrollTop = $el.scrollHeight">
                    @forelse ($conversa->mensagens as $mensagem)
                        <div class="flex flex-col {{ $mensagem->user_id === auth()->id() ? 'items-end' : 'items-start' }}" wire:key="mensagem-{{ $mensagem->id }}">
                            <div class="max-w-[75%] rounded-2xl px-4 py-2 {{ $mensagem->user_id === auth()->id() ? 'bg-orange-500 text-white' : 'bg-zinc-100 text-zinc-900' }}">
                                <p class="text-sm">{{ $mensagem->texto }}</p>
                            </div>
                            <span class="mt-1 text-xs text-zinc-400">{{ $mensagem->tempoDecorrido() }} atrás</span>
                        </div>
                    @empty
                        <flux:text class="text-center">{{ __('Nenhuma mensagem ainda.') }}</flux:text>
                    @endforelse
                </div>

                <form wire:submit.prevent="enviarMensagem" class="flex gap-2">
                    <flux:input wire:model="novaMensagem" class="flex-1 rounded-full" :placeholder="__('Escreva uma mensagem...')" maxlength="500" />
                    <flux:button type="submit" variant="primary" color="orange" class="rounded-full">{{ __('Enviar') }}</flux:button>
                </form>
                @error('novaMensagem') <flux:text class="mt-1 text-red-600">{{ $message }}</flux:text> @enderror
            </flux:card>
        </div>
    @endif
</div>
