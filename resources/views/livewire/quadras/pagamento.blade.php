<div class="container mb-5" style="max-width: 700px;">
    <div class="d-flex align-items-center gap-2 py-3">
        <a href="{{ route('quadras.index') }}" class="detalhes-link-topo text-decoration-none">
            <i class="bi bi-chevron-left"></i>
        </a>
        <h4 class="fw-semibold text-secondary mb-0">Pagamento</h4>
    </div>

    <div class="card border-0 shadow-sm card-arredondado mb-3">
        <div class="card-body p-4">
            <p class="detalhes-subtitulo mb-3">Resumo da reserva</p>
            <p class="fw-semibold texto-jogo mb-1">{{ $reserva->quadra->nome }}</p>
            <p class="text-muted mb-1">{{ $reserva->quadra->endereco }}, {{ $reserva->quadra->bairro }} - {{ $reserva->quadra->cidade }}</p>
            <p class="text-muted mb-0">
                {{ $reserva->data->isToday() ? 'Hoje' : $reserva->data->format('d/m/Y') }},
                {{ \Illuminate\Support\Carbon::parse($reserva->hora_inicio)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($reserva->hora_fim)->format('H:i') }}
                @if ($reserva->duracaoFormatada())
                    ({{ $reserva->duracaoFormatada() }})
                @endif
            </p>
        </div>
    </div>

    @if (auth()->user()->saldo_creditos > 0)
        <div class="card border-0 shadow-sm card-arredondado mb-3">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <p class="fw-semibold texto-jogo mb-1"><i class="bi bi-wallet2 me-1"></i> Você tem R$ {{ number_format(auth()->user()->saldo_creditos, 2, ',', '.') }} em créditos</p>
                    @if (! $this->podePagarComCredito())
                        <p class="text-muted small mb-0">Insuficiente para cobrir o valor total desta reserva.</p>
                    @endif
                </div>
                <button
                    type="button"
                    wire:click="pagarComCredito"
                    wire:loading.attr="disabled"
                    class="btn btn-outline-laranja fw-bold px-4"
                    @disabled(! $this->podePagarComCredito())
                >
                    Pagar com crédito
                </button>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="confirmarPagamento">
        <div class="card border-0 shadow-sm card-arredondado mb-3">
            <div class="card-body p-4">
                <p class="fw-semibold texto-jogo mb-3">Forma de Pagamento</p>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <button
                            type="button"
                            wire:click="$set('metodo', 'pix')"
                            class="detalhes-metodo-pagamento w-100 {{ $metodo === 'pix' ? 'detalhes-metodo-pagamento-ativo' : '' }}"
                        >
                            <i class="bi bi-qr-code fs-2 d-block mb-1"></i>
                            Pix
                        </button>
                    </div>
                    <div class="col-6">
                        <button
                            type="button"
                            wire:click="$set('metodo', 'cartao')"
                            class="detalhes-metodo-pagamento w-100 {{ $metodo === 'cartao' ? 'detalhes-metodo-pagamento-ativo' : '' }}"
                        >
                            <i class="bi bi-credit-card fs-2 d-block mb-1"></i>
                            Cartão de Crédito
                        </button>
                    </div>
                </div>

                @if ($metodo === 'cartao')
                    <div class="mb-3">
                        <label class="detalhes-subtitulo d-block mb-1">Número do Cartão</label>
                        <input type="text" class="form-control detalhes-input" placeholder="0000 0000 0000 0000" maxlength="19" required>
                    </div>
                    <div class="mb-3">
                        <label class="detalhes-subtitulo d-block mb-1">Nome no cartão</label>
                        <input type="text" class="form-control detalhes-input" placeholder="Como está no cartão" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="detalhes-subtitulo d-block mb-1">Validade</label>
                            <input type="text" class="form-control detalhes-input" placeholder="MM/AA" maxlength="5" required>
                        </div>
                        <div class="col-6">
                            <label class="detalhes-subtitulo d-block mb-1">CVV</label>
                            <input type="text" class="form-control detalhes-input" placeholder="000" maxlength="4" required>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="detalhes-qr-pix mx-auto mb-3">
                            <i class="bi bi-qr-code"></i>
                        </div>
                        <p class="text-muted mb-3">Escaneie o QR code ou copie o código abaixo</p>

                        <div class="row g-2 justify-content-center" x-data="{ copiado: false }">
                            <div class="col-9">
                                <input type="text" class="form-control detalhes-input text-truncate" value="{{ $this->codigoPix() }}" readonly onclick="this.select()">
                            </div>
                            <div class="col-3">
                                <button
                                    type="button"
                                    @click="
                                        navigator.clipboard.writeText(@js($this->codigoPix()));
                                        copiado = true;
                                        setTimeout(() => copiado = false, 2000);
                                    "
                                    class="btn btn-laranja fw-bold w-100 h-100"
                                >
                                    <span x-show="!copiado">Copiar</span>
                                    <span x-show="copiado"><i class="bi bi-check-lg"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm card-arredondado mb-3">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <p class="detalhes-subtitulo fs-4 mb-0">Valor a pagar</p>
                <p class="fw-semibold text-orange fs-2 mb-0">
                    R$ {{ number_format($reserva->quadra->valor_hora ?? 0, 2, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <a href="{{ route('quadras.index') }}" class="btn btn-outline-laranja fw-bold w-100 py-2">Voltar</a>
            </div>
            <div class="col-md-8">
                <button type="submit" class="btn btn-laranja fw-bold w-100 py-2">Confirmar Pagamento</button>
            </div>
        </div>
    </form>
</div>
