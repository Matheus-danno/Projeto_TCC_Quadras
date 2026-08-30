@php
    $esporteAtual = $esporte ? \App\Enums\Esporte::from($esporte) : null;
    $visual = [
        'futebol' => ['icone' => 'bi-dribbble', 'gradiente' => 'linear-gradient(135deg, #ff9a3c, #ff7d14)', 'cor' => '#ff7d14'],
        'futsal' => ['icone' => 'bi-circle-fill', 'gradiente' => 'linear-gradient(135deg, #2d6a4f, #1b4332)', 'cor' => '#008a61'],
        'volei' => ['icone' => 'bi-dribbble', 'gradiente' => 'linear-gradient(135deg, #4c9ce2, #2f7dc4)', 'cor' => '#34b6e8'],
        'basquete' => ['icone' => 'bi-circle-fill', 'gradiente' => 'linear-gradient(135deg, #f77f00, #d62828)', 'cor' => '#f77f00'],
        'tenis' => ['icone' => 'bi-record-circle', 'gradiente' => 'linear-gradient(135deg, #a7c957, #6a994e)', 'cor' => '#97aa00'],
        'beach_tennis' => ['icone' => 'bi-record-circle', 'gradiente' => 'linear-gradient(135deg, #06d6a0, #00b4d8)', 'cor' => '#01bda5'],
    ];
@endphp

