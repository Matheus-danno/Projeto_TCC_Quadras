<div>
    {{-- Seção de Filtros --}}
    <div class="row mb-4 align-items-center">
        <div class="col-auto"><strong>FILTROS</strong></div>
        <div class="col">
            <select class="form-select border-orange" wire:model.live="cidade">
                <option value="">Cidade</option>
                @foreach ($this->cidades as $cidade)
                    <option value="{{ $cidade }}">{{ $cidade }}</option>
                @endforeach
            </select>
        </div>
        <div class="col">
            <select class="form-select border-orange" wire:model.live="bairro">
                <option value="">Bairro</option>
                @foreach ($this->bairros as $bairro)
                    <option value="{{ $bairro }}">{{ $bairro }}</option>
                @endforeach
            </select>
        </div>
        <div class="col">
            <select class="form-select border-orange" wire:model.live="esporte">
                <option value="">Esporte</option>
                @foreach ($esportes as $opcao)
                    <option value="{{ $opcao->value }}">{{ $opcao->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col">
            <select class="form-select border-orange" wire:model.live="quadraNome">
                <option value="">Quadra</option>
                @foreach ($this->nomesQuadras as $nome)
                    <option value="{{ $nome }}">{{ $nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="col">
            <select class="form-select border-orange" wire:model.live="cobertura">
                <option value="">Cobertura</option>
                <option value="1">Coberta</option>
                <option value="0">Descoberta</option>
            </select>
        </div>
    </div>

    @if ($mensagemSucesso)
        <div class="alert alert-success" role="alert">
            {{ $mensagemSucesso }}
        </div>
    @endif

    {{-- Grid de Quadras --}}
    <div class="row g-4 gy-5">
        @forelse ($this->quadras as $quadra)
            <div class="col-md-6" wire:key="quadra-{{ $quadra->id }}">
                <div class="card card-quadra h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                    {{-- Carrossel de fotos --}}
                    <div id="carrossel-quadra-{{ $quadra->id }}" class="carousel slide">
                        <div class="carousel-inner">
                            @forelse ($quadra->fotos as $indice => $foto)
                                <div class="carousel-item {{ $indice === 0 ? 'active' : '' }}">
                                    <img src="{{ $foto->url() }}" class="d-block w-100" style="height: 220px; object-fit: cover;" alt="{{ $quadra->nome }}">
                                </div>
                            @empty
                                <div class="carousel-item active">
                                    <img src="{{ asset('imagens/tela_inicial/quadra_volei2.png') }}" class="d-block w-100" style="height: 220px; object-fit: cover;" alt="{{ $quadra->nome }}">
                                </div>
                            @endforelse
                        </div>

                        @if ($quadra->fotos->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#carrossel-quadra-{{ $quadra->id }}" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Foto anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carrossel-quadra-{{ $quadra->id }}" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Próxima foto</span>
                            </button>
                            <div class="carousel-indicators">
                                @foreach ($quadra->fotos as $indice => $foto)
                                    <button
                                        type="button"
                                        data-bs-target="#carrossel-quadra-{{ $quadra->id }}"
                                        data-bs-slide-to="{{ $indice }}"
                                        class="{{ $indice === 0 ? 'active' : '' }}"
                                        aria-label="Foto {{ $indice + 1 }}"
                                    ></button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="fw-bold mb-1">{{ $quadra->nome }}</h5>
                            <div class="text-end">
                                <span class="fs-5 fw-bold">R$ {{ number_format($quadra->valor_hora, 2, ',', '.') }}</span><br>
                                <span class="badge bg-light-green text-success">Valor Hora</span>
                            </div>
                        </div>

                        <div class="border rounded-3 p-2 mt-2 mb-2 position-relative">
                            <p class="fw-bold small mb-1">Descrição <i class="bi bi-eye text-muted position-absolute top-0 end-0 mt-2 me-2"></i></p>
                            <p class="text-muted small mb-0">
                                {{ $quadra->esporte->label() }} | {{ $quadra->cobertura ? 'Coberta' : 'Descoberta' }}
                                @if ($quadra->descricao)
                                    | {{ $quadra->descricao }}
                                @endif
                            </p>
                        </div>

                        <p class="text-muted small mb-3"><i class="bi bi-geo-alt"></i> {{ $quadra->endereco }} - {{ $quadra->bairro }}, {{ $quadra->cidade }}</p>

                        @if ($quadraSelecionada === $quadra->id)
                            <div class="border-top pt-3">
                                @guest
                                    <p class="small mb-2">Você precisa <a href="{{ route('login') }}">entrar</a> para reservar.</p>
                                @else
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
                                        <button class="btn btn-orange-action btn-sm px-4 rounded-pill text-white fw-bold" wire:click="reservar" wire:loading.attr="disabled">
                                            Confirmar Reserva
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm rounded-pill" wire:click="cancelarSelecao">
                                            Cancelar
                                        </button>
                                    </div>
                                @endguest
                            </div>
                        @else
                            <button class="btn btn-orange-action btn-sm px-4 rounded-pill text-white fw-bold" wire:click="selecionarQuadra({{ $quadra->id }})">
                                Agendar
                            </button>
                        @endif
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
