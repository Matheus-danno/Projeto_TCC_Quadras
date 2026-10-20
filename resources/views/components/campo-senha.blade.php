{{--
    Campo de senha dos cadastros (Livewire), com botão para mostrar/ocultar.
    wire:ignore impede que o Livewire redesenhe o campo após uma validação,
    o que voltaria o type para "password" com o ícone de "visível" ainda ativo.
--}}
@props(['model', 'autocomplete' => 'new-password'])

<div class="campo-senha" wire:ignore x-data="{ visivel: false }">
    <input
        type="password"
        :type="visivel ? 'text' : 'password'"
        class="cadastro-input"
        wire:model="{{ $model }}"
        placeholder="********"
        autocomplete="{{ $autocomplete }}"
        x-ref="campo"
    >
    <button
        type="button"
        class="campo-senha-botao"
        x-on:click="visivel = ! visivel; $refs.campo.focus()"
        :aria-pressed="visivel.toString()"
        :aria-label="visivel ? 'Ocultar senha' : 'Mostrar senha'"
        :title="visivel ? 'Ocultar senha' : 'Mostrar senha'"
        aria-label="Mostrar senha"
        title="Mostrar senha"
    >
        <i class="bi" :class="visivel ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
    </button>
</div>
