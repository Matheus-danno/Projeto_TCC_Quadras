@php
    $visual = [
        'futebol' => ['icone' => 'bi-dribbble', 'gradiente' => 'linear-gradient(135deg, #00b4d8, #0077b6)'],
        'futsal' => ['icone' => 'bi-circle-fill', 'gradiente' => 'linear-gradient(135deg, #2d6a4f, #1b4332)'],
        'volei' => ['icone' => 'bi-dribbble', 'gradiente' => 'linear-gradient(135deg, #FF8C00, #e67e00)'],
        'basquete' => ['icone' => 'bi-circle-fill', 'gradiente' => 'linear-gradient(135deg, #f77f00, #d62828)'],
        'tenis' => ['icone' => 'bi-record-circle', 'gradiente' => 'linear-gradient(135deg, #a7c957, #6a994e)'],
        'beach_tennis' => ['icone' => 'bi-record-circle', 'gradiente' => 'linear-gradient(135deg, #06d6a0, #00b4d8)'],
    ];
@endphp

<div class="container my-5 criar-sala-container">
    <h3 class="titulo-principal-laranja mb-4">Criar Nova Sala</h3>

    @guest
        <div class="alert alert-warning">
            Você precisa <a href="{{ route('login') }}">entrar</a> para criar uma sala.
        </div>
    @else
        <div>
            <h5 class="titulo-secao">Nome da sala</h5>
            <input type="text" class="form-control border-secondary-subtle fw-semibold text-secondary" wire:model="nome" placeholder="Ex: Racha de sexta-feira">
            @error('nome') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

            <h5 class="titulo-secao">Escolha o esporte</h5>
            <div class="row g-3 mb-2 text-center">
                @foreach ($esportes as $opcao)
                    <div class="col">
                        <div class="esporte-card {{ $esporte === $opcao->value ? 'active' : '' }}" wire:click="$set('esporte', '{{ $opcao->value }}')" style="cursor: pointer;">
                            <div class="esporte-img-box" style="background: {{ $visual[$opcao->value]['gradiente'] }};">
                                <i class="bi {{ $visual[$opcao->value]['icone'] }}"></i>
                            </div>
                            <span class="fw-bold text-secondary small">{{ $opcao->label() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            @error('esporte') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

            <h5 class="titulo-secao">Quadra (opcional)</h5>
            <select class="form-select border-secondary-subtle fw-semibold text-secondary" wire:model="quadraId">
                <option value="">Decidir depois</option>
                @foreach ($this->quadras as $quadra)
                    <option value="{{ $quadra->id }}">{{ $quadra->nome }} - {{ $quadra->cidade }}</option>
                @endforeach
            </select>
            @error('quadraId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

            <h5 class="titulo-secao">Número máximo de participantes</h5>
            <div class="caixa-destaque caixa-branca text-center p-2" style="width: 160px;">
                <label class="subtitulo-campo font-size-sm">Vagas</label>
                <div class="qty-grupo">
                    <button type="button" class="qty-btn" wire:click="decrementarVagas">-</button>
                    <input type="text" class="qty-input" value="{{ $maxParticipantes }}" readonly>
                    <button type="button" class="qty-btn" wire:click="incrementarVagas">+</button>
                </div>
            </div>
            @error('maxParticipantes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

            <div class="row g-3 mt-4 mb-5">
                <div class="col-md-6">
                    <a href="{{ route('encontre_time') }}" class="btn btn-voltar-laranja w-100 py-3 fw-bold fs-5 shadow-sm">Cancelar</a>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-criar-laranja w-100 py-3 fw-bold fs-5 shadow-sm" wire:click="criar" wire:loading.attr="disabled">
                        Criar Sala
                    </button>
                </div>
            </div>
        </div>
    @endguest
</div>
