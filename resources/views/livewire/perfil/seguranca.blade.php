<div>
    <h5 class="fw-bold text-secondary mb-1">Segurança da Conta</h5>
    <p class="text-muted small mb-4">Gerencie sua senha e a autenticação de dois fatores</p>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
        <div class="card-body p-4">
            <h6 class="fw-bold text-secondary mb-1">Alterar Senha</h6>
            <p class="text-muted small mb-3">Use uma senha longa e que você não utiliza em outros sites</p>

            @if ($mensagemSenha)
                <div class="alert alert-success">{{ $mensagemSenha }}</div>
            @endif

            <form wire:submit.prevent="atualizarSenha" class="d-flex flex-column gap-3" style="max-width: 420px;">
                <div>
                    <label class="form-label fw-bold small">Senha atual</label>
                    <input type="password" wire:model="current_password" class="form-control border-orange" autocomplete="current-password">
                    @error('current_password') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="form-label fw-bold small">Nova senha</label>
                    <input type="password" wire:model="password" class="form-control border-orange" autocomplete="new-password">
                    @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="form-label fw-bold small">Confirmar nova senha</label>
                    <input type="password" wire:model="password_confirmation" class="form-control border-orange" autocomplete="new-password">
                </div>

                <div>
                    <button type="submit" class="btn text-white px-4 shadow-sm" style="background-color: #FF8C00; border-radius: 10px; font-weight: bold;">
                        Salvar nova senha
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
                <h6 class="fw-bold text-secondary mb-0">Autenticação de Dois Fatores</h6>
                @if ($twoFactorEnabled)
                    <span class="badge rounded-pill" style="background-color: #d1f5dd; color: #1a7a3d;">Ativada</span>
                @else
                    <span class="badge rounded-pill" style="background-color: #fde2e2; color: #b42318;">Desativada</span>
                @endif
            </div>
            <p class="text-muted small mb-3">Adicione uma camada extra de segurança usando um aplicativo autenticador (TOTP)</p>

            @if ($mensagem2fa)
                <div class="alert alert-success">{{ $mensagem2fa }}</div>
            @endif

            @if ($twoFactorEnabled)
                <p class="text-muted small">Com a autenticação de dois fatores ativada, você precisará informar um código do seu aplicativo autenticador ao entrar.</p>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" wire:click="mostrarRecuperacao" class="btn btn-outline-orange btn-sm rounded-2">
                        Ver códigos de recuperação
                    </button>
                    <button type="button" wire:click="desativar2fa" wire:confirm="Desativar a autenticação de dois fatores?" class="btn btn-outline-danger btn-sm rounded-2">
                        Desativar 2FA
                    </button>
                </div>

                @if ($mostrarCodigosRecuperacao)
                    <div class="card border border-light-subtle shadow-none" style="border-radius: 15px;">
                        <div class="card-body">
                            <p class="text-muted small mb-2">Guarde esses códigos em um lugar seguro. Cada um pode ser usado uma única vez para entrar caso você perca acesso ao aplicativo autenticador.</p>
                            <div class="row row-cols-2 g-2 font-monospace small mb-3" style="max-width: 420px;">
                                @foreach ($codigosRecuperacao as $codigoRecuperacao)
                                    <div class="col">{{ $codigoRecuperacao }}</div>
                                @endforeach
                            </div>
                            <button type="button" wire:click="gerarNovosCodigos" wire:confirm="Gerar novos códigos? Os códigos antigos deixarão de funcionar." class="btn btn-outline-orange btn-sm rounded-2">
                                Gerar novos códigos
                            </button>
                        </div>
                    </div>
                @endif
            @elseif ($mostrarConfiguracao2fa)
                <div class="card border border-light-subtle shadow-none" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <p class="fw-bold mb-2" style="color: #2D3748;">Escaneie o QR Code com seu aplicativo autenticador</p>
                        <div class="d-flex justify-content-center bg-light rounded p-3 mb-3" style="max-width: 260px;">
                            {!! $qrCodeSvg !!}
                        </div>

                        <p class="text-muted small mb-1">Ou insira a chave manualmente:</p>
                        <input type="text" class="form-control border-orange mb-3" readonly value="{{ $manualSetupKey }}" style="max-width: 420px;">

                        <label class="form-label fw-bold small">Código de verificação</label>
                        <input
                            type="text"
                            wire:model="codigo"
                            maxlength="6"
                            class="form-control border-orange mb-2"
                            style="max-width: 200px;"
                            placeholder="000000"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/\D/g,'').slice(0,6)"
                        >
                        @error('codigo') <span class="text-danger small d-block mb-2">{{ $message }}</span> @enderror

                        <div class="d-flex gap-2 mt-2">
                            <button type="button" wire:click="confirmar2fa" class="btn text-white px-4 shadow-sm" style="background-color: #FF8C00; border-radius: 10px; font-weight: bold;">
                                Confirmar e ativar
                            </button>
                            <button type="button" wire:click="cancelarConfiguracao2fa" class="btn btn-outline-secondary fw-bold" style="border-radius: 10px;">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-muted small mb-3">Quando ativada, você precisará informar um código gerado por um aplicativo autenticador (como Google Authenticator) ao entrar.</p>
                <button type="button" wire:click="iniciarConfiguracao2fa" class="btn text-white px-4 shadow-sm" style="background-color: #FF8C00; border-radius: 10px; font-weight: bold;">
                    <i class="bi bi-shield-check me-1"></i> Ativar 2FA
                </button>
            @endif
        </div>
    </div>
</div>
