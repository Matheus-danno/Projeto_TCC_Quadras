<div class="container my-5" style="max-width: 700px;">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('salas.detalhes', $sala) }}" class="text-decoration-none fs-3" style="color: #FF8C00;">
            <i class="bi bi-chevron-left"></i>
        </a>
        <h1 class="fw-bold fs-3 mb-0 texto-escuro">Pagamento</h1>
    </div>

    <div class="card border-0 shadow-sm card-arredondado p-4 mb-4">
        <p class="text-muted small fw-bold mb-2">Resumo da partida</p>
        <p class="fw-bold texto-escuro mb-1">
            {{ $sala->esporte->label() }} · {{ $sala->nivel_desejado?->label() ?? 'Todos os níveis' }}
        </p>
        <p class="text-muted mb-1">
            @if ($sala->quadra)
                {{ $sala->quadra->nome }} - {{ $sala->quadra->endereco }} - {{ $sala->quadra->cidade }}
            @else
                Local a definir
            @endif
        </p>
        <p class="text-muted mb-0">
            @if ($sala->data && $sala->hora_inicio)
                {{ $sala->data->isToday() ? 'Hoje' : $sala->data->format('d/m/Y') }},
                {{ \Illuminate\Support\Carbon::parse($sala->hora_inicio)->format('H:i') }}
                - {{ \Illuminate\Support\Carbon::parse($sala->horaFim)->format('H:i') }}
                ({{ intdiv($sala->duracao_minutos, 60) }}h{{ str_pad($sala->duracao_minutos % 60, 2, '0', STR_PAD_LEFT) }}min)
            @else
                Horário a combinar
            @endif
            · {{ $sala->max_participantes }} jogadores
        </p>
    </div>

    <div class="card border-0 shadow-sm card-arredondado p-4 mb-4">
        <p class="fw-bold texto-escuro mb-3">Forma de Pagamento</p>

        <div class="row g-3 mb-4">
            <div class="col-6">
                <button
                    type="button"
                    wire:click="selecionarFormaPagamento('pix')"
                    class="btn w-100 py-3 rounded-4 {{ $formaPagamento === 'pix' ? 'border-2' : 'border' }}"
                    style="{{ $formaPagamento === 'pix' ? 'background-color: rgba(255,140,0,0.12); border-color: #FF8C00; color: #515151;' : 'border-color: #adb5bd; color: #515151;' }}"
                >
                    <i class="bi bi-qr-code fs-2 d-block mb-1"></i>
                    Pix
                </button>
            </div>
            <div class="col-6">
                <button
                    type="button"
                    wire:click="selecionarFormaPagamento('cartao')"
                    class="btn w-100 py-3 rounded-4 {{ $formaPagamento === 'cartao' ? 'border-2' : 'border' }}"
                    style="{{ $formaPagamento === 'cartao' ? 'background-color: rgba(255,140,0,0.12); border-color: #FF8C00; color: #515151;' : 'border-color: #adb5bd; color: #515151;' }}"
                >
                    <i class="bi bi-credit-card fs-2 d-block mb-1"></i>
                    Cartão de Crédito
                </button>
            </div>
        </div>

        @if ($formaPagamento === 'cartao')
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Número do Cartão</label>
                <input type="text" class="form-control border-secondary-subtle rounded-3" wire:model="numeroCartao" placeholder="0000 0000 0000 0000" maxlength="19">
                @error('numeroCartao') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Nome no cartão</label>
                <input type="text" class="form-control border-secondary-subtle rounded-3" wire:model="nomeCartao" placeholder="Como está no cartão">
                @error('nomeCartao') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label small fw-bold text-muted">Validade</label>
                    <input type="text" class="form-control border-secondary-subtle rounded-3" wire:model="validade" placeholder="MM/AA" maxlength="5">
                    @error('validade') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-muted">CVV</label>
                    <input type="text" class="form-control border-secondary-subtle rounded-3" wire:model="cvv" placeholder="000" maxlength="4">
                    @error('cvv') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
        @else
            <div class="text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3" style="width: 220px; height: 220px; background-color: #f1f1f1;">
                    <i class="bi bi-qr-code" style="font-size: 5rem; color: #adb5bd;"></i>
                </div>
                <p class="text-muted small mb-3">Escaneie o QR code ou copie o código abaixo</p>

                <div class="d-flex gap-2">
                    <input type="text" class="form-control border-secondary-subtle rounded-3 text-truncate" readonly value="{{ $this->codigoPix() }}">
                    <button type="button" class="btn btn-laranja fw-bold rounded-3 px-4 text-nowrap" onclick="navigator.clipboard.writeText('{{ $this->codigoPix() }}')">
                        Copiar
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if ($erro)
        <div class="alert alert-danger">{{ $erro }}</div>
    @endif

    <div class="card border-0 shadow-sm card-arredondado p-4 mb-4 d-flex flex-row justify-content-between align-items-center">
        <span class="text-muted">Valor a pagar</span>
        <span class="fw-bold fs-3" style="color: #FF8C00;">
            @if ($sala->quadra)
                R$ {{ number_format($sala->valorPorPessoa(), 2, ',', '.') }}
            @else
                A combinar
            @endif
        </span>
    </div>

    <div class="d-flex gap-3">
        <a href="{{ route('salas.detalhes', $sala) }}" class="btn btn-outline-laranja fw-bold px-4 py-3 rounded-4">Voltar</a>
        <button type="button" class="btn btn-laranja fw-bold px-4 py-3 rounded-4 flex-grow-1" wire:click="confirmarPagamento" wire:loading.attr="disabled">
            Confirmar Pagamento
        </button>
    </div>
</div>
