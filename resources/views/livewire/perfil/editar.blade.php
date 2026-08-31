<div>
    @if ($mensagemSucesso)
        <div class="alert alert-success">{{ $mensagemSucesso }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
        <div class="card-body p-4">
            <h5 class="fw-bold text-secondary mb-4">Foto de Perfil</h5>

            <div class="d-flex align-items-center gap-4 flex-wrap">
                <img
                    src="{{ $avatar && $avatar->isPreviewable() ? $avatar->temporaryUrl() : auth()->user()->avatarUrl() }}"
                    alt="Foto de Perfil"
                    class="rounded-circle border border-4 border-warning"
                    style="width: 110px; height: 110px; object-fit: cover;"
                >

                <div class="d-flex flex-column gap-2">
                    <div class="d-flex gap-2 flex-wrap">
                        <label class="btn text-white px-4 shadow-sm mb-0" style="background-color: #FF8C00; border-radius: 10px; font-weight: bold; cursor: pointer;">
                            <i class="bi bi-camera me-1"></i> Escolher foto
                            <input type="file" wire:model="avatar" accept="image/*" class="d-none">
                        </label>

                        @if ($avatar)
                            <button type="button" wire:click="salvarFoto" wire:loading.attr="disabled" class="btn btn-outline-orange fw-bold" style="border-radius: 10px;">
                                Salvar foto
                            </button>
                        @elseif (auth()->user()->avatar_path)
                            <button type="button" wire:click="removerFoto" wire:confirm="Remover a foto de perfil?" class="btn btn-outline-secondary fw-bold" style="border-radius: 10px;">
                                Remover foto
                            </button>
                        @endif
                    </div>
                    <small class="text-muted">JPG, PNG ou WEBP, até 2MB.</small>
                    @error('avatar') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="card-body p-4">
            <h5 class="fw-bold text-secondary mb-4">Dados Pessoais</h5>

            <form wire:submit.prevent="salvar" class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label fw-bold small">Nome</label>
                    <input type="text" wire:model="name" class="form-control border-orange" required>
                    @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="form-label fw-bold small">E-mail</label>
                    <input type="email" wire:model="email" class="form-control border-orange" required>
                    @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">CPF</label>
                        <input type="text" value="{{ $this->cpfFormatado() }}" class="form-control" disabled>
                        <small class="text-muted">O CPF não pode ser alterado após o cadastro.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Data de nascimento</label>
                        <input type="date" wire:model="dataNascimento" class="form-control border-orange" required>
                        @error('dataNascimento') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="form-label fw-bold small">Sexo</label>
                    <select wire:model="sexo" class="form-select border-orange" required>
                        <option value="">Selecione</option>
                        @foreach ($sexos as $opcao)
                            <option value="{{ $opcao->value }}">{{ $opcao->label() }}</option>
                        @endforeach
                    </select>
                    @error('sexo') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="form-label fw-bold small">Endereço</label>
                    <input type="text" wire:model="endereco" class="form-control border-orange" required>
                    @error('endereco') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">CEP</label>
                        <input
                            type="text"
                            wire:model="cep"
                            class="form-control border-orange"
                            placeholder="00000-000"
                            maxlength="9"
                            oninput="this.value = this.value.replace(/\D/g,'').replace(/(\d{5})(\d)/,'$1-$2').slice(0,9)"
                            required
                        >
                        @error('cep') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold small">Cidade</label>
                        <input type="text" wire:model="cidade" class="form-control border-orange" required>
                        @error('cidade') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Estado (UF)</label>
                        <input type="text" wire:model="estado" class="form-control border-orange" maxlength="2" required>
                        @error('estado') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="form-label fw-bold small">Telefone</label>
                    <input
                        type="text"
                        wire:model="telefone"
                        class="form-control border-orange"
                        placeholder="(00) 00000-0000"
                        maxlength="15"
                        oninput="this.value = this.value.replace(/\D/g,'').replace(/(\d{2})(\d)/,'($1) $2').replace(/(\d{5})(\d)/,'$1-$2').slice(0,15)"
                        required
                    >
                    @error('telefone') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div>
                    <button type="submit" class="btn text-white px-4 shadow-sm" style="background-color: #FF8C00; border-radius: 10px; font-weight: bold;">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
