<div>
@guest
    <div class="container my-5 criar-sala-container">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4">
            <div class="card-body">
                <i class="bi bi-lock-fill text-orange" style="font-size: 2.5rem;"></i>
                <p class="fw-semibold mt-3 mb-4">Você precisa entrar para criar uma sala.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-orange-action fw-bold px-4">Entrar</a>
                    <a href="{{ route('registro') }}" class="btn btn-outline-secondary fw-bold px-4">Cadastrar-se</a>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="container my-4 criar-sala-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('encontre_time') }}" class="detalhes-link-topo text-decoration-none">
                <i class="bi bi-x-lg me-1"></i> Cancelar
            </a>
            <h5 class="fw-normal text-orange mb-0">Criar Nova Partida</h5>
            <span></span>
        </div>

        <div class="card border-0 shadow-sm card-arredondado mb-3">
            <div class="card-body p-4">
                <h5 class="fw-semibold texto-jogo mb-3">Informações básicas</h5>

                <p class="detalhes-subtitulo mb-2">Escolha o esporte</p>
                <div class="row g-3 mb-4">
                    @foreach ($esportes as $opcao)
                        <div class="col-6 col-md-2-4" style="flex: 0 0 20%; max-width: 20%;">
                            <div
                                class="esporte-card text-center {{ $esporte === $opcao->value ? 'active' : '' }}"
                                wire:click="$set('esporte', '{{ $opcao->value }}')"
                                style="cursor: pointer; border-color: {{ $esporte === $opcao->value ? $opcao->cor() : 'transparent' }};"
                            >
                                <div class="esporte-img-box" style="background: {{ $opcao->cor() }};">
                                    @if ($opcao->imagemBola())
                                        <img src="{{ asset($opcao->imagemBola()) }}" alt="{{ $opcao->label() }}">
                                    @else
                                        <i class="bi {{ $opcao->icone() }}"></i>
                                    @endif
                                </div>
                                <span class="fw-bold text-secondary small">{{ $opcao === \App\Enums\Esporte::Futebol ? 'Futebol Society' : $opcao->label() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('esporte') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="detalhes-subtitulo d-block mb-1">Data</label>
                        <input type="date" class="form-control detalhes-input" wire:model="data" value="{{ $data }}" min="{{ now()->toDateString() }}">
                        @error('data') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="detalhes-subtitulo d-block mb-1">Hora</label>
                        <select class="form-select detalhes-input" wire:model="horaInicio">
                            @foreach ($this->horariosDisponiveis() as $hora)
                                <option value="{{ $hora }}" @selected($hora === $horaInicio)>{{ $hora }}</option>
                            @endforeach
                        </select>
                        @error('horaInicio') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="detalhes-subtitulo d-block mb-1">Duração</label>
                        <select class="form-select detalhes-input" wire:model="duracaoMinutos">
                            @foreach ($this->duracoesDisponiveis() as $minutos)
                                <option value="{{ $minutos }}" @selected($minutos === $duracaoMinutos)>{{ $this->duracaoFormatada($minutos) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <p class="detalhes-subtitulo mb-2">Número de Jogadores</p>
                <div class="row g-3 align-items-stretch mb-4">
                    <div class="col-6 col-md-2">
                        <div class="caixa-destaque caixa-branca text-center h-100">
                            <label class="subtitulo-campo">Total Jogadores</label>
                            <div class="qty-grupo">
                                <button type="button" class="qty-btn" wire:click="decrementarTotalJogadores">-</button>
                                <input type="text" class="qty-input" value="{{ $totalJogadores }}" readonly>
                                <button type="button" class="qty-btn" wire:click="incrementarTotalJogadores">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="caixa-destaque caixa-branca text-center h-100">
                            <label class="subtitulo-campo">Total de Vagas</label>
                            <div class="qty-grupo">
                                <button type="button" class="qty-btn" wire:click="decrementarVagas">-</button>
                                <input type="text" class="qty-input" value="{{ $maxParticipantes }}" readonly>
                                <button type="button" class="qty-btn" wire:click="incrementarVagas">+</button>
                            </div>
                        </div>
                    </div>
                    @if ($esporte)
                        @php $esporteAtual = \App\Enums\Esporte::from($esporte); @endphp
                        <div class="col-12 col-md-4">
                            <button type="button" wire:click="selecionarFormato('recomendado')" class="caixa-destaque caixa-verde text-start h-100 w-100 border-0">
                                <span class="caixa-verde-texto texto-formato-label d-block">Recomendado para o {{ $esporteAtual->label() }}</span>
                                <span class="caixa-verde-texto texto-formato-valor fw-bold">{{ $esporteAtual->formatoRecomendado()['descricao'] }}</span>
                            </button>
                        </div>
                        <div class="col-12 col-md-4">
                            <button type="button" wire:click="selecionarFormato('alternativo')" class="caixa-destaque caixa-laranja text-start h-100 w-100 border-0">
                                <span class="caixa-laranja-texto texto-formato-label d-block">Formato alternativo</span>
                                <span class="caixa-laranja-texto texto-formato-valor fw-bold">{{ $esporteAtual->formatoAlternativo()['descricao'] }}</span>
                            </button>
                        </div>
                    @endif
                </div>
                @error('maxParticipantes') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

                <p class="detalhes-subtitulo mb-2">Nível de habilidade</p>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="subtitulo-campo">Nível desejado</label>
                        <select class="form-select detalhes-input select-nivel-desejado" wire:model="nivel">
                            @foreach ($niveis as $opcao)
                                <option value="{{ $opcao->value }}" @selected($opcao->value === $nivel)>{{ $opcao->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="subtitulo-campo">Aceitar níveis adjacentes</label>
                        <div class="pill-radio-grupo">
                            @foreach ($niveisFlexibilidade as $opcao)
                                <label class="pill-radio-opcao">
                                    <input type="radio" wire:model="nivelFlexibilidade" value="{{ $opcao->value }}" @checked($opcao->value === $nivelFlexibilidade)>
                                    <span class="pill-radio-dot"></span>
                                    <span class="pill-radio-label">{{ $opcao->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="fw-semibold texto-jogo mb-3">Escolha a quadra</h5>
        <div class="card border-0 shadow-sm card-arredondado mb-3" x-data x-init="navigator.geolocation && navigator.geolocation.getCurrentPosition((posicao) => $wire.usarLocalizacao(posicao.coords.latitude, posicao.coords.longitude), () => {})">
            <div class="card-body p-4">
                <div class="input-group mb-4">
                    <span class="input-group-text bg-white border-orange"><i class="bi bi-search text-orange"></i></span>
                    <input type="text" class="form-control border-orange" wire:model.live="buscaQuadra" placeholder="Pesquisar quadra">
                </div>

                @if ($quadraId && $this->quadraSelecionada)
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="caixa-destaque caixa-branca caixa-resumo-financeiro">
                                <label class="subtitulo-campo">Valor por pessoa</label>
                                <p class="fw-bold text-orange caixa-resumo-valor mb-0">R$ {{ number_format($this->precoPessoa() ?? 0, 2, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="caixa-destaque caixa-laranja caixa-resumo-financeiro">
                                <label class="subtitulo-campo">Total a arrecadar</label>
                                <p class="fw-bold text-orange caixa-resumo-valor mb-0">R$ {{ number_format($this->totalArrecadar() ?? 0, 2, ',', '.') }}</p>
                                <span class="small text-muted">({{ $maxParticipantes }} jogadores x {{ number_format($this->precoPessoa() ?? 0, 2, ',', '.') }})</span>
                            </div>
                        </div>
                    </div>
                @endif

                @if (! $esporte)
                    <p class="text-muted mb-0">Escolha um esporte acima para ver as quadras disponíveis.</p>
                @elseif ($this->quadras->isEmpty())
                    <p class="text-muted mb-0">Nenhuma quadra encontrada.</p>
                @else
                    <div class="quadras-scroll-area">
                    <div class="row g-3">
                        @foreach ($this->quadras as $quadra)
                            <div class="col-12" wire:key="quadra-{{ $quadra->id }}">
                                <div class="card card-quadra shadow-sm border-0 rounded-4 overflow-hidden {{ $quadraId === $quadra->id ? 'quadra-selecionada' : '' }} {{ ! $quadra->disponivel ? 'opacity-50' : '' }}">
                                    <div class="d-flex quadra-card-row">
                                        @php $imagensQuadra = collect($quadra->listaImagens())->map(fn ($img) => asset($img))->all(); @endphp
                                        <div class="quadra-card-img-wrap flex-shrink-0 position-relative" x-data="{ imagens: @js($imagensQuadra), indice: 0 }">
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
                                        <div class="p-3 flex-grow-1 min-w-0">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <h6 class="text-orange fw-bold mb-0 quadra-card-titulo">{{ $quadra->nome }}</h6>
                                                <div class="text-end flex-shrink-0">
                                                    <span class="fw-bold quadra-card-preco">R$ {{ number_format($quadra->valor_hora, 2, ',', '.') }}</span><br>
                                                    <span class="badge bg-light-green text-success">Valor Hora</span>
                                                </div>
                                            </div>

                                            <div class="quadra-card-descricao-box mt-2 mb-1">
                                                <p class="mb-1 text-orange fw-bold small">Descrição</p>
                                                <p class="text-muted small mb-0 quadra-card-descricao">
                                                    {{ implode(' | ', $quadra->listaAmenidades()) }}
                                                </p>
                                            </div>

                                            <p class="text-muted small mb-1 quadra-card-descricao">
                                                <i class="bi bi-geo-alt quadra-card-icone"></i>{{ $quadra->endereco }} - {{ $quadra->bairro }}, {{ $quadra->cidade }}
                                            </p>
                                            @if ($quadra->distanciaKm ?? null)
                                                <p class="text-danger small mb-2 quadra-card-descricao quadra-card-distancia">{{ $quadra->distanciaKm }} km de distância de você</p>
                                            @endif

                                            <div class="text-end">
                                                @if (! $quadra->disponivel)
                                                    <span class="badge bg-danger-subtle text-danger fw-bold">Horário Indisponível</span>
                                                @elseif ($quadraId === $quadra->id)
                                                    <button type="button" class="btn btn-laranja fw-bold btn-ver-detalhes" wire:click="selecionarQuadra({{ $quadra->id }})">Selecionado</button>
                                                @else
                                                    <button type="button" class="btn btn-outline-laranja fw-bold btn-ver-detalhes" wire:click="selecionarQuadra({{ $quadra->id }})">Selecionar</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    </div>
                @endif
                @error('quadraId') <div class="text-danger small mt-3 mb-0">{{ $message }}</div> @enderror
            </div>
        </div>

        <h5 class="fw-semibold texto-jogo mb-3">Configurações da sala</h5>
        <div class="card border-0 shadow-sm card-arredondado mb-3">
            <div class="card-body p-4">
                <p class="detalhes-subtitulo mb-2">Privacidade da Sala</p>
                <div class="row g-3 mb-4">
                    @foreach (\App\Enums\Privacidade::cases() as $opcao)
                        <div class="col-md-6">
                            <div class="radio-card {{ $privacidade === $opcao->value ? 'active' : '' }}" wire:click="$set('privacidade', '{{ $opcao->value }}')" style="cursor: pointer;">
                                <span class="circulo-check {{ $privacidade === $opcao->value ? 'checked' : '' }}"></span>
                                <div>
                                    <p class="fw-bold mb-0">{{ $opcao->label() }}</p>
                                    <p class="text-muted small mb-0">{{ $opcao->descricao() }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="detalhes-subtitulo mb-2">Aprovação de membros</p>
                <div class="row g-3 mb-4">
                    @foreach (\App\Enums\Aprovacao::cases() as $opcao)
                        <div class="col-md-6">
                            <div class="radio-card {{ $aprovacao === $opcao->value ? 'active' : '' }}" wire:click="$set('aprovacao', '{{ $opcao->value }}')" style="cursor: pointer;">
                                <span class="circulo-check {{ $aprovacao === $opcao->value ? 'checked' : '' }}"></span>
                                <div>
                                    <p class="fw-bold mb-0">{{ $opcao->label() }}</p>
                                    <p class="text-muted small mb-0">{{ $opcao->descricao() }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <label class="detalhes-subtitulo d-block mb-1">Regras adicionais</label>
                <textarea class="form-control detalhes-input" rows="3" wire:model="regrasAdicionais" placeholder="Adicione informações extras: tipo de bola, uniforme sugerido, regras específicas."></textarea>
            </div>
        </div>

        @if ($esporte && $quadraId)
            <h5 class="fw-semibold texto-jogo mb-3">Resumo da partida</h5>
            <div class="card border-0 shadow-sm card-arredondado mb-3">
                <div class="card-body p-4">
                    <p class="fw-semibold texto-jogo mb-1">{{ \App\Enums\Esporte::from($esporte)->label() }} - {{ \App\Enums\NivelHabilidade::from($nivel)->label() }}</p>
                    <p class="text-muted mb-1">{{ $this->quadraSelecionada?->nome }} - {{ $this->quadraSelecionada?->endereco }}</p>
                    <p class="text-muted mb-1">
                        {{ \Illuminate\Support\Carbon::parse($data)->isToday() ? 'Hoje' : \Illuminate\Support\Carbon::parse($data)->format('d/m/Y') }},
                        {{ $horaInicio }} - {{ date('H:i', strtotime($horaInicio.' +'.$duracaoMinutos.' minutes')) }}
                        ({{ $this->duracaoFormatada($duracaoMinutos) }})
                    </p>
                    <p class="text-muted mb-3">
                        {{ $totalJogadores }} jogadores no total
                        @if ($maxParticipantes < $totalJogadores)
                            · {{ $maxParticipantes }} vagas abertas pelo app
                        @endif
                        · Nível: {{ \App\Enums\NivelHabilidade::from($nivel)->label() }}
                    </p>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <p class="fw-bold texto-jogo mb-0">Valor total: R$ {{ number_format($this->totalArrecadar() ?? 0, 2, ',', '.') }}</p>
                        <p class="fw-bold text-orange mb-0">R$ {{ number_format($this->precoPessoa() ?? 0, 2, ',', '.') }} / pessoa</p>
                    </div>
                </div>
            </div>
        @endif

        @error('criar') <div class="alert alert-danger">{{ $message }}</div> @enderror

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <a href="{{ route('encontre_time') }}" class="btn btn-voltar-laranja w-100 py-3 fw-bold">Voltar</a>
            </div>
            <div class="col-md-8">
                <button type="button" wire:click="criar" wire:loading.attr="disabled" class="btn btn-criar-laranja w-100 py-3 fw-bold">
                    Criar e Pagar
                </button>
            </div>
        </div>

        <div class="caixa-importante">
            <p class="fw-bold mb-2">Importante</p>
            <ul class="mb-0 small">
                <li>Você será o administrador desta sala</li>
                <li>A sala ficará reservada para o horário escolhido assim que for criada</li>
                <li>Cancelamentos podem ser feitos até 5h antes do jogo</li>
            </ul>
        </div>
    </div>
@endguest
</div>
