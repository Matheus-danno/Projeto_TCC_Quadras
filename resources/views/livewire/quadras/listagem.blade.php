<div>
    {{-- Seção de Filtros --}}
    <div class="row mb-4 g-2 align-items-center">
        <div class="col-auto"><strong>FILTROS</strong></div>
        <div class="col-auto">
            <select class="form-select border-orange filtro-select" wire:model.live="cidade">
                <option value="">Cidade</option>
                @foreach ($this->cidades as $cidade)
                    <option value="{{ $cidade }}">{{ $cidade }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select border-orange filtro-select" wire:model.live="bairro">
                <option value="">Bairro</option>
                @foreach ($this->bairros as $bairro)
                    <option value="{{ $bairro }}">{{ $bairro }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select border-orange filtro-select" wire:model.live="esporte">
                <option value="">Esporte</option>
                @foreach ($esportes as $opcao)
                    <option value="{{ $opcao->value }}">{{ $opcao->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select border-orange filtro-select" wire:model.live="quadraNome">
                <option value="">Quadra</option>
                @foreach ($this->nomesQuadras as $nome)
                    <option value="{{ $nome }}">{{ $nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select border-orange filtro-select" wire:model.live="cobertura">
                <option value="">Cobertura</option>
                <option value="1">Coberta</option>
                <option value="0">Descoberta</option>
            </select>
        </div>
        <div class="col-auto ms-3">
            <button
                type="button"
                x-data
                @click="
                    navigator.geolocation.getCurrentPosition(
                        (posicao) => $wire.usarLocalizacao(posicao.coords.latitude, posicao.coords.longitude),
                        () => alert('Não foi possível obter sua localização.')
                    )
                "
                class="btn btn-sm {{ $ordenarPorProximidade ? 'btn-outline-orange btn-outline-orange-active' : 'btn-orange-quadras-proximas' }} rounded-2 fw-bold d-inline-flex align-items-center gap-1 text-nowrap"
            >
                <i class="bi bi-geo-alt-fill"></i> Quadras mais próximas
            </button>
        </div>
        @if ($this->temFiltrosAtivos())
            <div class="col-auto">
                <button
                    type="button"
                    wire:click="limparFiltros"
                    class="btn btn-sm btn-link text-secondary text-decoration-none fw-bold d-inline-flex align-items-center gap-1 text-nowrap"
                >
                    <i class="bi bi-x-circle"></i> Limpar filtros
                </button>
            </div>
        @endif
    </div>

    {{-- Grid de Quadras --}}
    <div class="row g-4">
        @forelse ($this->quadras as $quadra)
            <div class="col-md-6 {{ $quadraSelecionada === $quadra->id ? 'col-md-12' : '' }}" wire:key="quadra-{{ $quadra->id }}">
                <div class="card card-quadra h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="row g-0 h-100 quadra-listagem-row">
                        @php $imagensQuadra = collect($quadra->listaImagens())->map(fn ($img) => asset($img))->all(); @endphp
                        <div class="col-5 position-relative quadra-listagem-img-wrap" x-data="{ imagens: @js($imagensQuadra), indice: 0 }">
                            <img :src="imagens[indice]" alt="{{ $quadra->nome }}">
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
                        <div class="col-7 p-3">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <h6 class="text-orange fw-bold mb-0 quadra-listagem-titulo">{{ $quadra->nome }}</h6>
                                <div class="text-end flex-shrink-0">
                                    <span class="fw-bold quadra-listagem-preco">R$ {{ number_format($quadra->valor_hora, 2, ',', '.') }}</span><br>
                                    <span class="badge bg-light-green text-success quadra-listagem-badge">Valor Hora</span>
                                </div>
                            </div>

                            <div class="mt-2 quadra-listagem-texto-sec">
                                <p class="mb-1 text-orange fw-bold">Descrição</p>
                                <p class="text-muted mb-2">
                                    {{ $quadra->esporte->label() }} | {{ $quadra->cobertura ? 'Coberta' : 'Descoberta' }}
                                    @if ($quadra->descricao)
                                        | {{ $quadra->descricao }}
                                    @endif
                                </p>
                            </div>

                            <div class="mt-3">
                                <p class="text-muted quadra-listagem-texto-sec {{ ($quadra->distanciaKm ?? null) ? 'mb-1' : 'mb-3' }}"><i class="bi bi-geo-alt"></i> {{ $quadra->endereco }} - {{ $quadra->bairro }}, {{ $quadra->cidade }}</p>
                                @if ($quadra->distanciaKm ?? null)
                                    <p class="text-danger quadra-listagem-texto-sec mb-3"><i class="bi bi-signpost-2"></i> {{ $quadra->distanciaKm }} km de distância de você</p>
                                @endif

                                @if ($quadraSelecionada === $quadra->id)
                                    <div class="border-top pt-3">
                                        <div class="mb-2">
                                            <label class="small fw-bold">Data</label>
                                            <input type="date" class="form-control form-control-sm border-orange" wire:model="data" min="{{ now()->toDateString() }}">
                                            @error('data') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="small fw-bold">Horário</label>
                                            <select class="form-select form-select-sm border-orange" wire:model="horaInicio">
                                                <option value="">Selecione</option>
                                                @foreach ($this->horariosDisponiveis() as $horario)
                                                    <option value="{{ $horario }}">{{ $horario }}</option>
                                                @endforeach
                                            </select>
                                            @error('horaInicio') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-orange-action btn-sm px-4 rounded-2 fw-bold" wire:click="reservar" wire:loading.attr="disabled">
                                                Confirmar Reserva
                                            </button>
                                            <button class="btn btn-outline-secondary btn-sm rounded-2" wire:click="cancelarSelecao">
                                                Cancelar
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    @guest
                                        <button class="btn btn-orange-action btn-sm px-4 rounded-2 fw-bold" wire:click="$dispatch('login-necessario', { mensagem: 'Você precisa entrar para reservar uma quadra.' })">
                                            Agendar
                                        </button>
                                    @else
                                        <button class="btn btn-orange-action btn-sm px-4 rounded-2 fw-bold" wire:click="selecionarQuadra({{ $quadra->id }})">
                                            Agendar
                                        </button>
                                    @endguest
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted text-center py-5">Nenhuma quadra encontrada com esses filtros.</p>
            </div>
        @endforelse
    </div>
</div>
