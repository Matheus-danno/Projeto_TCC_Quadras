<div class="flex w-full flex-col gap-6">
    <div>
        <flux:heading size="xl">{{ __('Mensagens') }}</flux:heading>
        <flux:text class="mt-1">{{ __('Pedidos de suporte enviados pelos usuários na Central de Ajuda.') }}</flux:text>
    </div>

    <flux:select wire:model.live="filtroStatus" :label="__('Status')" class="max-w-xs rounded-full">
        <flux:select.option value="pendentes">{{ __('Pendentes') }}</flux:select.option>
        <flux:select.option value="respondidas">{{ __('Respondidas') }}</flux:select.option>
        <flux:select.option value="">{{ __('Todas') }}</flux:select.option>
    </flux:select>

    @if ($this->mensagens->isEmpty())
        <flux:card class="flex flex-col items-center gap-2 py-16 text-center">
            <flux:icon.chat-bubble-left-right class="size-8 text-zinc-300" />
            <flux:heading size="lg">{{ __('Nenhuma mensagem encontrada') }}</flux:heading>
            <flux:text>{{ __('Ajuste o filtro para ver outras mensagens.') }}</flux:text>
        </flux:card>
    @else
        <div class="flex flex-col gap-4">
            @foreach ($this->mensagens as $mensagem)
                <flux:card wire:key="mensagem-{{ $mensagem->id }}" class="flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <flux:heading size="lg">{{ $mensagem->assunto }}</flux:heading>
                            <flux:text class="mt-1">
                                {{ $mensagem->user?->name ?? 'Usuário removido' }}
                                @if ($mensagem->user)
                                    &middot; {{ $mensagem->user->email }}
                                @endif
                                &middot; {{ $mensagem->created_at->translatedFormat('d/m/Y \à\s H:i') }}
                            </flux:text>
                        </div>

                        @if ($mensagem->respondida_em)
                            <flux:badge color="green" size="sm">{{ __('Respondida') }}</flux:badge>
                        @else
                            <flux:badge color="amber" size="sm">{{ __('Pendente') }}</flux:badge>
                        @endif
                    </div>

                    <flux:text class="whitespace-pre-line text-zinc-700">{{ $mensagem->mensagem }}</flux:text>

                    <div class="flex flex-wrap gap-3">
                        @if ($mensagem->user)
                            <flux:button
                                href="mailto:{{ $mensagem->user->email }}?subject=Re: {{ $mensagem->assunto }}"
                                variant="outline"
                                size="sm"
                                class="rounded-full !border-orange-300 !text-orange-600 hover:!bg-orange-50"
                            >
                                {{ __('Responder por e-mail') }}
                            </flux:button>
                        @endif

                        @if ($mensagem->respondida_em)
                            <flux:button
                                wire:click="marcarComoPendente({{ $mensagem->id }})"
                                variant="ghost"
                                size="sm"
                                class="rounded-full"
                            >
                                {{ __('Marcar como pendente') }}
                            </flux:button>
                        @else
                            <flux:button
                                wire:click="marcarComoRespondida({{ $mensagem->id }})"
                                variant="primary"
                                color="orange"
                                size="sm"
                                class="rounded-full"
                            >
                                {{ __('Marcar como respondida') }}
                            </flux:button>
                        @endif
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
