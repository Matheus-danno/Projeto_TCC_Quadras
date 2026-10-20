{{-- Consentimento LGPD usado nos cadastros de jogador e de dono de quadra. --}}
<div class="cadastro-field">
    <div class="form-check">
        <input
            type="checkbox"
            class="form-check-input"
            id="aceitaTermos"
            wire:model="aceitaTermos"
            style="accent-color: var(--primary-orange, #ff8c00);"
        >
        <label class="form-check-label small text-muted" for="aceitaTermos">
            Li e aceito a
            <a href="{{ route('politica.privacidade') }}" target="_blank" class="text-orange fw-bold">Política de Privacidade</a>
            e os
            <a href="{{ route('termos.uso') }}" target="_blank" class="text-orange fw-bold">Termos de Uso</a>,
            e autorizo o tratamento dos meus dados para as finalidades descritas neles.
        </label>
    </div>
    @error('aceitaTermos') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
</div>
