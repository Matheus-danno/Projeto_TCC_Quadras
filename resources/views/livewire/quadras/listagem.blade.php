<div class="courts-browser">
    @if ($quadraSelecionada)
        @php
            $quadraAgendamento = $this->quadras->firstWhere('id', $quadraSelecionada);
            $meses = [
                1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
            ];
            $inicioMes = now()->startOfMonth();
            $diasNoMes = $inicioMes->daysInMonth;
            $espacosAntes = $inicioMes->dayOfWeek;
            $dataSelecionada = $data ? \Carbon\Carbon::parse($data) : null;
            $horariosBackend = $this->horariosDisponiveis();
            $horariosVisuais = collect(range(8, 21))
                ->flatMap(fn (int $hora) => [sprintf('%02d:00', $hora), sprintf('%02d:30', $hora)])
                ->values();
        @endphp

        @if ($quadraAgendamento)
            <section class="scheduler" aria-labelledby="scheduler-title">
                <div class="scheduler__topbar">
                    <button type="button" class="scheduler__back" wire:click="cancelarSelecao" aria-label="Voltar para todas as quadras">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <h1 id="scheduler-title">Agendar horário</h1>
                </div>

                <div class="scheduler-court">
                    <strong>{{ $quadraAgendamento->nome }}</strong>
                    <span>{{ $quadraAgendamento->esporte->label() }} · {{ $quadraAgendamento->endereco }} - {{ $quadraAgendamento->bairro }}, {{ $quadraAgendamento->cidade }}</span>
                    <span class="scheduler-court__price">R$ {{ number_format($quadraAgendamento->valor_hora, 2, ',', '.') }} / hora</span>
                </div>

                <div class="scheduler-calendar" aria-label="Calendário de agendamento">
                    <div class="scheduler-calendar__header">
                        <button type="button" aria-label="Mês anterior" disabled><i class="bi bi-chevron-left"></i></button>
                        <strong>{{ $meses[$inicioMes->month] }} {{ $inicioMes->year }}</strong>
                        <button type="button" aria-label="Próximo mês" disabled><i class="bi bi-chevron-right"></i></button>
                    </div>

                    <div class="scheduler-calendar__weekdays" aria-hidden="true">
                        <span>D</span><span>S</span><span>T</span><span>Q</span><span>Q</span><span>S</span><span>S</span>
                    </div>

                    <div class="scheduler-calendar__days">
                        @for ($i = 0; $i < $espacosAntes; $i++)
                            <span class="scheduler-day scheduler-day--empty" aria-hidden="true"></span>
                        @endfor

                        @for ($dia = 1; $dia <= $diasNoMes; $dia++)
                            @php
                                $dataDia = $inicioMes->copy()->day($dia);
                                $valorData = $dataDia->toDateString();
                                $passado = $dataDia->isBefore(now()->startOfDay());
                                $selecionado = $data === $valorData;
                            @endphp
                            <button
                                type="button"
                                class="scheduler-day {{ $selecionado ? 'is-selected' : '' }} {{ $passado ? 'is-unavailable' : '' }}"
                                @if (!$passado) wire:click="$set('data', '{{ $valorData }}')" @else disabled @endif
                                aria-label="{{ $dia }} de {{ $meses[$inicioMes->month] }}"
                                aria-pressed="{{ $selecionado ? 'true' : 'false' }}"
                            >
                                {{ $dia }}
                            </button>
                        @endfor
                    </div>
                </div>

                <div class="scheduler-section">
                    <div class="scheduler-section__heading">
                        <div>
                            <strong>Escolha o horário de início</strong>
                        </div>
                    </div>

                    <div class="scheduler-times" role="group" aria-label="Horários disponíveis">
                        @foreach ($horariosVisuais as $horario)
                            @php($suportadoPeloBackend = in_array($horario, $horariosBackend, true))
                            <button
                                type="button"
                                class="scheduler-time {{ $horaInicio === $horario ? 'is-selected' : '' }} {{ !$suportadoPeloBackend ? 'is-unavailable' : '' }}"
                                @if ($suportadoPeloBackend) wire:click="$set('horaInicio', '{{ $horario }}')" @else disabled @endif
                                aria-pressed="{{ $horaInicio === $horario ? 'true' : 'false' }}"
                            >
                                {{ $horario }}
                            </button>
                        @endforeach
                    </div>

                    <div class="scheduler-legend">
                        <span><i class="scheduler-legend__dot scheduler-legend__dot--free"></i>Livre</span>
                        <span><i class="scheduler-legend__dot scheduler-legend__dot--selected"></i>Selecionado</span>
                        <span><i class="scheduler-legend__dot scheduler-legend__dot--occupied"></i>Ocupado</span>
                    </div>
                </div>

                <div class="scheduler-duration">
                    <div>
                        <strong>Quanto tempo você quer jogar?</strong>
                        <span>Ajuste de 30 em 30 minutos.</span>
                    </div>
                    <div class="scheduler-duration__control" aria-label="Duração da reserva">
                        <button type="button" disabled aria-label="Diminuir duração">−</button>
                        <strong>1h</strong>
                        <button type="button" disabled aria-label="Aumentar duração">+</button>
                    </div>
                    <small>A reserva atual do sistema permanece limitada a 1 hora.</small>
                </div>

                @error('data')
                    <div class="scheduler-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                @enderror
                @error('horaInicio')
                    <div class="scheduler-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                @enderror

                <div class="scheduler-summary">
                    <i class="bi bi-check-circle"></i>
                    <div>
                        <strong>Você selecionou:</strong>
                        <span>
                            @if ($dataSelecionada && $horaInicio)
                                {{ $dataSelecionada->format('d/m/Y') }}, {{ $horaInicio }} - {{ date('H:i', strtotime($horaInicio . ' +1 hour')) }} · 1h · R$ {{ number_format($quadraAgendamento->valor_hora, 2, ',', '.') }}
                            @elseif ($dataSelecionada)
                                {{ $dataSelecionada->format('d/m/Y') }} · escolha um horário
                            @else
                                escolha uma data e um horário
                            @endif
                        </span>
                    </div>
                </div>

                @guest
                    <a href="{{ route('login') }}" class="scheduler-submit scheduler-submit--link">
                        Entrar para continuar
                    </a>
                @else
                    <button
                        type="button"
                        class="scheduler-submit"
                        wire:click="reservar"
                        wire:loading.attr="disabled"
                        @disabled(!$data || !$horaInicio)
                    >
                        <span wire:loading.remove wire:target="reservar">Confirmar agendamento</span>
                        <span wire:loading wire:target="reservar">Confirmando...</span>
                    </button>
                @endguest
            </section>
        @else
            <div class="courts-empty">
                <i class="bi bi-exclamation-circle"></i>
                <strong>Quadra indisponível</strong>
                <button type="button" class="court-button" wire:click="cancelarSelecao">Voltar</button>
            </div>
        @endif
    @else
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

                        <div class="court-card__actions">
                            <label class="court-hours">
                                <span>Qtd. de Horas:</span>
                                <select aria-label="Quantidade de horas" disabled title="A duração atual da reserva é de 1 hora">
                                    <option>1</option>
                                </select>
                            </label>
                            <button class="court-button" type="button" wire:click="selecionarQuadra({{ $quadra->id }})">
                                Agendar
                            </button>
                        </div>
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
    @endif
</div>
