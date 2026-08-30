<div>
    <h5 class="fw-bold text-secondary mb-1">Mensagens</h5>
    <p class="text-muted small mb-4">Fale com o dono das quadras que você alugou ao organizar uma sala</p>

    @if ($quadraSelecionada === null)
        <div class="d-flex flex-column gap-3">
            @forelse ($this->quadras as $quadra)
                @php $ultimaMensagem = $quadra->ultimaMensagem; @endphp
                <button
                    type="button"
                    wire:click="selecionarQuadra({{ $quadra->id }})"
                    wire:key="quadra-{{ $quadra->id }}"
                    class="card border border-light-subtle shadow-none text-start bg-white"
                    style="border-radius: 15px;"
                >
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h6 class="fw-bold mb-1" style="color: #2D3748;">{{ $quadra->nome }}</h6>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-person-badge me-1"></i> {{ $quadra->dono?->nome_estabelecimento ?? $quadra->dono?->name ?? 'Dono da quadra' }}
                            </p>
                            @if ($ultimaMensagem)
                                <p class="text-muted small mb-0 mt-1">{{ Str::limit($ultimaMensagem->texto, 60) }} · {{ $ultimaMensagem->tempoDecorrido() }} atrás</p>
                            @else
                                <p class="text-muted small mb-0 mt-1">Nenhuma mensagem ainda</p>
                            @endif
                        </div>
                        <i class="bi bi-chevron-right text-orange"></i>
                    </div>
                </button>
            @empty
                <p class="text-muted small">Você ainda não organizou nenhuma sala em uma quadra. Depois de criar uma sala, você poderá falar com o dono dela aqui.</p>
            @endforelse
        </div>
    @else
        @php $quadra = $this->quadras->firstWhere('id', $quadraSelecionada); @endphp
        <div class="d-flex align-items-center gap-2 mb-3">
            <button type="button" wire:click="voltar" class="btn btn-link text-secondary text-decoration-none p-0">
                <i class="bi bi-chevron-left"></i> Voltar
            </button>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1" style="color: #2D3748;">{{ $quadra->nome }}</h6>
                <p class="text-muted small mb-3">
                    <i class="bi bi-person-badge me-1"></i> {{ $quadra->dono?->nome_estabelecimento ?? $quadra->dono?->name ?? 'Dono da quadra' }}
                </p>

                <div class="mb-3" style="max-height: 320px; overflow-y: auto;" wire:poll.5s x-init="$el.scrollTop = $el.scrollHeight">
                    @forelse (($this->conversaAtual?->mensagens ?? collect()) as $mensagem)
                        <div class="d-flex gap-2 mb-3 {{ $mensagem->user_id === auth()->id() ? 'flex-row-reverse text-end' : '' }}" wire:key="mensagem-{{ $mensagem->id }}">
                            <div class="grupo-avatar flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.7rem;">
                                {{ $mensagem->user?->initials() }}
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 {{ $mensagem->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                                    <span class="fw-semibold small">{{ $mensagem->user_id === auth()->id() ? 'Você' : ($quadra->dono?->nome_estabelecimento ?? $quadra->dono?->name) }}</span>
                                    <span class="text-muted" style="font-size: 0.7rem;">{{ $mensagem->tempoDecorrido() }} atrás</span>
                                </div>
                                <p class="mb-0 small">{{ $mensagem->texto }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small text-center py-3 mb-0">Nenhuma mensagem ainda. Envie a primeira!</p>
                    @endforelse
                </div>

                <form wire:submit.prevent="enviarMensagem" class="d-flex gap-2">
                    <input type="text" class="form-control border-orange rounded-2" wire:model="novaMensagem" placeholder="Escreva uma mensagem..." maxlength="500">
                    <button type="submit" class="btn btn-laranja fw-bold px-4">Enviar</button>
                </form>
                @error('novaMensagem') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>
    @endif
</div>
