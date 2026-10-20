{{-- Campo de senha das telas de login, com botão para mostrar/ocultar o que foi digitado. --}}
<div class="login-input-wrap mb-2">
    <i class="bi bi-lock-fill"></i>
    <input type="password" name="password" id="login-senha" class="login-input" placeholder="Digite sua senha" required autocomplete="current-password">
    <button
        type="button"
        class="login-ver-senha"
        aria-controls="login-senha"
        aria-label="Mostrar senha"
        aria-pressed="false"
        title="Mostrar senha"
        onclick="
            const campo = document.getElementById('login-senha');
            const mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            this.setAttribute('aria-pressed', mostrar);
            this.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
            this.title = mostrar ? 'Ocultar senha' : 'Mostrar senha';
            this.querySelector('i').className = mostrar ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
            campo.focus();
        "
    >
        <i class="bi bi-eye-fill"></i>
    </button>
</div>
