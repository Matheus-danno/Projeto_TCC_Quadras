<div class="container mb-5" style="max-width: 700px;">
    <div class="d-flex align-items-center gap-2 py-3">
        <a href="{{ route('salas.detalhes', $sala) }}" class="detalhes-link-topo text-decoration-none">
            <i class="bi bi-chevron-left"></i>
        </a>
        <h4 class="fw-semibold text-secondary mb-0">Pagamento</h4>
    </div>

    <div class="card border-0 shadow-sm card-arredondado mb-3">
        <div class="card-body p-4">
            <p class="detalhes-subtitulo mb-3">Resumo da partida</p>
            <p class="fw-semibold texto-jogo mb-1">
                {{ $sala->esporte->label() }} @if ($sala->nivel_desejado) · {{ $sala->nivel_desejado->label() }} @endif
            </p>
            @if ($sala->quadra)
                <p class="text-muted mb-1">{{ $sala->quadra->nome }} - {{ $sala->quadra->endereco }}, {{ $sala->quadra->bairro }}</p>
            @endif
            <p class="text-muted mb-0">
                @if ($sala->data && $sala->horario_inicio)
                    {{ $sala->data->isToday() ? 'Hoje' : $sala->data->format('d/m/Y') }},
                    {{ $sala->horario_inicio->format('H:i') }} - {{ $sala->horario_fim?->format('H:i') }}
                    @if ($sala->duracaoFormatada())
                        ({{ $sala->duracaoFormatada() }})
                    @endif
                @else
                    Horário a combinar
                @endif
                · {{ $sala->max_participantes }} jogadores
            </p>
        </div>
    </div>

    <form wire:submit.prevent="confirmarPagamento">
        <div class="card border-0 shadow-sm card-arredondado mb-3">
            <div class="card-body p-4">
                <p class="fw-semibold texto-jogo mb-3">Forma de Pagamento</p>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <button
                            type="button"
                            wire:click="selecionarFormaPagamento('pix')"
                            class="detalhes-metodo-pagamento w-100 {{ $formaPagamento === 'pix' ? 'detalhes-metodo-pagamento-ativo' : '' }}"
                        >
                            <i class="bi bi-qr-code fs-2 d-block mb-1"></i>
                            Pix
                        </button>
                    </div>
                    <div class="col-6">
                        <button
                            type="button"
                            wire:click="selecionarFormaPagamento('cartao')"
                            class="detalhes-metodo-pagamento w-100 {{ $formaPagamento === 'cartao' ? 'detalhes-metodo-pagamento-ativo' : '' }}"
                        >
                            <i class="bi bi-credit-card fs-2 d-block mb-1"></i>
                            Cartão de Crédito
                        </button>
                    </div>
                </div>

                @if ($formaPagamento === 'cartao')
                    <div class="mb-3">
                        <label class="detalhes-subtitulo d-block mb-1">Número do Cartão</label>
                        <input type="text" class="form-control detalhes-input" wire:model="numeroCartao" placeholder="0000 0000 0000 0000" maxlength="19">
                        @error('numeroCartao') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="detalhes-subtitulo d-block mb-1">Nome no cartão</label>
                        <input type="text" class="form-control detalhes-input" wire:model="nomeCartao" placeholder="Como está no cartão">
                        @error('nomeCartao') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="detalhes-subtitulo d-block mb-1">Validade</label>
                            <input type="text" class="form-control detalhes-input" wire:model="validade" placeholder="MM/AA" maxlength="5">
                            @error('validade') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6">
                            <label class="detalhes-subtitulo d-block mb-1">CVV</label>
                            <input type="text" class="form-control detalhes-input" wire:model="cvv" placeholder="000" maxlength="4">
                            @error('cvv') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
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

        @if ($erro)
            <div class="alert alert-danger">{{ $erro }}</div>
        @endif

        <div class="card border-0 shadow-sm card-arredondado mb-3">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <p class="detalhes-subtitulo fs-4 mb-0">Valor a pagar</p>
                <p class="fw-semibold text-orange fs-2 mb-0">
                    @if ($sala->precoPessoaCalculado())
                        R$ {{ number_format($sala->precoPessoaCalculado(), 2, ',', '.') }}
                    @else
                        A combinar
                    @endif
                </p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <a href="{{ route('salas.detalhes', $sala) }}" class="btn btn-outline-laranja fw-bold w-100 py-2">Voltar</a>
            </div>
            <div class="col-md-8">
                <button type="submit" class="btn btn-laranja fw-bold w-100 py-2" wire:loading.attr="disabled">Confirmar Pagamento</button>
            </div>
        </div>
    </form>
</div>
