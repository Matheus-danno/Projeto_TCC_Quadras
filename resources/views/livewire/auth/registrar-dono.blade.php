<div class="card shadow-sm cadastro-dono-card">
    <h2 class="cadastro-dono-titulo">Cadastre seu Estabelecimento</h2>
    <p class="cadastro-dono-subtitulo">Cadastre sua quadra e comece a receber reservas.</p>

    <form wire:submit="registrar">
        <div class="cadastro-field">
            <label class="cadastro-label">Nome do Estabelecimento</label>
            <input type="text" class="cadastro-input" wire:model="nomeEstabelecimento" placeholder="Ex: Arena Sports Bauru">
            @error('nomeEstabelecimento') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="row g-3 cadastro-field">
            <div class="col-md-6">
                <label class="cadastro-label">CNPJ</label>
                <input
                    type="text"
                    class="cadastro-input"
                    wire:model="cnpj"
                    placeholder="00.000.000/0000-00"
                    maxlength="18"
                    oninput="this.value = this.value.replace(/\D/g,'').replace(/(\d{2})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1/$2').replace(/(\d{4})(\d{1,2})$/,'$1-$2').slice(0,18)"
                >
                @error('cnpj') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="cadastro-label">Telefone</label>
                <input
                    type="text"
                    class="cadastro-input"
                    wire:model="telefone"
                    placeholder="(00) 00000-0000"
                    maxlength="15"
                    oninput="this.value = this.value.replace(/\D/g,'').replace(/(\d{2})(\d)/,'($1) $2').replace(/(\d{5})(\d)/,'$1-$2').slice(0,15)"
                >
                @error('telefone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="cadastro-field">
            <label class="cadastro-label">Nome do Responsável</label>
            <input type="text" class="cadastro-input" wire:model="name" placeholder="Nome completo">
            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="cadastro-field">
            <label class="cadastro-label">E-mail</label>
            <input type="email" class="cadastro-input" wire:model="email" placeholder="seuemail@exemplo.com">
            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="cadastro-field">
            <label class="cadastro-label">Endereço</label>
            <input type="text" class="cadastro-input" wire:model="endereco" placeholder="Rua, número - Bairro">
            @error('endereco') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="row g-3 cadastro-field">
            <div class="col-md-8">
                <label class="cadastro-label">Cidade</label>
                <input type="text" class="cadastro-input" wire:model="cidade" placeholder="Cidade">
                @error('cidade') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="cadastro-label">Estado</label>
                <select class="cadastro-select" wire:model="estado">
                    <option value="">UF</option>
                    @foreach (['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $uf)
                        <option value="{{ $uf }}">{{ $uf }}</option>
                    @endforeach
                </select>
                @error('estado') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row g-3 cadastro-field">
            <div class="col-md-6">
                <label class="cadastro-label">Senha</label>
                <input type="password" class="cadastro-input" wire:model="password" placeholder="********">
                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="cadastro-label">Confirmar Senha</label>
                <input type="password" class="cadastro-input" wire:model="password_confirmation" placeholder="********">
            </div>
        </div>

        <div class="cadastro-field">
            <div class="form-check">
                <input
                    type="checkbox"
                    class="form-check-input"
                    id="aceitaComissao"
                    wire:model="aceitaComissao"
                    style="accent-color: var(--primary-orange, #ff8c00);"
                >
                <label class="form-check-label small text-muted" for="aceitaComissao">
                    Declaro estar ciente e de acordo que a AlugaQuadra reterá uma comissão de
                    <strong>5% sobre o valor de cada reserva</strong> realizada nas minhas quadras,
                    inclusive quando o agendamento for feito manualmente pelo estabelecimento
                    (Agendamento Manual).
                </label>
            </div>
            @error('aceitaComissao') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex gap-3">
            <a href="{{ route('login.dono') }}" class="btn btn-cadastro-cancelar w-50">Cancelar</a>
            <button type="submit" class="btn btn-cadastro-criar w-50" wire:loading.attr="disabled">Criar Conta</button>
        </div>
    </form>
</div>
