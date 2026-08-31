<div>
    <h5 class="fw-bold text-secondary mb-1">Minhas Salas</h5>
    <p class="text-muted small mb-4">Partidas que você criou ou das quais está participando</p>

    <h6 class="fw-bold text-secondary mb-3">Próximas</h6>
    <div class="d-flex flex-column gap-3 mb-4">
        @forelse ($this->futuras as $sala)
            <div class="card border border-light-subtle shadow-none" style="border-radius: 15px;" wire:key="sala-{{ $sala->id }}">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h6 class="fw-bold mb-0" style="color: #2D3748;">{{ $sala->esporte->label() }} @if ($sala->nivel_desejado) · {{ $sala->nivel_desejado->label() }} @endif</h6>
                            @if ($sala->criador_id === auth()->id())
                                <span class="badge grupo-badge-organizador">Organizador</span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            @if ($sala->quadra)
                                <span class="me-2"><i class="bi bi-geo-alt me-1 text-warning"></i> {{ $sala->quadra->nome }}</span>
                            @endif
                            @if ($sala->data)
                                <span class="me-2">• {{ $sala->data->isToday() ? 'Hoje' : $sala->data->format('d/m/Y') }}</span>
                                <span>• {{ $sala->horario_inicio?->format('H:i') }} - {{ $sala->horario_fim?->format('H:i') }}</span>
                            @else
                                <span>• Horário a definir</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-md-end d-flex flex-column align-items-md-end align-items-start">
                        <span class="fw-bold text-orange mb-2">{{ $sala->participantes->count() }} de {{ $sala->max_participantes }} jogadores</span>
                        <a href="{{ route('salas.grupo', $sala) }}" class="btn btn-outline-orange btn-sm rounded-2">
                            Ver sala
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted small">Você ainda não tem salas futuras.</p>
        @endforelse
    </div>

    <h6 class="fw-bold text-secondary mb-3">Anteriores</h6>
    <div class="d-flex flex-column gap-3">
        @forelse ($this->passadas as $sala)
            <div class="card border border-light-subtle shadow-none" style="border-radius: 15px; opacity: 0.75;" wire:key="sala-{{ $sala->id }}">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h6 class="fw-bold mb-0" style="color: #2D3748;">{{ $sala->esporte->label() }} @if ($sala->nivel_desejado) · {{ $sala->nivel_desejado->label() }} @endif</h6>
                            @if ($sala->criador_id === auth()->id())
                                <span class="badge grupo-badge-organizador">Organizador</span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            @if ($sala->quadra)
                                <span class="me-2"><i class="bi bi-geo-alt me-1 text-warning"></i> {{ $sala->quadra->nome }}</span>
                            @endif
                            @if ($sala->data)
                                <span class="me-2">• {{ $sala->data->format('d/m/Y') }}</span>
                            @endif
                            @if ($sala->horario_inicio)
                                <span>• {{ $sala->horario_inicio->format('H:i') }} - {{ $sala->horario_fim?->format('H:i') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-md-end d-flex flex-column align-items-md-end align-items-start">
                        <span class="fw-bold text-orange mb-2">{{ $sala->participantes->count() }} de {{ $sala->max_participantes }} jogadores</span>
                        @if ($sala->participantes->contains('id', auth()->id()))
                            <a href="{{ route('salas.avaliar', $sala) }}" class="btn btn-outline-orange btn-sm rounded-2">
                                <i class="bi bi-star me-1"></i> Avaliar
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted small">Nenhuma sala anterior.</p>
        @endforelse
    </div>
</div>
