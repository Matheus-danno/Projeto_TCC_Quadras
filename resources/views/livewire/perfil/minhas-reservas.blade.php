<div>
    <h5 class="fw-bold text-secondary mb-1">Histórico de Reservas</h5>
    <p class="text-muted small mb-4">Confira todas as suas reservas anteriores e futuras</p>

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
                        <span class="fw-bold fs-5 mb-2" style="color: #FF8C00;">R$ {{ number_format($reserva->quadra->valor_hora, 2, ',', '.') }}</span>

                        @if ($reserva->sala)
                            <a href="{{ route('salas.grupo', $reserva->sala) }}" class="btn btn-outline-orange btn-sm rounded-2 mb-2">
                                Ver minha sala
                            </a>
                        @endif

                        @if ($reserva->podeCancelar())
                            @if ($reserva->status->value === 'confirmada')
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelarReserva{{ $reserva->id }}">
                                    Cancelar
                                </button>
                            @else
                                <button type="button" class="btn btn-outline-danger btn-sm" wire:click="cancelar({{ $reserva->id }})" wire:confirm="Cancelar esta reserva?">
                                    Cancelar
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                @if (isset($erros[$reserva->id]))
                    <div class="text-danger small px-3 pb-3">{{ $erros[$reserva->id] }}</div>
                @endif
            </div>

            @if ($reserva->status->value === 'confirmada' && $reserva->podeCancelar())
                <div class="modal fade" id="cancelarReserva{{ $reserva->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4">
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" style="z-index: 1;" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            <div class="modal-body p-4 text-center">
                                <i class="bi bi-question-circle text-orange" style="font-size: 2.5rem;"></i>
                                <p class="fw-semibold mt-3 mb-1">Cancelar reserva na {{ $reserva->quadra->nome }}?</p>
                                <p class="text-muted small mb-4">Prefere receber o dinheiro de volta ou guardar como crédito para uma próxima reserva?</p>
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-outline-secondary fw-bold px-3" data-bs-dismiss="modal" wire:click="cancelar({{ $reserva->id }}, 'extorno')">
                                        Quero reembolso
                                    </button>
                                    <button type="button" class="btn btn-orange-action fw-bold px-3" data-bs-dismiss="modal" wire:click="cancelar({{ $reserva->id }}, 'credito')">
                                        Quero crédito
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
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
                    <div class="text-md-end d-flex flex-column align-items-md-end align-items-start">
                        <span class="badge rounded-pill px-3 mb-2 {{ match ($reserva->status->value) {
                            'confirmada' => 'bg-success',
                            'pendente' => 'bg-warning text-dark',
                            'cancelada' => 'bg-secondary',
                        } }} text-white">{{ $reserva->status->label() }}</span>

                        @if ($reserva->sala)
                            <a href="{{ route('salas.grupo', $reserva->sala) }}" class="btn btn-outline-orange btn-sm rounded-2">
                                Ver minha sala
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted small">Nenhuma reserva anterior.</p>
        @endforelse
    </div>
</div>
