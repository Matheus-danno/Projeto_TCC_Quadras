<div class="container mb-5" style="max-width: 700px;">
    @if (session('sala-criada'))
        <div class="alert alert-success mt-3">{{ session('sala-criada') }}</div>
    @endif
    @if ($erro)
        <div class="alert alert-danger mt-3">{{ $erro }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center detalhes-topo py-3">
        <h4 class="fw-bold text-orange mb-0"><i class="bi bi-door-open me-2"></i>Sala</h4>
        <span class="badge grupo-badge-voce">Você está nessa sala</span>
    </div>

    <div class="card border-0 shadow-sm card-arredondado mb-3">
        <div class="card-body p-4">
            <p class="fw-semibold texto-jogo mb-3">{{ $sala->esporte->label() }} @if ($sala->nivel_desejado) · {{ $sala->nivel_desejado->label() }} @endif</p>
            <ul class="list-unstyled text-muted mb-0">
                @if ($sala->quadra)
                    <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>{{ $sala->quadra->nome }} - {{ $sala->quadra->endereco }}, {{ $sala->quadra->bairro }} - {{ $sala->quadra->cidade }}</li>
                @endif
                @if ($sala->data)
                    <li class="mb-2"><i class="bi bi-calendar3 me-2"></i>{{ $sala->data->isToday() ? 'Hoje' : $sala->data->translatedFormat('d \d\e F \d\e Y') }}</li>
                    <li class="mb-2">
                        <i class="bi bi-clock me-2"></i>{{ $sala->horario_inicio?->format('H:i') }} - {{ $sala->horario_fim?->format('H:i') }}
                        @if ($sala->duracaoFormatada())
                            ({{ $sala->duracaoFormatada() }})
                        @endif
                    </li>
                @endif
                @if ($sala->precoPessoaCalculado())
                    <li class="mb-0"><i class="bi bi-cash-coin me-2"></i>R$ {{ number_format($sala->precoPessoaCalculado(), 2, ',', '.') }} por pessoa</li>
                @endif
            </ul>
        </div>
    </div>

    <div class="card border-0 shadow-sm card-arredondado mb-3">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="fw-semibold texto-jogo mb-0">Quem está na sala</p>
                <span class="fw-bold text-orange">{{ $sala->participantes->count() }} de {{ $sala->max_participantes }}</span>
            </div>
            <div class="progress mb-4" style="height: 6px;">
                <div class="progress-bar" style="width: {{ $sala->percentualOcupacao() }}%; background-color: #FF8C00;"></div>
            </div>

            <div style="max-height: 280px; overflow-y: auto;">
                @foreach ($sala->participantes as $index => $participante)
                    <div class="d-flex align-items-center gap-3 py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <div class="grupo-avatar" style="background-color: {{ ['#ffe0b2', '#c8e6c9', '#bbdefb', '#f8bbd0', '#d1c4e9', '#b2ebf2'][$index % 6] }};">
                            {{ $participante->initials() }}
                        </div>
                        <p class="fw-semibold texto-jogo mb-0 flex-grow-1">{{ $participante->name }}</p>
                        @if ($participante->id === $sala->criador_id)
                            <span class="badge grupo-badge-organizador">Organizador</span>
                        @endif
                        @if ($participante->id === auth()->id())
                            <span class="badge grupo-badge-voce">Você</span>
                        @endif
                    </div>
                @endforeach

                @if ($sala->status->value === 'fechada')
                    <div class="text-muted small mt-2"><i class="bi bi-lock-fill me-1"></i> Sala fechada, sem mais vagas oferecidas.</div>
                @else
                    @for ($i = 0; $i < $sala->vagasRestantes(); $i++)
                        <div class="d-flex align-items-center gap-3 py-2 {{ ($sala->participantes->count() + $i) < $sala->max_participantes - 1 ? 'border-bottom' : '' }}">
                            <div class="grupo-avatar grupo-avatar-vazio"></div>
                            <p class="text-muted mb-0">Vaga livre</p>
                        </div>
                    @endfor
                @endif
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm card-arredondado mb-3">
        <div class="card-body p-4">
            <p class="fw-semibold texto-jogo mb-3">Chat da sala</p>

            <div
                class="mb-3"
                style="max-height: 320px; overflow-y: auto;"
                wire:poll.5s="atualizarMensagens"
                x-init="$el.scrollTop = $el.scrollHeight"
            >
                @forelse ($sala->mensagens as $index => $mensagem)
                    <div class="d-flex gap-2 mb-3 {{ $mensagem->user_id === auth()->id() ? 'flex-row-reverse text-end' : '' }}" wire:key="mensagem-{{ $mensagem->id }}">
                        <div class="grupo-avatar flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.7rem; background-color: {{ ['#ffe0b2', '#c8e6c9', '#bbdefb', '#f8bbd0', '#d1c4e9', '#b2ebf2'][$index % 6] }};">
                            {{ $mensagem->user?->initials() }}
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 {{ $mensagem->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                                <span class="fw-semibold small">{{ $mensagem->user_id === auth()->id() ? 'Você' : $mensagem->user?->name }}</span>
                                @if ($mensagem->user_id === $sala->criador_id)
                                    <span class="badge grupo-badge-organizador" style="font-size: 0.6rem;">Organizador</span>
                                @endif
                                @if ($mensagem->destinatario_id)
                                    <span class="badge bg-secondary" style="font-size: 0.6rem;"><i class="bi bi-lock-fill"></i> Privado</span>
                                @endif
                                <span class="text-muted" style="font-size: 0.7rem;">{{ $mensagem->tempoDecorrido() }} atrás</span>
                            </div>
                            <p class="mb-0 small">{{ $mensagem->texto }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small text-center py-3 mb-0">Nenhuma mensagem ainda. Comece a conversa!</p>
                @endforelse
            </div>

            @unless (auth()->id() === $sala->criador_id)
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" wire:model="mensagemPrivada" id="mensagemPrivada">
                    <label class="form-check-label small text-muted" for="mensagemPrivada">
                        <i class="bi bi-lock-fill"></i> Enviar só para o organizador
                    </label>
                </div>
            @endunless

            <form wire:submit.prevent="enviarMensagem" class="d-flex gap-2">
                <input type="text" class="form-control border-orange rounded-2" wire:model="novaMensagem" placeholder="Escreva uma mensagem..." maxlength="500">
                <button type="submit" class="btn btn-laranja fw-bold px-4">Enviar</button>
            </form>
            @error('novaMensagem') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
    </div>

    @if (auth()->id() === $sala->criador_id && $sala->podeFecharComVagas())
        <div class="card border-0 shadow-sm card-arredondado mb-3">
            <div class="card-body p-4">
                <p class="fw-semibold texto-jogo mb-2"><i class="bi bi-cash-coin me-2"></i>Faltam jogadores?</p>
                <p class="text-muted small mb-3">
                    Você pode fechar a sala agora com os {{ $sala->participantes->count() }} jogadores confirmados,
                    pagando a diferença de <strong>R$ {{ number_format($sala->diferencaParaFechar(), 2, ',', '.') }}</strong>
                    para cobrir o valor total da quadra.
                </p>
                <a href="{{ route('salas.pagar-diferenca', $sala) }}" class="btn btn-outline-laranja fw-bold w-100 py-2">
                    Fechar sala e pagar a diferença
                </a>
            </div>
        </div>
    @endif

    @if ($sala->atividades->isNotEmpty())
        <div class="card border-0 shadow-sm card-arredondado mb-3">
            <div class="card-body p-4">
                <p class="fw-semibold texto-jogo mb-3">Atividade da sala</p>
                <ul class="list-unstyled text-muted small mb-0">
                    @foreach ($sala->atividades->take(10) as $atividade)
                        <li class="mb-2">{{ $atividade->descricao }} <span class="text-muted">· {{ $atividade->tempoDecorrido() }} atrás</span></li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <button
                type="button"
                wire:click="sairDaSala"
                wire:confirm="Tem certeza que deseja sair da sala?"
                class="btn btn-outline-danger fw-bold w-100 py-2"
            >
                Sair da sala
            </button>
        </div>
        <div class="col-md-6">
            <button
                type="button"
                x-data="{ copiado: false }"
                @click="
                    navigator.clipboard.writeText(@js(route('salas.detalhes', $sala)));
                    copiado = true;
                    setTimeout(() => copiado = false, 2000);
                "
                class="btn btn-laranja fw-bold w-100 py-2"
            >
                <template x-if="!copiado"><span><i class="bi bi-share me-1"></i> Compartilhar</span></template>
                <template x-if="copiado"><span><i class="bi bi-check-lg me-1"></i> Link copiado!</span></template>
            </button>
        </div>
    </div>
</div>
