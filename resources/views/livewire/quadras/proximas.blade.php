<div
    x-data="{
        buscando: false,
        buscarLocalizacao() {
            if (! navigator.geolocation) {
                $wire.marcarPermissaoNegada();
                return;
            }

            this.buscando = true;

            navigator.geolocation.getCurrentPosition(
                (posicao) => {
                    this.buscando = false;
                    $wire.buscarPorLocalizacao(posicao.coords.latitude, posicao.coords.longitude);
                },
                () => {
                    this.buscando = false;
                    $wire.marcarPermissaoNegada();
                }
            );
        },
    }"
>
    {{-- Cabeçalho: botão de localização + seletor de raio --}}
    <div class="row mb-4 align-items-center g-3">
        <div class="col-auto">
            <button
                type="button"
                class="btn btn-orange-action btn-sm px-4 rounded-pill text-white fw-bold"
                @click="buscarLocalizacao()"
                :disabled="buscando"
            >
                <i class="bi bi-geo-alt"></i>
                <span x-show="! buscando">{{ $latitude === null ? 'Usar minha localização' : 'Atualizar localização' }}</span>
                <span x-show="buscando">Localizando...</span>
            </button>
        </div>

        @if ($latitude !== null)
            <div class="col-auto d-flex align-items-center gap-2 flex-wrap">
                <span class="text-orange fw-bold small">RAIO</span>
                @foreach ($this->raiosDisponiveis() as $km)
                    <button
                        type="button"
                        class="btn btn-sm rounded-pill {{ $raioKm === $km ? 'btn-orange-action text-white' : 'border-orange text-orange' }}"
                        wire:click="atualizarRaio({{ $km }})"
                    >
                        {{ $km }} km
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Permissão de localização negada --}}
    @if ($permissaoNegada)
        <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2" role="alert">
            <span><i class="bi bi-exclamation-triangle"></i> Não foi possível acessar sua localização. Verifique se a permissão de localização está liberada para este site no navegador e tente novamente.</span>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" @click="buscarLocalizacao()">
                Tentar novamente
            </button>
        </div>
    @endif

    {{-- Erro ao buscar na Overpass API --}}
    @if ($erro)
        <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2" role="alert">
            <span><i class="bi bi-exclamation-triangle"></i> {{ $erro }}</span>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" @click="buscarLocalizacao()">
                Tentar novamente
            </button>
        </div>
    @endif

    {{-- Carregando --}}
    <div wire:loading wire:target="buscarPorLocalizacao, atualizarRaio" class="text-center py-5">
        <div class="spinner-border text-orange" role="status">
            <span class="visually-hidden">Buscando quadras próximas...</span>
        </div>
    </div>

    {{-- Resultados --}}
    <div wire:loading.remove wire:target="buscarPorLocalizacao, atualizarRaio">
        @if ($latitude === null)
            @unless ($permissaoNegada)
                <div class="text-center py-5">
                    <i class="bi bi-geo-alt display-4 text-orange"></i>
                    <p class="text-muted mt-3 mb-0">Toque em "Usar minha localização" para ver as quadras esportivas mais perto de você.</p>
                </div>
            @endunless
        @else
            <div class="row g-4">
                @forelse ($quadras as $quadra)
                    <div class="col-md-6" wire:key="quadra-proxima-{{ $loop->index }}">
                        <div class="card card-quadra h-100 shadow-sm border-0 rounded-4 p-3">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <h5 class="text-orange fw-bold mb-1">{{ $quadra->nome }}</h5>
                                <span class="badge bg-light-green text-success text-nowrap">{{ $quadra->distanciaFormatada() }}</span>
                            </div>

                            <p class="text-muted small mb-3"><i class="bi bi-trophy"></i> {{ $quadra->esporte }}</p>

                            <a
                                href="https://www.google.com/maps/dir/?api=1&destination={{ $quadra->latitude }},{{ $quadra->longitude }}"
                                target="_blank"
                                rel="noopener"
                                class="btn btn-orange-action btn-sm px-4 rounded-pill text-white fw-bold align-self-start"
                            >
                                <i class="bi bi-geo-alt"></i> Abrir no mapa
                            </a>
                        </div>
                    </div>
                @empty
                    @if (! $erro)
                        <div class="col-12">
                            <p class="text-muted text-center py-5">Nenhuma quadra encontrada num raio de {{ $raioKm }}km. Tente aumentar o raio.</p>
                        </div>
                    @endif
                @endforelse
            </div>
        @endif
    </div>
</div>
