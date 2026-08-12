<div class="card shadow-sm cadastro-card">
    <h2 class="cadastro-titulo">Crie sua conta</h2>

    <form wire:submit="registrar">
        <div class="cadastro-field">
            <label class="cadastro-label">Nome completo</label>
            <input type="text" class="cadastro-input" wire:model="name" placeholder="Digite seu nome">
            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="cadastro-field">
            <label class="cadastro-label">Data de Nascimento</label>
            <div class="row g-2">
                <div class="col-4">
                    <select class="cadastro-select" wire:model="diaNascimento">
                        <option value="">Dia</option>
                        @foreach ($this->dias() as $dia)
                            <option value="{{ $dia }}">{{ $dia }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4">
                    <select class="cadastro-select" wire:model="mesNascimento">
                        <option value="">Mês</option>
                        @foreach ($this->meses() as $numero => $nome)
                            <option value="{{ $numero }}">{{ $nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4">
                    <select class="cadastro-select" wire:model="anoNascimento">
                        <option value="">Ano</option>
                        @foreach ($this->anos() as $ano)
                            <option value="{{ $ano }}">{{ $ano }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @error('diaNascimento') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            @error('mesNascimento') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            @error('anoNascimento') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="row g-3 cadastro-field">
            <div class="col-md-6">
                <label class="cadastro-label">CPF</label>
                <input
                    type="text"
                    class="cadastro-input"
                    wire:model="cpf"
                    placeholder="000.000.000-00"
                    maxlength="14"
                    oninput="this.value = this.value.replace(/\D/g,'').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2').slice(0,14)"
                >
                @error('cpf') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="cadastro-label">Sexo</label>
                <div class="d-flex gap-2">
                    @foreach ($sexos as $opcao)
                        <label class="cadastro-sexo">
                            {{ $opcao->label() }}
                            <input type="radio" wire:model="sexo" value="{{ $opcao->value }}">
                        </label>
                    @endforeach
                </div>
                @error('sexo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row g-3 cadastro-field">
            <div class="col-md-8">
                <label class="cadastro-label">Endereço</label>
                <input type="text" class="cadastro-input" wire:model="endereco" placeholder="Rua, Nº - Bairro">
                @error('endereco') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="cadastro-label">CEP</label>
                <input
                    type="text"
                    class="cadastro-input"
                    wire:model="cep"
                    placeholder="00000-000"
                    maxlength="9"
                    oninput="this.value = this.value.replace(/\D/g,'').replace(/(\d{5})(\d)/,'$1-$2').slice(0,9)"
                >
                @error('cep') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row g-3 cadastro-field">
            <div class="col-md-8">
                <label class="cadastro-label">Cidade</label>
                <input type="text" class="cadastro-input" wire:model="cidade" placeholder="Sua cidade">
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
            <div class="col-md-7">
                <label class="cadastro-label">E-mail</label>
                <input type="email" class="cadastro-input" wire:model="email" placeholder="seu@email.com">
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-5">
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
            <label class="cadastro-label">Senha</label>
            <input type="password" class="cadastro-input" wire:model="password" placeholder="********">
            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="cadastro-field">
            <label class="cadastro-label">Confirmar Senha</label>
            <input type="password" class="cadastro-input" wire:model="password_confirmation" placeholder="********">
        </div>

        <div class="d-flex gap-3">
            <a href="{{ route('home') }}" class="btn btn-cadastro-cancelar w-50">Cancelar</a>
            <button type="submit" class="btn btn-cadastro-criar w-50" wire:loading.attr="disabled">Criar conta</button>
        </div>
    </form>
</div>
