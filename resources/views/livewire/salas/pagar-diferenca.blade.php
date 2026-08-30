<div class="container mb-5" style="max-width: 700px;">
    <div class="d-flex align-items-center gap-2 py-3">
        <a href="{{ route('salas.grupo', $sala) }}" class="detalhes-link-topo text-decoration-none">
            <i class="bi bi-chevron-left"></i>
        </a>
        <h4 class="fw-semibold text-secondary mb-0">Pagar diferença</h4>
    </div>

    <div class="card border-0 shadow-sm card-arredondado mb-3">
        <div class="card-body p-4">
            <p class="detalhes-subtitulo mb-3">Fechar sala com menos jogadores</p>
            <p class="fw-semibold texto-jogo mb-1">{{ $sala->esporte->label() }} @if ($sala->nivel_desejado) · {{ $sala->nivel_desejado->label() }} @endif</p>
            @if ($sala->quadra)
                <p class="text-muted mb-1">{{ $sala->quadra->nome }} - {{ $sala->quadra->endereco }}, {{ $sala->quadra->bairro }}</p>
            @endif
            <p class="text-muted mb-0">
                {{ $sala->participantes->count() }} de {{ $sala->max_participantes }} jogadores confirmados
            </p>
        </div>
    </div>

    <div class="alert alert-warning">
        Ao fechar a sala agora, as {{ $sala->vagasRestantes() }} vaga(s) restante(s) deixarão de ser oferecidas e você
        paga a diferença entre o valor total da quadra e o que já foi arrecadado pelos jogadores confirmados.
    </div>

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
                <p class="detalhes-subtitulo fs-4 mb-0">Diferença a pagar</p>
                <p class="fw-semibold text-orange fs-2 mb-0">
                    R$ {{ number_format($sala->diferencaParaFechar(), 2, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <a href="{{ route('salas.grupo', $sala) }}" class="btn btn-outline-laranja fw-bold w-100 py-2">Voltar</a>
            </div>
            <div class="col-md-8">
                <button type="submit" class="btn btn-laranja fw-bold w-100 py-2">Confirmar e fechar sala</button>
            </div>
        </div>
    </form>
</div>