<div class="container my-5 criar-sala-container">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="titulo-principal-laranja mb-0">Criar Nova Partida</h3>
        <a href="{{ route('encontre_time') }}" class="text-orange fw-bold text-decoration-none">
            <i class="bi bi-x-lg me-1"></i> Cancelar
        </a>
    </div>

    @guest
        <div class="alert alert-warning">
            Você precisa <a href="{{ route('login') }}">entrar</a> para criar uma partida.
        </div>
    @else
        <div>
            {{-- informações básicas --}}
            <h5 class="titulo-secao mt-0">informações básicas</h5>

            <span class="subtitulo-campo">Nome da sala</span>
            <input type="text" class="form-control border-secondary-subtle fw-semibold text-secondary" wire:model="nome" placeholder="Ex: Racha de sexta-feira">
            @error('nome') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

            <span class="subtitulo-campo mt-4 d-block">Escolha o esporte</span>
            <div class="row g-3 mb-2 text-center">
                @foreach ($esportes as $opcao)
                    <div class="col">
                        <div
                            class="esporte-card {{ $esporte === $opcao->value ? 'active' : '' }}"
                            style="{{ $esporte === $opcao->value ? 'border-color: '.$visual[$opcao->value]['cor'] : '' }}"
                            wire:click="$set('esporte', '{{ $opcao->value }}')"
                        >
                            <div class="esporte-img-box" style="background: {{ $visual[$opcao->value]['gradiente'] }};">
                                <i class="bi {{ $visual[$opcao->value]['icone'] }}"></i>
                            </div>
                            <span class="fw-bold text-secondary small">{{ $opcao->label() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            @error('esporte') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <span class="subtitulo-campo">Data</span>
                    <input type="date" class="form-control border-secondary-subtle fw-semibold text-secondary" wire:model="data" min="{{ now()->toDateString() }}">
                    @error('data') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <span class="subtitulo-campo">Hora</span>
                    <select class="form-select border-secondary-subtle fw-semibold text-secondary" wire:model="horaInicio">
                        <option value="">Selecione</option>
                        @foreach ($this->horariosDisponiveis() as $horario)
                            <option value="{{ $horario }}">{{ $horario }}</option>
                        @endforeach
                    </select>
                    @error('horaInicio') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <span class="subtitulo-campo">Duração</span>
                    <select class="form-select border-secondary-subtle fw-semibold text-secondary" wire:model="duracaoMinutos">
                        @foreach ($this->duracoesDisponiveis() as $minutos => $rotulo)
                            <option value="{{ $minutos }}">{{ $rotulo }}</option>
                        @endforeach
                    </select>
                    @error('duracaoMinutos') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Número de Jogadores --}}
            <h5 class="titulo-secao">Número de Jogadores</h5>

            <div class="row g-3 align-items-stretch">
                <div class="col-md-3">
                    <div class="caixa-destaque caixa-branca text-center p-2">
                        <label class="subtitulo-campo font-size-sm">Total Jogadores</label>
                        <div class="qty-grupo">
                            <button type="button" class="qty-btn" wire:click="decrementarTotalJogadores">-</button>
                            <input type="text" class="qty-input" value="{{ $totalJogadores }}" readonly>
                            <button type="button" class="qty-btn" wire:click="incrementarTotalJogadores">+</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="caixa-destaque caixa-branca text-center p-2">
                        <label class="subtitulo-campo font-size-sm">Vagas mínimas p/ abrir</label>
                        <div class="qty-grupo">
                            <button type="button" class="qty-btn" wire:click="decrementarVagas">-</button>
                            <input type="text" class="qty-input" value="{{ $maxParticipantes }}" readonly>
                            <button type="button" class="qty-btn" wire:click="incrementarVagas">+</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="caixa-destaque caixa-verde text-center" wire:click="aplicarFormato('recomendado')" style="cursor: pointer;">
                        <span class="caixa-verde-texto fw-bold small">Recomendado para o {{ $esporteAtual?->label() ?? 'esporte' }}</span>
                        @if ($esporteAtual)
                            <span class="caixa-verde-texto fw-bold fs-5">{{ $esporteAtual->formatoRecomendado()['jogadores'] }} jogadores ({{ $esporteAtual->formatoRecomendado()['descricao'] }})</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="caixa-destaque caixa-laranja text-center" wire:click="aplicarFormato('alternativo')" style="cursor: pointer;">
                        <span class="caixa-laranja-texto fw-bold small">Formato alternativo</span>
                        @if ($esporteAtual)
                            <span class="caixa-laranja-texto fw-bold fs-5">{{ $esporteAtual->formatoAlternativo()['jogadores'] }} jogadores ({{ $esporteAtual->formatoAlternativo()['descricao'] }})</span>
                        @endif
                    </div>
                </div>
            </div>
            @error('maxParticipantes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

            {{-- Nível de habilidade --}}
            <h5 class="titulo-secao">Nível de habilidade</h5>

            <div class="row g-3">
                <div class="col-md-5">
                    <span class="subtitulo-campo">Nível desejado</span>
                    <select class="form-select border-secondary-subtle fw-semibold text-secondary" wire:model="nivelDesejado">
                        <option value="">Selecione</option>
                        @foreach ($niveis as $opcao)
                            <option value="{{ $opcao->value }}">{{ $opcao->label() }}</option>
                        @endforeach
                    </select>
                    @error('nivelDesejado') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-7">
                    <span class="subtitulo-campo">Aceitar níveis adjacentes</span>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($aceitacoes as $opcao)
                            <div class="radio-card radio-pill {{ $aceitacaoNiveis === $opcao->value ? 'active' : '' }}" wire:click="$set('aceitacaoNiveis', '{{ $opcao->value }}')">
                                <span class="circulo-check"></span>
                                <span class="fw-semibold small text-secondary">{{ $opcao->label() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Escolha a quadra --}}
            <h5 class="titulo-secao">Escolha a quadra</h5>

            <div class="input-group mb-3">
                <span class="input-group-text bg-white border-orange"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-orange" wire:model.live.debounce.300ms="buscaQuadra" placeholder="Pesquisar quadra">
                <button
                    type="button"
                    class="btn btn-outline-laranja"
                    x-data
                    x-on:click="navigator.geolocation.getCurrentPosition((p) => $wire.usarLocalizacao(p.coords.latitude, p.coords.longitude))"
                >
                    <i class="bi bi-geo-alt"></i> Perto de mim
                </button>
            </div>
            @error('quadraId') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

            <div class="row row-cols-1 row-cols-md-2 g-3" style="max-height: 700px; overflow-y: auto;">
                @forelse ($this->quadras as $quadra)
                    <div class="col" wire:key="quadra-{{ $quadra->id }}">
                        <div class="quadra-card {{ $quadraId === $quadra->id ? 'active' : '' }} h-100">
                            <div class="d-flex gap-3 p-3">
                                <img
                                    src="{{ $quadra->fotoCapa()?->url() ?? asset('imagens/tela_inicial/quadra_volei2.png') }}"
                                    alt="{{ $quadra->nome }}"
                                    class="rounded-3 flex-shrink-0"
                                    style="width: 130px; height: 100px; object-fit: cover;"
                                >
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="fw-bold text-orange mb-0">{{ $quadra->nome }}</h6>
                                        <div class="text-end">
                                            <span class="fw-bold">R$ {{ number_format($quadra->valor_hora, 2, ',', '.') }}</span>
                                            <span class="badge bg-light-green text-success d-block">Valor Hora</span>
                                        </div>
                                    </div>

                                    <p class="text-muted small mb-1 mt-1">
                                        <i class="bi bi-geo-alt"></i> {{ $quadra->endereco }} - {{ $quadra->bairro }}
                                    </p>

                                    <p class="small mb-2">
                                        <span class="fw-bold text-orange">Descrição</span>
                                        {{ $quadra->esporte->label() }} | {{ $quadra->cobertura ? 'Coberta' : 'Descoberta' }}
                                        @if ($quadra->descricao)
                                            | {{ $quadra->descricao }}
                                        @endif
                                    </p>

                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            @if (isset($quadra->distanciaKm) && $quadra->distanciaKm !== null)
                                                <span class="small text-muted"><i class="bi bi-geo"></i> {{ $quadra->distanciaKm }} km</span>
                                            @endif
                                        </div>

                                        @if ($quadra->indisponivel)
                                            <span class="badge-indisponivel">Horário Indisponível</span>
                                        @elseif ($quadraId === $quadra->id)
                                            <button type="button" class="btn btn-laranja btn-sm rounded-pill px-3 fw-bold" wire:click="selecionarQuadra({{ $quadra->id }})">Selecionado</button>
                                        @else
                                            <button type="button" class="btn btn-outline-laranja btn-sm rounded-pill px-3 fw-bold" wire:click="selecionarQuadra({{ $quadra->id }})">Selecionar</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted text-center py-4">Nenhuma quadra encontrada.</p>
                    </div>
                @endforelse
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <div class="caixa-destaque caixa-branca">
                        <span class="subtitulo-campo mb-1">Valor por pessoa</span>
                        <span class="text-orange fw-bold fs-3">{{ $this->resumoPartida['valorPorPessoa'] }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="caixa-destaque caixa-laranja">
                        <span class="caixa-laranja-texto subtitulo-campo mb-1">Total a arrecadar</span>
                        <span class="caixa-laranja-texto fw-bold fs-3">{{ $this->resumoPartida['valorTotal'] }}</span>
                        <span class="text-muted small">({{ $maxParticipantes }} jogadores x {{ $this->resumoPartida['valorPorPessoa'] }})</span>
                    </div>
                </div>
            </div>
            <p class="text-muted small fst-italic mt-1">Valores são apenas uma prévia de preço, sem cobrança real.</p>

            {{-- Configurações da sala --}}
            <h5 class="titulo-secao">Configurações da sala</h5>

            <span class="subtitulo-campo">Privacidade da Sala</span>
            <div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
                @foreach ($privacidades as $opcao)
                    <div class="col">
                        <div class="radio-card {{ $privacidade === $opcao->value ? 'active' : '' }}" wire:click="$set('privacidade', '{{ $opcao->value }}')">
                            <span class="circulo-check"></span>
                            <div>
                                <div class="fw-bold text-secondary">{{ $opcao->label() }}</div>
                                <div class="small text-muted">{{ $opcao->descricao() }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <span class="subtitulo-campo">Aprovação de membros</span>
            <div class="row row-cols-1 row-cols-md-2 g-3">
                @foreach ($aprovacoes as $opcao)
                    <div class="col">
                        <div class="radio-card {{ $aprovacao === $opcao->value ? 'active' : '' }}" wire:click="$set('aprovacao', '{{ $opcao->value }}')">
                            <span class="circulo-check"></span>
                            <div>
                                <div class="fw-bold text-secondary">{{ $opcao->label() }}</div>
                                <div class="small text-muted">{{ $opcao->descricao() }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <span class="subtitulo-campo mt-4 d-block">Regras adicionais</span>
            <textarea class="form-control border-secondary-subtle" wire:model="regrasAdicionais" rows="3" placeholder="Adicione informações extras: tipo de bola, uniforme sugerido, regras específicas."></textarea>
            @error('regrasAdicionais') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

            {{-- Resumo da partida --}}
            <h5 class="titulo-secao">Resumo da partida</h5>

            <div class="caixa-resumo rounded-3">
                <p class="fw-bold text-secondary mb-3">{{ $this->resumoPartida['esporteNivel'] }}</p>
                <div class="d-flex flex-column gap-2 small text-secondary">
                    <div>{{ $this->resumoPartida['quadraEndereco'] }}</div>
                    <div>{{ $this->resumoPartida['dataHora'] }}</div>
                    <div>{{ $this->resumoPartida['jogadores'] }}</div>
                    <div>{{ $this->resumoPartida['nivelAceitacao'] }}</div>
                    {{-- "Times balanceados" é apenas texto informativo, não há algoritmo de balanceamento implementado --}}
                    <div>Times balanceados automaticamente</div>
                    <div>{{ $this->resumoPartida['privacidadeAprovacao'] }}</div>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span class="text-secondary">{{ $this->resumoPartida['valorTotal'] }}</span>
                    <span class="text-orange">{{ $this->resumoPartida['valorPorPessoa'] }}</span>
                </div>
            </div>

            @if ($errors->any())
                @php
                    $rotulosCampos = [
                        'nome' => 'Nome da sala',
                        'esporte' => 'Esporte',
                        'quadraId' => 'Quadra (seção "Escolha a quadra")',
                        'data' => 'Data',
                        'horaInicio' => 'Hora',
                        'duracaoMinutos' => 'Duração',
                        'totalJogadores' => 'Total Jogadores',
                        'maxParticipantes' => 'Vagas mínimas p/ abrir',
                        'nivelDesejado' => 'Nível desejado',
                        'aceitacaoNiveis' => 'Aceitar níveis adjacentes',
                        'privacidade' => 'Privacidade da sala',
                        'aprovacao' => 'Aprovação de membros',
                        'regrasAdicionais' => 'Regras adicionais',
                    ];
                @endphp
                <div class="alert alert-danger mt-4">
                    <strong>Corrija os campos abaixo antes de criar a partida:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->keys() as $campo)
                            <li>{{ $rotulosCampos[$campo] ?? $campo }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-3 mt-4 mb-4">
                <div class="col-md-6">
                    <a href="{{ route('encontre_time') }}" class="btn btn-voltar-laranja w-100 py-3 fw-bold fs-5 shadow-sm">Voltar</a>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-criar-laranja w-100 py-3 fw-bold fs-5 shadow-sm" wire:click="criar" wire:loading.attr="disabled">
                        Criar Partida
                    </button>
                </div>
            </div>

            {{-- Texto mantido igual ao design do Figma (copy estático); pagamento e expiração automática não são implementados de fato --}}
            <div class="caixa-importante mb-5">
                <h6 class="fw-bold mb-2">Importante</h6>
                <ul class="mb-0 small">
                    <li>Você será o administrador desta sala</li>
                    <li>Sua vaga será confirmada após o pagamento de R$ 5,00</li>
                    <li>A sala ficará aberta por 30 minutos. Caso não atinja 80% do número de jogadores solicitados, o agendamento da quadra não será realizado, e o valor pago será automaticamente estornado.</li>
                </ul>
            </div>
        </div>
    @endguest
</div>
