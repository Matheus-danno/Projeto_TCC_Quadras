@props(['nota' => 5, 'comentario' => '', 'acao'])

<div class="mt-2">
    <div class="d-flex gap-1 mb-2">
        @for ($i = 1; $i <= 5; $i++)
            <button
                type="button"
                wire:click="$set('nota', {{ $i }})"
                class="btn btn-link p-0 border-0"
                style="font-size: 1.5rem; line-height: 1;"
            >
                <i class="bi {{ $nota >= $i ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}"></i>
            </button>
        @endfor
    </div>
    @error('nota') <span class="text-danger small d-block mb-2">{{ $message }}</span> @enderror

    <textarea wire:model="comentario" class="form-control border-orange mb-2" rows="2" placeholder="Comentário (opcional)"></textarea>
    @error('comentario') <span class="text-danger small d-block mb-2">{{ $message }}</span> @enderror

    <div class="d-flex gap-2">
        <button type="button" wire:click="{{ $acao }}" class="btn btn-laranja btn-sm fw-bold">Enviar avaliação</button>
        <button type="button" wire:click="cancelarAvaliacao" class="btn btn-outline-secondary btn-sm fw-bold">Cancelar</button>
    </div>
</div>
