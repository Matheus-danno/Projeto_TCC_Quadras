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
    </div>

    @if ($mensagemSucesso)
        <div class="alert alert-success" role="alert">
            {{ $mensagemSucesso }}
        </div>
    @endif

    {{-- Grid de Quadras --}}
    <div class="row g-4">
        @forelse ($this->quadras as $quadra)
            <div class="col-md-6" wire:key="quadra-{{ $quadra->id }}">
                <div class="card card-quadra h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="row g-0">
                        <div class="col-5 position-relative">
                            <img src="{{ asset('imagens/tela_inicial/quadra_volei2.png') }}" class="img-fluid h-100 object-fit-cover" alt="{{ $quadra->nome }}">
                        </div>
                        <div class="col-7 p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="text-orange fw-bold">{{ $quadra->nome }}</h5>
                                <div class="text-end">
                                    <span class="fs-4 fw-bold">R$ {{ number_format($quadra->valor_hora, 2, ',', '.') }}</span><br>
                                    <span class="badge bg-light-green text-success">Valor Hora</span>
                                </div>
                            </div>

                            <div class="mt-2 small">
                                <p class="mb-1 text-orange fw-bold">Descrição</p>
                                <p class="text-muted mb-2">
                                    {{ $quadra->esporte->label() }} | {{ $quadra->cobertura ? 'Coberta' : 'Descoberta' }}
                                    @if ($quadra->descricao)
                                        | {{ $quadra->descricao }}
                                    @endif
                                </p>
                            </div>

                            <div class="mt-3">
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
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted text-center py-5">Nenhuma quadra encontrada com esses filtros.</p>
            </div>
        @endforelse
    </div>
</div>
