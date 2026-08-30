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

                <div>
                    <button type="submit" class="btn text-white px-4 shadow-sm" style="background-color: #FF8C00; border-radius: 10px; font-weight: bold;">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
