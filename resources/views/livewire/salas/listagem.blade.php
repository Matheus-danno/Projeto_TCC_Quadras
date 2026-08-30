<div class="container mb-5">
    @if (session('sala-criada'))
        <div class="alert alert-success mt-4">{{ session('sala-criada') }}</div>
    @endif

    <div class="row g-4 mt-1">
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-3 card-arredondado filtros-card">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4">FILTROS</h6>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Esporte</label>
                        <select class="form-select border-secondary-subtle rounded-3" wire:model="esporte">
                            <option value="">Todos</option>
                            @foreach ($esportes as $opcao)
                                <option value="{{ $opcao->value }}">{{ $opcao->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Nível</label>
                        <select class="form-select border-secondary-subtle rounded-3" wire:model="nivel">
                            <option value="">Todos</option>
                            @foreach ($niveis as $opcao)
                                <option value="{{ $opcao->value }}">{{ $opcao->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Distância</label>
                        <select class="form-select border-secondary-subtle rounded-3" wire:model="distanciaKm">
                            <option value="">Qualquer distância</option>
                            <option value="1">Até 1km</option>
                            <option value="5">Até 5km</option>
                            <option value="10">Até 10km</option>
                            <option value="20">Até 20km</option>
                        </select>
                        @if (! $userLat)
                            <small class="text-muted d-block mt-1">Use "encontrar sala próxima de mim" para habilitar este filtro.</small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Horas</label>
                        <select class="form-select border-secondary-subtle rounded-3" wire:model="horario">
                            <option value="">Qualquer horário</option>
                            @foreach (range(6, 23) as $hora)
                                <option value="{{ sprintf('%02d:00', $hora) }}">{{ sprintf('%02dh00', $hora) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">Data</label>
                        <input type="date" class="form-control border-secondary-subtle rounded-3" wire:model="data" min="{{ now()->toDateString() }}">
                    </div>

                    <button type="button" wire:click="$refresh" class="btn btn-laranja w-100 fw-bold py-2 mb-2 shadow-sm card-arredondado">
                        Filtrar
                    </button>
                    <button type="button" wire:click="limparFiltros" class="btn btn-link w-100 text-secondary text-decoration-none">
                        Limpar Filtros
                    </button>
                </div>
            </div>

            <button
                type="button"
                x-data
                @click="
                    navigator.geolocation.getCurrentPosition(
                        (posicao) => $wire.usarLocalizacao(posicao.coords.latitude, posicao.coords.longitude),
                        () => alert('Não foi possível obter sua localização.')
                    )
                "
                class="btn btn-laranja w-100 py-2 mb-3 shadow-sm card-arredondado d-flex flex-column align-items-center gap-0"
            >
                Encontrar uma sala próxima de mim!
                <i class="bi bi-geo-alt-fill"></i>
            </button>

            @php
                $mapaLat = $userLat ?? -23.5505;
                $mapaLng = $userLng ?? -46.6333;
            @endphp
            <div class="mapa-container card-arredondado">
                <iframe
                    width="100%" height="100%" style="border:0"
                    loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $mapaLng - 0.03 }}%2C{{ $mapaLat - 0.02 }}%2C{{ $mapaLng + 0.03 }}%2C{{ $mapaLat + 0.02 }}&layer=mapnik&marker={{ $mapaLat }}%2C{{ $mapaLng }}"
                ></iframe>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="d-flex flex-column gap-3">
                @forelse ($this->salas as $sala)
                    <div class="card border-0 shadow-sm card-partida card-jogo" wire:key="sala-{{ $sala->id }}" style="border-left: 6px solid {{ $sala->esporte->cor() }};">
                        @php
                            $avatares = view('livewire.salas.partials.avatares', ['sala' => $sala])->render();
                        @endphp
                        <div class="card-body p-4 d-flex flex-column gap-1">
                            @if ($sala->destaque)
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge-destaque">
                                        <i class="bi bi-star-fill"></i> Destaque
                                    </span>

                                    {!! $avatares !!}
                                </div>
                            @endif

                            <div class="d-flex flex-column flex-md-row gap-4 align-items-md-stretch">
                                @php $imagensSala = collect($sala->quadra?->listaImagens() ?? [$sala->esporte->imagem()])->map(fn ($img) => asset($img))->all(); @endphp
                                <div class="position-relative card-jogo-imagem-wrap" x-data="{ imagens: @js($imagensSala), indice: 0 }">
                                    <img :src="imagens[indice]" alt="{{ $sala->esporte->label() }}">
                                    <template x-if="imagens.length > 1">
                                        <button type="button" class="quadra-carrossel-seta quadra-carrossel-seta-esquerda" @click.stop.prevent="indice = (indice - 1 + imagens.length) % imagens.length">
                                            <i class="bi bi-chevron-left"></i>
                                        </button>
                                    </template>
                                    <template x-if="imagens.length > 1">
                                        <button type="button" class="quadra-carrossel-seta quadra-carrossel-seta-direita" @click.stop.prevent="indice = (indice + 1) % imagens.length">
                                            <i class="bi bi-chevron-right"></i>
                                        </button>
                                    </template>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h5 class="fw-semibold mb-0 texto-jogo">{{ $sala->esporte->label() }} @if ($sala->nivel_desejado) - {{ $sala->nivel_desejado->label() }} @endif</h5>

                                        @unless ($sala->destaque)
                                            {!! $avatares !!}
                                        @endunless
                                    </div>

                                    <div class="text-muted small mb-1 d-flex flex-wrap gap-3">
                                        <span><i class="bi bi-geo-alt text-orange me-1"></i>{{ $sala->quadra?->nome ?? 'Local a definir' }}</span>
                                    </div>

                                    <div class="text-muted small mb-2 d-flex flex-wrap gap-3">
                                        <span>
                                            <i class="bi bi-clock text-orange me-1"></i>
                                            @if ($sala->data)
                                                {{ $sala->data->isToday() ? 'Hoje' : $sala->data->format('d/m') }},
                                                {{ $sala->horario_inicio?->format('H:i') }} - {{ $sala->horario_fim?->format('H:i') }}
                                            @else
                                                Horário a definir
                                            @endif
                                        </span>
                                    </div>

                                    <div class="d-flex gap-2 mb-2 flex-wrap">
                                        <span class="badge badge-vagas">
                                            {{ $sala->participantes->count() }}/{{ $sala->max_participantes }} vagas
                                        </span>
                                        @if ($sala->tempoParaComecoFormatado())
                                            <span class="badge badge-tempo">
                                                {{ $sala->vagasRestantes() > 0 ? "faltam {$sala->vagasRestantes()}" : 'completa' }}
                                                · fecha em {{ $sala->tempoParaComecoFormatado() }}
                                            </span>
                                        @endif
                                    </div>

                                    @if ($sala->precoPessoaCalculado())
                                        <p class="fw-semibold texto-jogo mb-0">
                                            R$ {{ number_format($sala->precoPessoaCalculado(), 2, ',', '.') }} / pessoa
                                        </p>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2">
                                        <div>
                                            <div class="text-muted small d-flex align-items-center gap-1">
                                                Administrador: {{ $sala->criador->name }}
                                                @if ($sala->criador->notaMedia())
                                                    <span class="ms-1"><i class="bi bi-star-fill text-warning"></i> {{ number_format($sala->criador->notaMedia(), 1, ',', '.') }}</span>
                                                @endif
                                            </div>
                                            @if ($sala->distanciaKm ?? null)
                                                <p class="text-danger small mb-3 mt-1">
                                                    <i class="bi bi-signpost-2"></i> {{ $sala->distanciaKm }} km de distância de você
                                                </p>
                                            @endif
                                        </div>

                                        @guest
                                            <a href="#" class="btn btn-outline-laranja btn-ver-detalhes fw-bold" data-bs-toggle="modal" data-bs-target="#modalLoginNecessario" data-mensagem="Você precisa entrar para ver os detalhes da sala.">Ver Detalhes</a>
                                        @else
                                            <a href="{{ route('salas.detalhes', $sala) }}" class="btn btn-outline-laranja btn-ver-detalhes fw-bold">Ver Detalhes</a>
                                        @endguest
                                    </div>

                                    @if (isset($erros[$sala->id]))
                                        <div class="text-danger small mt-2">{{ $erros[$sala->id] }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-5">Nenhuma sala encontrada com esses filtros.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
