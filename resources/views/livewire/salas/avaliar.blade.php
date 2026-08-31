<div class="container mb-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center detalhes-topo py-3">
        <a href="{{ route('salas.detalhes', $sala) }}" class="detalhes-link-topo text-decoration-none">
            <i class="bi bi-x-lg me-1"></i> Voltar
        </a>
        <h4 class="fw-bold text-orange mb-0">Avaliar Partida</h4>
        <span></span>
    </div>

    <p class="text-muted mb-4">
        {{ $sala->esporte->label() }} · {{ $sala->quadra?->nome ?? 'Local a definir' }} ·
        {{ $sala->data?->format('d/m/Y') }}
    </p>

    @if ($mensagemSucesso)
        <div class="alert alert-success">{{ $mensagemSucesso }}</div>
    @endif

    {{-- Avaliar o administrador --}}
    @if ($sala->criador_id !== auth()->id())
        <h6 class="fw-semibold texto-jogo mb-2">Administrador da sala</h6>
        <div class="card border-0 shadow-sm card-arredondado mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $sala->criador->avatarUrl() }}" alt="{{ $sala->criador->name }}" class="detalhes-avatar-participante">
                    <div class="flex-grow-1">
                        <p class="fw-semibold texto-jogo mb-0">{{ $sala->criador->name }}</p>
                        <p class="text-muted small mb-0">Administrador</p>
                    </div>

                    @if ($this->avaliacaoAdministrador && $alvoAtivo !== 'administrador')
                        <span class="text-muted"><i class="bi bi-star-fill text-warning"></i> {{ $this->avaliacaoAdministrador->nota }}/5</span>
                    @endif
                </div>

                @if ($alvoAtivo === 'administrador')
                    <x-avaliacao-formulario
                        :nota="$nota"
                        :comentario="$comentario"
                        acao="avaliarAdministrador"
                    />
                @else
                    <button type="button" wire:click="abrirAvaliacao('administrador')" class="btn btn-outline-laranja btn-sm fw-bold">
                        {{ $this->avaliacaoAdministrador ? 'Editar avaliação' : 'Avaliar administrador' }}
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Avaliar a quadra --}}
    @if ($sala->quadra)
        <h6 class="fw-semibold texto-jogo mb-2">A quadra</h6>
        <div class="card border-0 shadow-sm card-arredondado mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="flex-grow-1">
                        <p class="fw-semibold texto-jogo mb-0">{{ $sala->quadra->nome }}</p>
                        <p class="text-muted small mb-0">{{ $sala->quadra->endereco }} - {{ $sala->quadra->bairro }}</p>
                    </div>

                    @if ($this->avaliacaoQuadra && $alvoAtivo !== 'quadra')
                        <span class="text-muted"><i class="bi bi-star-fill text-warning"></i> {{ $this->avaliacaoQuadra->nota }}/5</span>
                    @endif
                </div>

                @if ($alvoAtivo === 'quadra')
                    <x-avaliacao-formulario
                        :nota="$nota"
                        :comentario="$comentario"
                        acao="avaliarQuadra"
                    />
                @else
                    <button type="button" wire:click="abrirAvaliacao('quadra')" class="btn btn-outline-laranja btn-sm fw-bold">
                        {{ $this->avaliacaoQuadra ? 'Editar avaliação' : 'Avaliar quadra' }}
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Avaliar os demais participantes --}}
    @if ($this->participantesParaAvaliar->isNotEmpty())
        <h6 class="fw-semibold texto-jogo mb-2">Jogadores do grupo</h6>
        <div class="card border-0 shadow-sm card-arredondado mb-4">
            <div class="card-body p-4">
                @foreach ($this->participantesParaAvaliar as $participante)
                    @php $avaliacaoParticipante = $this->avaliacoesParticipantes->get($participante->id); @endphp
                    <div class="py-3 {{ ! $loop->last ? 'border-bottom' : '' }}" wire:key="participante-{{ $participante->id }}">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img src="{{ $participante->avatarUrl() }}" alt="{{ $participante->name }}" class="detalhes-avatar-participante">
                            <div class="flex-grow-1">
                                <p class="fw-semibold texto-jogo mb-0">{{ $participante->name }}</p>
                                @if ($participante->nivel)
                                    <p class="text-muted small mb-0">{{ $participante->nivel->label() }}</p>
                                @endif
                            </div>

                            @if ($avaliacaoParticipante && $alvoAtivo !== 'participante-'.$participante->id)
                                <span class="text-muted"><i class="bi bi-star-fill text-warning"></i> {{ $avaliacaoParticipante->nota }}/5</span>
                            @endif
                        </div>

                        @if ($alvoAtivo === 'participante-'.$participante->id)
                            <x-avaliacao-formulario
                                :nota="$nota"
                                :comentario="$comentario"
                                acao="avaliarParticipante({{ $participante->id }})"
                            />
                        @else
                            <button type="button" wire:click="abrirAvaliacao('participante-{{ $participante->id }}')" class="btn btn-outline-laranja btn-sm fw-bold">
                                {{ $avaliacaoParticipante ? 'Editar avaliação' : 'Avaliar jogador' }}
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
