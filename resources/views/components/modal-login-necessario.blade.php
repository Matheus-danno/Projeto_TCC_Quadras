<div class="modal fade" id="modalLoginNecessario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" style="z-index: 1;" data-bs-dismiss="modal" aria-label="Fechar"></button>
            <div class="modal-body p-4 text-center">
                <i class="bi bi-lock-fill text-orange" style="font-size: 2.5rem;"></i>
                <p class="fw-semibold mt-3 mb-4" id="modalLoginNecessarioTexto">Você precisa entrar para continuar.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-orange-action fw-bold px-4">Entrar</a>
                    <a href="{{ route('registro') }}" class="btn btn-outline-secondary fw-bold px-4">Cadastrar-se</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('modalLoginNecessario');
        const modalTexto = document.getElementById('modalLoginNecessarioTexto');
        const modal = new bootstrap.Modal(modalEl);

        modalEl.addEventListener('show.bs.modal', (event) => {
            const mensagem = event.relatedTarget?.dataset?.mensagem;
            if (mensagem) {
                modalTexto.textContent = mensagem;
            }
        });

        window.abrirModalLoginNecessario = (mensagem) => {
            modalTexto.textContent = mensagem || 'Você precisa entrar para continuar.';
            modal.show();
        };

        document.addEventListener('livewire:init', () => {
            Livewire.on('login-necessario', (data) => {
                window.abrirModalLoginNecessario(data.mensagem);
            });
        });
    });
</script>
