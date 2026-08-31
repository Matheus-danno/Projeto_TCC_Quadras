<div>
    <h5 class="fw-bold text-secondary mb-1">Métodos de Pagamento</h5>
    <p class="text-muted small mb-4">Gerencie seus cartões e formas de pagamento</p>

    @if ($mensagemSucesso)
        <div class="alert alert-success">{{ $mensagemSucesso }}</div>
    @endif

    <div class="d-flex flex-column gap-3 mb-4">
        @forelse ($this->cartoes as $cartao)
            <div class="card border border-light-subtle shadow-none" style="border-radius: 15px;" wire:key="cartao-{{ $cartao->id }}">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="grupo-avatar flex-shrink-0" style="background-color: #fff3e0;">
                            <i class="bi {{ $cartao->bandeiraIcone() }} text-orange fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="fw-bold mb-0" style="color: #2D3748;">{{ $cartao->numeroMascarado() }}</h6>
                                @if ($cartao->principal)
                                    <span class="badge grupo-badge-voce">Principal</span>
                                @endif
                            </div>
                            <p class="text-muted small mb-0">{{ $cartao->nome_titular }} · Validade {{ $cartao->validade }}</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @unless ($cartao->principal)
                            <button type="button" wire:click="definirPrincipal({{ $cartao->id }})" class="btn btn-outline-orange btn-sm rounded-2">
                                Tornar principal
                            </button>
                        @endunless
                        <button type="button" wire:click="removerCartao({{ $cartao->id }})" wire:confirm="Remover este cartão?" class="btn btn-outline-danger btn-sm rounded-2">
                            Remover
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted small">Você ainda não tem cartões salvos.</p>
        @endforelse
    </div>

    @if ($mostrarFormulario)
        <div class="card border-0 shadow-sm" style="border-radius: 20px;">
            <div class="card-body p-4">
                <h6 class="fw-bold text-secondary mb-3">Adicionar cartão</h6>

                <form wire:submit.prevent="adicionarCartao" class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-label fw-bold small">Número do cartão</label>
                        <input
                            type="text"
                            wire:model="numeroCartao"
                            class="form-control border-orange"
                            placeholder="0000 0000 0000 0000"
                            maxlength="19"
                            oninput="this.value = this.value.replace(/\D/g,'').slice(0,19)"
                        >
                        @error('numeroCartao') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label fw-bold small">Nome impresso no cartão</label>
                        <input type="text" wire:model="nomeCartao" class="form-control border-orange" placeholder="Como está no cartão">
                        @error('nomeCartao') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Validade</label>
                            <input
                                type="text"
                                wire:model="validade"
                                class="form-control border-orange"
                                placeholder="MM/AA"
                                maxlength="5"
                                oninput="this.value = this.value.replace(/\D/g,'').replace(/(\d{2})(\d)/,'$1/$2').slice(0,5)"
                            >
                            @error('validade') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">CVV</label>
                            <input
                                type="text"
                                wire:model="cvv"
                                class="form-control border-orange"
                                placeholder="000"
                                maxlength="4"
                                oninput="this.value = this.value.replace(/\D/g,'').slice(0,4)"
                            >
                            @error('cvv') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn text-white px-4 shadow-sm" style="background-color: #FF8C00; border-radius: 10px; font-weight: bold;">
                            Salvar cartão
                        </button>
                        <button type="button" wire:click="cancelarFormulario" class="btn btn-outline-secondary fw-bold" style="border-radius: 10px;">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <button type="button" wire:click="abrirFormulario" class="btn text-white px-4 shadow-sm" style="background-color: #FF8C00; border-radius: 10px; font-weight: bold;">
            <i class="bi bi-plus-circle me-1"></i> Adicionar cartão
        </button>
    @endif

    <h6 class="fw-bold text-secondary mb-1 mt-5">Comprovantes de Pagamento</h6>
    <p class="text-muted small mb-3">Histórico de pagamentos de quadras e salas</p>

    <div class="d-flex flex-column gap-3">
        @forelse ($this->comprovantes as $comprovante)
            <div class="card border border-light-subtle shadow-none" style="border-radius: 15px;" wire:key="comprovante-{{ $comprovante['chave'] }}">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="grupo-avatar flex-shrink-0" style="background-color: #fff3e0;">
                            <i class="bi bi-receipt text-orange fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1" style="color: #2D3748;">{{ $comprovante['quadra'] }}</h6>
                            <p class="text-muted small mb-0">{{ $comprovante['referencia'] }} · {{ $comprovante['data']->format('d/m/Y \à\s H:i') }}</p>
                            <p class="text-muted small mb-0">{{ $comprovante['forma_pagamento'] }}</p>
                        </div>
                    </div>
                    <span class="fw-bold text-orange fs-5">R$ {{ number_format($comprovante['valor'], 2, ',', '.') }}</span>
                </div>
            </div>
        @empty
            <p class="text-muted small">Você ainda não tem comprovantes de pagamento.</p>
        @endforelse
    </div>
</div>
