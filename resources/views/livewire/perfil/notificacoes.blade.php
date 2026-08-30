<div>
    <h5 class="fw-bold text-secondary mb-1">Notificações</h5>
    <p class="text-muted small mb-4">Avisos sobre suas salas e oportunidades de quadras</p>

    <div class="d-flex flex-column gap-3">
        @forelse ($this->notificacoes as $notificacao)
            <div class="card border border-light-subtle shadow-none" style="border-radius: 15px;" wire:key="notificacao-{{ $loop->index }}">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="grupo-avatar flex-shrink-0" style="background-color: #fff3e0;">
                            <i class="bi {{ $notificacao['icone'] }} text-orange"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1" style="color: #2D3748;">{{ $notificacao['titulo'] }}</h6>
                            <p class="text-muted small mb-0">{{ $notificacao['mensagem'] }}</p>
                        </div>
                    </div>
                    <a href="{{ $notificacao['link'] }}" class="btn btn-outline-orange btn-sm rounded-2 text-nowrap">
                        {{ $notificacao['linkTexto'] }}
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="bi bi-bell text-warning" style="font-size: 2rem;"></i>
                <h5 class="fw-bold text-secondary mt-3 mb-1">Nenhuma notificação por agora</h5>
                <p class="text-muted small mb-0">Avisos sobre suas salas perto de fechar e quadras com preço abaixo da média aparecem aqui.</p>
            </div>
        @endforelse
    </div>
</div>
