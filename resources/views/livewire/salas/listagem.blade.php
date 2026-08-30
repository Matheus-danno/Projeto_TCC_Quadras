<div class="container mb-5">
    @if (session('sala-criada'))
        <div class="alert alert-success mt-4">{{ session('sala-criada') }}</div>
    @endif

    <div class="row g-4 mt-1">
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-3 card-arredondado">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4">FILTROS</h6>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Esporte</label>
                        <select class="form-select border-secondary-subtle rounded-3" wire:model.live="esporte">
                            <option value="">Todos</option>
                            @foreach ($esportes as $opcao)
                                <option value="{{ $opcao->value }}">{{ $opcao->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <a href="{{ route('criar_sala') }}" class="btn btn-laranja w-100 fw-bold py-3 mb-2 shadow-sm d-flex align-items-center justify-content-center card-arredondado text-decoration-none">
                <i class="bi bi-plus-circle me-2"></i> Criar uma Sala
            </a>
        </div>

        <div class="col-lg-9">
            <div class="d-flex flex-column gap-3">
                @forelse ($this->salas as $sala)
                    <div class="card border-0 shadow-sm card-partida" wire:key="sala-{{ $sala->id }}">
                        <div class="card-body p-4 d-flex flex-column flex-md-row gap-4 align-items-md-center">
                            <div class="icone-esporte bg-warning">
                                <i class="bi bi-dribbble text-white fs-1"></i>
                            </div>

                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1 texto-escuro">{{ $sala->nome }} - {{ $sala->esporte->label() }}</h5>
                                <p class="text-muted small mb-1">
                                    <i class="bi bi-geo-alt text-warning me-1"></i>
                                    {{ $sala->quadra?->nome ?? 'Local a definir' }}
                                </p>

                                <div class="d-flex gap-2 mb-2">
                                    <span class="badge bg-info text-white rounded-pill px-3 py-2">
                                        {{ $sala->participantes->count() }}/{{ $sala->max_participantes }} vagas
                                    </span>
                                </div>

                                <div class="text-muted small d-flex align-items-center gap-1">
                                    Administrador: {{ $sala->criador->name }}
                                </div>
                            </div>

                            <div class="d-flex flex-column align-items-md-end justify-content-between h-100">
                                <div class="grupo-avatares mb-3 mb-md-0">
                                    @foreach ($sala->participantes->take(3) as $participante)
                                        <img src="https://i.pravatar.cc/150?u={{ $participante->id }}" alt="{{ $participante->name }}">
                                    @endforeach
                                    @if ($sala->participantes->count() > 3)
                                        <div class="avatar-extra">+{{ $sala->participantes->count() - 3 }}</div>
                                    @endif
                                </div>

                                <div class="d-flex gap-2 mt-auto">
                                    <a href="{{ route('salas.detalhes', $sala) }}" class="btn btn-outline-secondary fw-bold px-3 rounded-pill">
                                        Ver detalhes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-5">Nenhuma sala aberta no momento.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
