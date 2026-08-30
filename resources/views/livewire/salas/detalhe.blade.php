<div class="container mb-5">
    @if (session('sala-criada'))
        <div class="alert alert-success mt-3">{{ session('sala-criada') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center detalhes-topo py-3">
        <a href="{{ route('encontre_time') }}" class="detalhes-link-topo text-decoration-none">
            <i class="bi bi-x-lg me-1"></i> Voltar
        </a>

        <h4 class="fw-bold text-orange mb-0">Detalhes da Sala</h4>

        <button
            type="button"
            x-data="{ copiado: false }"
            @click="
                navigator.clipboard.writeText(window.location.href);
                copiado = true;
                setTimeout(() => copiado = false, 2000);
            "
            class="btn btn-link detalhes-link-topo text-decoration-none p-0"
        >
            <template x-if="!copiado"><span><i class="bi bi-share me-1"></i> Compartilhar</span></template>
            <template x-if="copiado"><span><i class="bi bi-check-lg me-1"></i> Link copiado!</span></template>
        </button>
    </div>

    <div class="row g-2 mb-3">
        @for ($i = 0; $i < 4; $i++)
            <div class="col-3">
                <img
                    src="{{ asset($sala->esporte->imagem()) }}"
                    alt="{{ $sala->esporte->label() }}"
                    class="detalhes-galeria-img {{ $i === 0 ? 'rounded-start-3' : ($i === 3 ? 'rounded-end-3' : '') }}"
                >
            </div>
        @endfor
    </div>

    @auth
        @if ($sala->nivel_desejado && auth()->user()->nivel)
            @php $compativel = $sala->nivel_desejado->compativelCom(auth()->user()->nivel); @endphp
            <div class="alert-compativel {{ $compativel ? 'alert-compativel-sim' : 'alert-compativel-nao' }} mb-3">
                <p class="fw-semibold mb-1">
                    {{ $compativel ? '✓ Você é compatível com esta sala!' : '⚠ Nível diferente do seu' }}
                </p>
                <p class="mb-0">
                    Seu nível: {{ auth()->user()->nivel->label() }}
                    · Reputação:
                    @if (auth()->user()->notaMedia())
                        <i class="bi bi-star-fill"></i> {{ number_format(auth()->user()->notaMedia(), 1, ',', '.') }}
                        ({{ auth()->user()->salas()->count() }} {{ auth()->user()->salas()->count() === 1 ? 'jogo' : 'jogos' }})
                    @else
                        sem avaliações ainda
                    @endif
                </p>
            </div>
        @endif
    @endauth

    <div class="card border-0 shadow-sm card-arredondado mb-3">
        <div class="card-body p-4">
            <div class="row align-items-start">
                <div class="col-lg-8">
                    <h3 class="fw-semibold texto-jogo mb-2">{{ $sala->esporte->label() }}</h3>

                    <div class="d-flex gap-2 mb-3 flex-wrap">
                        @if ($sala->nivel_desejado)
                            <span class="badge badge-vagas">{{ $sala->nivel_desejado->label() }}</span>
                        @endif
                        <span class="badge badge-vagas">{{ $sala->participantes->count() }}/{{ $sala->max_participantes }} vagas</span>
                        @if ($sala->tempoParaComecoFormatado())
                            <span class="badge badge-tempo-forte">
                                Faltam {{ $sala->tempoParaComecoFormatado() }} para a sala fechar
                            </span>
                        @endif
                    </div>

                    <p class="detalhes-subtitulo mb-1"><i class="bi bi-geo-alt text-orange me-1"></i> Local</p>
                    <p class="fw-semibold texto-jogo mb-0">{{ $sala->quadra?->nome ?? 'Local a definir' }}</p>
                    @if ($sala->quadra)
                        <p class="text-muted small mb-0">
                            {{ $sala->quadra->endereco }} - {{ $sala->quadra->cidade }}, {{ $sala->quadra->bairro }}
                        </p>
                    @endif
                </div>

                @if ($sala->quadra)
                    <div class="col-lg-4">
                        <div class="detalhes-descricao-box">
                            <p class="fw-semibold text-secondary small mb-2">Descrição</p>
                            <p class="small mb-0">
                                {{ implode(' | ', $sala->quadra->listaAmenidades()) }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <hr class="detalhes-linha">

            <p class="detalhes-subtitulo mb-1"><i class="bi bi-clock text-orange me-1"></i> Data e Horário</p>
            <p class="fw-semibold texto-jogo mb-1">
                @if ($sala->data)
                    {{ $sala->data->isToday() ? 'Hoje' : $sala->data->format('d/m/Y') }},
                    {{ $sala->horario_inicio?->format('H:i') }} - {{ $sala->horario_fim?->format('H:i') }}
                @else
                    Horário a definir
                @endif
            </p>
            @if ($sala->duracaoFormatada())
                <p class="text-muted small mb-0">Duração: {{ $sala->duracaoFormatada() }}</p>
            @endif

            <hr class="detalhes-linha">

            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2">
                <div>
                    <p class="detalhes-subtitulo mb-1">Valor por pessoa</p>
                    @if ($sala->precoPessoaCalculado())
                        <p class="fw-semibold text-orange fs-4 mb-0">R$ {{ number_format($sala->precoPessoaCalculado(), 2, ',', '.') }}</p>
                    @else
                        <p class="text-muted mb-0">Gratuito</p>
                    @endif
                </div>
                @if ($sala->valorTotalQuadra())
                    <p class="text-muted small mb-0">Total da Quadra: R$ {{ number_format($sala->valorTotalQuadra(), 2, ',', '.') }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm card-arredondado mb-3">
        <div class="card-body p-4 d-flex align-items-center gap-3">
            <img src="https://i.pravatar.cc/150?u={{ $sala->criador->id }}" alt="{{ $sala->criador->name }}" class="detalhes-avatar-admin">

            <div class="flex-grow-1">
                <p class="fw-semibold texto-jogo mb-0">{{ $sala->criador->name }}</p>
                <p class="text-muted small mb-0">Administrador da Sala</p>
                <p class="text-muted small mb-0">
                    Membro desde {{ $sala->criador->membroDesde() }} - {{ $sala->criador->partidasOrganizadas() }} partidas organizadas
                </p>
            </div>

            @if ($sala->criador->notaMedia())
                <div class="text-end">
                    <p class="fw-semibold texto-jogo fs-4 mb-0"><i class="bi bi-star-fill text-warning"></i> {{ number_format($sala->criador->notaMedia(), 1, ',', '.') }}</p>
                    <p class="text-muted small mb-0">({{ $sala->criador->avaliacoesRecebidas->count() }} avaliações)</p>
                </div>
            @endif
        </div>
    </div>

    <h5 class="fw-semibold texto-jogo mb-3">Participantes Confirmados ({{ $this->participantes->count() }})</h5>
    <div class="card border-0 shadow-sm card-arredondado mb-3">
        <div class="card-body p-4">

            @php
                $participantesExibidos = $mostrarTodosParticipantes ? $this->participantes : $this->participantes->take(3);
            @endphp

            @foreach ($participantesExibidos as $participante)
                <div class="d-flex align-items-center gap-3 py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                    <img src="https://i.pravatar.cc/150?u={{ $participante->id }}" alt="{{ $participante->name }}" class="detalhes-avatar-participante">

                    <div class="flex-grow-1">
                        <p class="fw-semibold texto-jogo mb-0">
                            {{ $participante->name }}
                            @if ($participante->id === $sala->criador_id)
                                <span class="badge badge-tempo ms-1">ADMIN</span>
                            @endif
                        </p>
                        @if ($participante->nivel)
                            <p class="text-muted small mb-0">{{ $participante->nivel->label() }}</p>
                        @endif
                    </div>

                    @if ($participante->notaMedia())
                        <span class="text-muted"><i class="bi bi-star-fill text-warning"></i> {{ number_format($participante->notaMedia(), 1, ',', '.') }}</span>
                    @endif
                </div>
            @endforeach

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <div>
                    @if (! $mostrarTodosParticipantes && $this->participantes->count() > 3)
                        <p class="text-muted small mb-0">+ {{ $this->participantes->count() - 3 }} jogadores confirmados</p>
                    @endif
                    <p class="fw-bold text-muted small mb-0">Vagas disponíveis ({{ $sala->vagasRestantes() }})</p>
                </div>

                @if ($this->participantes->count() > 3)
                    <button type="button" wire:click="$toggle('mostrarTodosParticipantes')" class="btn btn-link text-orange fw-bold text-decoration-none p-0">
                        {{ $mostrarTodosParticipantes ? 'Ver menos' : 'Ver todos' }} <i class="bi bi-arrow-right"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <h5 class="fw-semibold texto-jogo mb-3">Regras da sala</h5>
    <div class="card border-0 shadow-sm card-arredondado mb-3">
        <div class="card-body p-4">
            <ul class="detalhes-regras-lista">
                @if ($sala->nivel_desejado)
                    <li>✓ Nível: {{ $sala->nivel_desejado->label() }} ({{ $sala->aceitacao_niveis_adjacentes->label() === 'Aceitar todos' ? 'aceita níveis adjacentes' : $sala->aceitacao_niveis_adjacentes->label() }})</li>
                @endif
                <li>✓ Sala {{ strtolower($sala->privacidade->label()) }} - {{ $sala->privacidade->descricao() }}</li>
                <li>✓ {{ $sala->aprovacao->label() }} - {{ $sala->aprovacao->descricao() }}</li>
                <li>✓ Cancelamento gratuito até 5h antes do jogo</li>
                <li>✓ Times serão balanceados automaticamente pelo sistema</li>
                <li>✓ Avaliação obrigatória após a partida</li>
                @if ($sala->regras_adicionais)
                    <li>✓ {{ $sala->regras_adicionais }}</li>
                @endif
                <li class="text-muted">⚠ Ausência não justificada afeta sua reputação</li>
            </ul>
        </div>
    </div>

    @if ($this->pedidosPendentes->isNotEmpty())
        <h5 class="fw-semibold texto-jogo mb-3">Pedidos de participação ({{ $this->pedidosPendentes->count() }})</h5>
        <div class="card border-0 shadow-sm card-arredondado mb-3">
            <div class="card-body p-4">
                @foreach ($this->pedidosPendentes as $pedido)
                    <div class="d-flex align-items-center gap-3 py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <img src="https://i.pravatar.cc/150?u={{ $pedido->user->id }}" alt="{{ $pedido->user->name }}" class="detalhes-avatar-participante">
                        <p class="fw-semibold texto-jogo mb-0 flex-grow-1">{{ $pedido->user->name }}</p>
                        <button type="button" wire:click="aprovarPedido({{ $pedido->id }})" class="btn btn-laranja fw-bold btn-ver-detalhes">Aprovar</button>
                        <button type="button" wire:click="recusarPedido({{ $pedido->id }})" class="btn btn-outline-secondary fw-bold btn-ver-detalhes">Recusar</button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($mensagemPendente)
        <div class="alert alert-info">{{ $mensagemPendente }}</div>
    @endif

    @if (isset($erros['entrar']))
        <div class="alert alert-danger">{{ $erros['entrar'] }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <button
                type="button"
                x-data="{ copiado: false }"
                @click="
                    navigator.clipboard.writeText(window.location.href);
                    copiado = true;
                    setTimeout(() => copiado = false, 2000);
                "
                class="btn btn-outline-laranja fw-bold w-100 py-3"
            >
                <template x-if="!copiado"><span><i class="bi bi-share me-1"></i> Compartilhar Sala</span></template>
                <template x-if="copiado"><span><i class="bi bi-check-lg me-1"></i> Link copiado!</span></template>
            </button>
        </div>
        <div class="col-md-6">
            @guest
                <p class="small mb-2 text-center">Você precisa <a href="{{ route('login') }}">entrar</a> para participar.</p>
            @else
                @if ($sala->status->value === 'fechada')
                    <button class="btn btn-outline-secondary fw-bold w-100 py-3" disabled>Sala fechada</button>
                @elseif ($sala->participantes->contains('id', auth()->id()))
                    <a href="{{ route('salas.grupo', $sala) }}" class="btn btn-laranja fw-bold w-100 py-3">Ver minha sala</a>
                @elseif ($this->meuPedido?->status?->value === 'pendente')
                    <button class="btn btn-outline-laranja fw-bold w-100 py-3" style="color: #515151;" disabled>Pedido aguardando aprovação</button>
                @elseif ($sala->participantes->count() >= $sala->max_participantes)
                    <button class="btn btn-outline-secondary fw-bold w-100 py-3" disabled>Sala cheia</button>
                @elseif ($sala->aprovacao->value === 'automatica' && $sala->precoPessoaCalculado())
                    <a href="{{ route('salas.pagamento', $sala) }}" class="btn btn-laranja fw-bold w-100 py-3">
                        Entrar e Pagar - R$ {{ number_format($sala->precoPessoaCalculado(), 2, ',', '.') }}
                    </a>
                @else
                    <button wire:click="entrar" class="btn btn-laranja fw-bold w-100 py-3">
                        {{ $sala->aprovacao->value === 'manual' ? 'Solicitar entrada' : 'Entrar' }}
                    </button>
                @endif
            @endguest
        </div>
    </div>

    @if ($sala->precoPessoaCalculado())
        <div class="caixa-info-pagamento mb-3">
            <p class="fw-bold text-orange mb-2">Informações de pagamento</p>
            <p class="mb-1">Pagamento seguro via cartão ou PIX</p>
            <p class="mb-1">Confirmação instantânea da sua vaga</p>
            <p class="mb-0">Reembolso automático em caso de cancelamento</p>
        </div>
    @endif

    @if ($sala->tempoParaComecoFormatado() && $sala->vagasRestantes() > 0)
        <div class="caixa-reserva-temporaria mb-3">
            <p class="fw-bold mb-2"><i class="bi bi-clock-history me-1"></i> Reserva temporária ativa!</p>
            <p class="mb-1">O horário foi reservado por {{ $sala->tempoParaComecoFormatado() }} enquanto a sala completa</p>
            <p class="mb-0">Faltam apenas {{ $sala->vagasRestantes() }} jogadores para confirmação definitiva!</p>
        </div>
    @endif

    @if ($sala->atividades->isNotEmpty())
        <h5 class="fw-semibold texto-jogo mb-3">Atividade recente</h5>
        <div class="card border-0 shadow-sm card-arredondado">
            <div class="card-body p-4">

                @foreach ($sala->atividades as $atividade)
                    <div class="py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <p class="text-muted small fw-bold mb-1">Há {{ $atividade->tempoDecorrido() }}</p>
                        <p class="texto-jogo small mb-0">{{ $atividade->descricao }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
