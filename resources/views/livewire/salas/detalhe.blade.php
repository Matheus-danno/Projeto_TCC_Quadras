<div class="container my-5" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center border-bottom border-3 border-warning pb-3 mb-4">
        <a href="{{ route('encontre_time') }}" class="d-inline-flex align-items-center gap-1 text-decoration-none fw-bold" style="color: #FF8C00;">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <h1 class="fw-bold fs-3 mb-0 text-center" style="color: #FF8C00;">Detalhes da Sala</h1>
        <span style="width: 70px;"></span>
    </div>

    <div class="card border-0 shadow-sm card-arredondado overflow-hidden mb-4">
        <div id="carrosselDetalhesSala" class="carousel slide">
            <div class="carousel-inner">
                @forelse (($sala->quadra?->fotos ?? []) as $indice => $foto)
                    <div class="carousel-item {{ $indice === 0 ? 'active' : '' }}">
                        <img src="{{ $foto->url() }}" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="{{ $sala->quadra->nome }}">
                    </div>
                @empty
                    <div class="carousel-item active">
                        <img src="{{ asset('imagens/tela_inicial/quadra_volei2.png') }}" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="{{ $sala->nome }}">
                    </div>
                @endforelse
            </div>

            @if (($sala->quadra?->fotos->count() ?? 0) > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#carrosselDetalhesSala" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Foto anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carrosselDetalhesSala" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Próxima foto</span>
                </button>
                <div class="carousel-indicators">
                    @foreach (($sala->quadra?->fotos ?? []) as $indice => $foto)
                        <button type="button" data-bs-target="#carrosselDetalhesSala" data-bs-slide-to="{{ $indice }}" class="{{ $indice === 0 ? 'active' : '' }}" aria-label="Foto {{ $indice + 1 }}"></button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm card-arredondado p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
            <h2 class="fw-bold texto-escuro mb-0">{{ $sala->nome }} - {{ $sala->esporte->label() }}</h2>
            <span class="badge bg-info text-white rounded-pill px-3 py-2 fs-6">
                {{ $sala->participantes->count() }}/{{ $sala->max_participantes }} vagas
            </span>
        </div>

        @if ($sala->nivel_desejado)
            <span class="badge bg-laranja-principal text-white rounded-pill px-3 py-2 mb-3">Nível {{ $sala->nivel_desejado->label() }}</span>
        @endif

        <hr>

        <div class="row g-4 mb-3">
            <div class="col-md-6">
                <p class="text-muted small fw-bold mb-1"><i class="bi bi-geo-alt text-warning me-1"></i>Local</p>
                @if ($sala->quadra)
                    <p class="fw-bold mb-0">{{ $sala->quadra->nome }}</p>
                    <p class="text-muted small mb-0">{{ $sala->quadra->endereco }} - {{ $sala->quadra->bairro }}, {{ $sala->quadra->cidade }}</p>
                @else
                    <p class="text-muted mb-0">Local a definir</p>
                @endif
            </div>

            <div class="col-md-6">
                <p class="text-muted small fw-bold mb-1"><i class="bi bi-clock text-warning me-1"></i>Data e Horário</p>
                @if ($sala->data && $sala->hora_inicio)
                    <p class="fw-bold mb-0">
                        {{ $sala->data->format('d/m/Y') }}, {{ \Illuminate\Support\Carbon::parse($sala->hora_inicio)->format('H:i') }}
                        - {{ \Illuminate\Support\Carbon::parse($sala->horaFim)->format('H:i') }}
                    </p>
                    <p class="text-muted small mb-0">Duração: {{ intdiv($sala->duracao_minutos, 60) }}h{{ str_pad($sala->duracao_minutos % 60, 2, '0', STR_PAD_LEFT) }}min</p>
                @else
                    <p class="text-muted mb-0">A combinar</p>
                @endif
            </div>
        </div>

        @if ($sala->quadra)
            <div class="row g-4 mb-3">
                <div class="col-md-6">
                    <p class="text-muted small fw-bold mb-1">Valor da Quadra</p>
                    <p class="fw-bold fs-5 mb-0" style="color: #FF8C00;">R$ {{ number_format($sala->quadra->valor_hora, 2, ',', '.') }} / hora</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted small fw-bold mb-1">Estrutura</p>
                    <p class="text-muted mb-0">
                        {{ $sala->quadra->cobertura ? 'Coberta' : 'Descoberta' }}
                        @if ($sala->quadra->descricao)
                            | {{ $sala->quadra->descricao }}
                        @endif
                    </p>
                </div>
            </div>
        @endif

        <hr>

        <div class="d-flex align-items-center gap-3 mb-3">
            <img src="https://i.pravatar.cc/150?u={{ $sala->criador->id }}" alt="{{ $sala->criador->name }}" class="rounded-circle" width="56" height="56">
            <div>
                <p class="fw-bold mb-0">{{ $sala->criador->name }}</p>
                <p class="text-muted small mb-0">Administrador da Sala</p>
            </div>
        </div>

        @if ($sala->regras_adicionais)
            <div>
                <p class="fw-bold texto-escuro mb-1">Regras da sala</p>
                <p class="text-muted small mb-0" style="white-space: pre-line;">{{ $sala->regras_adicionais }}</p>
            </div>
        @endif
    </div>

    @guest
        <p class="small mb-3">Você precisa <a href="{{ route('login') }}">entrar</a> para participar.</p>
    @endguest

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('encontre_time') }}" class="btn btn-outline-secondary fw-bold px-4 rounded-pill">Voltar</a>

        @auth
            @if ($sala->participantes->contains('id', auth()->id()))
                <button type="button" class="btn btn-outline-secondary fw-bold px-4 rounded-pill" disabled>Você já está nessa sala</button>
            @elseif ($sala->participantes->count() >= $sala->max_participantes)
                <button type="button" class="btn btn-outline-secondary fw-bold px-4 rounded-pill" disabled>Sala cheia</button>
            @else
                <a href="{{ route('salas.pagamento', $sala) }}" class="btn btn-laranja fw-bold px-4 rounded-pill">
                    Entrar e Pagar
                    @if ($sala->quadra)
                        - R$ {{ number_format($sala->valorPorPessoa(), 2, ',', '.') }}
                    @endif
                </a>
            @endif
        @endauth
    </div>
</div>
