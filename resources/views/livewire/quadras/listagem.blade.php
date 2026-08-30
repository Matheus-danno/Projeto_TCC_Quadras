<div class="courts-browser">
    <section class="court-filters" aria-label="Filtros de quadras">
        <span class="court-filters__label">FILTROS</span>

        <div class="court-filter-control">
            <select wire:model.live="cidade" aria-label="Filtrar por cidade">
                <option value="">Cidade</option>
                @foreach ($this->cidades as $cidade)
                    <option value="{{ $cidade }}">{{ $cidade }}</option>
                @endforeach
            </select>
        </div>

        <div class="court-filter-control">
            <select wire:model.live="bairro" aria-label="Filtrar por bairro">
                <option value="">Bairro</option>
                @foreach ($this->bairros as $bairro)
                    <option value="{{ $bairro }}">{{ $bairro }}</option>
                @endforeach
            </select>
        </div>

        <div class="court-filter-control">
            <select wire:model.live="esporte" aria-label="Filtrar por esporte">
                <option value="">Esporte</option>
                @foreach ($esportes as $opcao)
                    <option value="{{ $opcao->value }}">{{ $opcao->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="court-filter-control court-filter-control--presentation" title="Filtro visual preparado para integração">
            <select aria-label="Filtrar por quadra">
                <option>Quadra</option>
            </select>
        </div>

        <div class="court-filter-control court-filter-control--presentation" title="Filtro visual preparado para integração">
            <select aria-label="Filtrar por cobertura">
                <option>Cobertura</option>
            </select>
        </div>
    </section>

    @if ($mensagemSucesso)
        <div class="court-feedback" role="status">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ $mensagemSucesso }}</span>
        </div>
    @endif

    <section class="courts-grid" aria-live="polite">
        @forelse ($this->quadras as $quadra)
            @php
                $imagem = match ($quadra->esporte->value) {
                    'tenis' => 'quadra_tenis.png',
                    'volei', 'beach_tennis' => 'quadra_volei2.png',
                    default => 'quadra_futebol.png',
                };
            @endphp

            <article class="court-card" wire:key="quadra-{{ $quadra->id }}">
                <div class="court-card__media">
                    <img src="{{ asset('imagens/tela_inicial/' . $imagem) }}" alt="{{ $quadra->nome }}">
                    <button type="button" class="court-card__arrow court-card__arrow--left" aria-label="Imagem anterior">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="court-card__arrow court-card__arrow--right" aria-label="Próxima imagem">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>

                <div class="court-card__body">
                    <div class="court-card__headline">
                        <h2>{{ $quadra->nome }}</h2>
                        <div class="court-card__price">
                            <strong>R$ {{ number_format($quadra->valor_hora, 2, ',', '.') }}</strong>
                            <span>Valor Hora</span>
                        </div>
                    </div>

                    <div class="court-card__description">
                        <div class="court-card__description-title">
                            <span>Descrição</span>
                            <i class="bi bi-wind" aria-hidden="true"></i>
                        </div>
                        <p>
                            {{ $quadra->esporte->label() }}
                            <span>|</span>
                            {{ $quadra->cobertura ? 'Coberta' : 'Descoberta' }}
                            @if ($quadra->descricao)
                                <span>|</span> {{ $quadra->descricao }}
                            @endif
                        </p>
                    </div>

                    <div class="court-card__meta">
                        <strong>5 km de distância de você</strong>
                        <span><i class="bi bi-geo-alt-fill"></i> {{ $quadra->endereco }} - {{ $quadra->bairro }}, {{ $quadra->cidade }}</span>
                    </div>

                    @if ($quadraSelecionada === $quadra->id)
                        <div class="court-card__booking">
                            @guest
                                <p class="court-card__login-note">
                                    Você precisa <a href="{{ route('login') }}">entrar</a> para reservar.
                                </p>
                                <button type="button" class="court-button court-button--secondary" wire:click="cancelarSelecao">Fechar</button>
                            @else
                                <div class="court-booking-fields">
                                    <label>
                                        <span>Data</span>
                                        <input type="date" wire:model="data" min="{{ now()->toDateString() }}">
                                        @error('data') <small>{{ $message }}</small> @enderror
                                    </label>
                                    <label>
                                        <span>Horário</span>
                                        <select wire:model="horaInicio">
                                            <option value="">Selecione</option>
                                            @foreach ($this->horariosDisponiveis() as $horario)
                                                <option value="{{ $horario }}">{{ $horario }}</option>
                                            @endforeach
                                        </select>
                                        @error('horaInicio') <small>{{ $message }}</small> @enderror
                                    </label>
                                </div>
                                <div class="court-booking-actions">
                                    <button class="court-button court-button--secondary" type="button" wire:click="cancelarSelecao">Cancelar</button>
                                    <button class="court-button" type="button" wire:click="reservar" wire:loading.attr="disabled">Confirmar</button>
                                </div>
                            @endguest
                        </div>
                    @else
                        <div class="court-card__actions">
                            <label class="court-hours">
                                <span>Qtd. de Horas:</span>
                                <select aria-label="Quantidade de horas">
                                    <option>1</option>
                                </select>
                            </label>
                            <button class="court-button" type="button" wire:click="selecionarQuadra({{ $quadra->id }})">
                                Agendar
                            </button>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="courts-empty">
                <i class="bi bi-search"></i>
                <strong>Nenhuma quadra encontrada</strong>
                <span>Tente ajustar os filtros para visualizar outras opções.</span>
            </div>
        @endforelse
    </section>
</div>
