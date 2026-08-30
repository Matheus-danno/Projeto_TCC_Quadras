<div>
    <h5 class="fw-bold text-secondary mb-1">Histórico de Reservas</h5>
    <p class="text-muted small mb-4">Confira todas as suas reservas anteriores e futuras</p>

    @if ($erroCancelamento)
        <div class="alert alert-danger">{{ $erroCancelamento }}</div>
    @endif

    <h6 class="fw-bold text-secondary mb-3">Próximas</h6>
    <div class="d-flex flex-column gap-3 mb-4">
        @forelse ($this->futuras as $reserva)
            <div class="card border border-light-subtle shadow-none" style="border-radius: 15px;" wire:key="reserva-{{ $reserva->id }}">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h6 class="fw-bold mb-1" style="color: #2D3748;">{{ $reserva->quadra->nome }}</h6>
                        <div class="text-muted small">
                            <span class="me-2"><i class="bi bi-calendar-check me-1 text-warning"></i> {{ $reserva->data->format('d/m/Y') }}</span>
                            <span class="me-2">• {{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fim, 0, 5) }}</span>
                            <span>• {{ $reserva->quadra->esporte->label() }}</span>
                        </div>
                    </div>
                    <div class="text-md-end d-flex flex-column align-items-md-end align-items-start">
                        <span class="badge rounded-pill px-3 mb-2 {{ match ($reserva->status->value) {
                            'confirmada' => 'bg-success',
                            'pendente' => 'bg-warning text-dark',
                            'cancelada' => 'bg-secondary',
                        } }} text-white">{{ $reserva->status->label() }}</span>
                        <span class="fw-bold fs-5" style="color: #FF8C00;">R$ {{ number_format($reserva->quadra->valor_hora, 2, ',', '.') }}</span>

                        @if ($reserva->podeCancelar())
                            @if ($reserva->status->value === 'pendente')
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger mt-2"
                                    wire:click="cancelar({{ $reserva->id }})"
                                    wire:confirm="Cancelar essa reserva?"
                                >
                                    Cancelar
                                </button>
                            @else
                                <div class="d-flex gap-2 mt-2">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        wire:click="cancelar({{ $reserva->id }}, 'credito')"
                                        wire:confirm="Cancelar e receber R$ {{ number_format($reserva->quadra->valor_hora, 2, ',', '.') }} como crédito?"
                                    >
                                        Cancelar (receber crédito)
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        wire:click="cancelar({{ $reserva->id }}, 'extorno')"
                                        wire:confirm="Cancelar e solicitar extorno?"
                                    >
                                        Cancelar (extorno)
                                    </button>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted small">Você ainda não tem reservas futuras.</p>
        @endforelse
    </div>

    <h6 class="fw-bold text-secondary mb-3">Anteriores</h6>
    <div class="d-flex flex-column gap-3">
        @forelse ($this->passadas as $reserva)
            <div class="card border border-light-subtle shadow-none" style="border-radius: 15px; opacity: 0.75;" wire:key="reserva-{{ $reserva->id }}">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h6 class="fw-bold mb-1" style="color: #2D3748;">{{ $reserva->quadra->nome }}</h6>
                        <div class="text-muted small">
                            <span class="me-2"><i class="bi bi-calendar-check me-1 text-warning"></i> {{ $reserva->data->format('d/m/Y') }}</span>
                            <span class="me-2">• {{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fim, 0, 5) }}</span>
                            <span>• {{ $reserva->quadra->esporte->label() }}</span>
                        </div>
                    </div>
                    <span class="badge rounded-pill px-3 {{ match ($reserva->status->value) {
                        'confirmada' => 'bg-success',
                        'pendente' => 'bg-warning text-dark',
                        'cancelada' => 'bg-secondary',
                    } }} text-white">{{ $reserva->status->label() }}</span>
                </div>
            </div>
        @empty
            <p class="text-muted small">Nenhuma reserva anterior.</p>
        @endforelse
    </div>
</div>
