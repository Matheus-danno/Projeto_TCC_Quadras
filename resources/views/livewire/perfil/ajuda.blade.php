<div>
    <style>
        .faq-acordeao .accordion-button {
            font-weight: bold;
            color: #2D3748;
        }
        .faq-acordeao .accordion-button:not(.collapsed) {
            color: #FF8C00;
            background-color: #fff3e0;
            box-shadow: none;
        }
        .faq-acordeao .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 140, 0, 0.25);
        }
    </style>

    <h5 class="fw-bold text-secondary mb-1">Central de Ajuda</h5>
    <p class="text-muted small mb-4">Tire suas dúvidas ou fale diretamente com o nosso suporte</p>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
        <div class="card-body p-4">
            <h6 class="fw-bold text-secondary mb-3">Perguntas frequentes</h6>

            <div class="accordion faq-acordeao" id="acordeaoAjuda">
                @foreach ($this->perguntasFrequentes() as $item)
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button
                                class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#faq-{{ $loop->index }}"
                            >
                                {{ $item['pergunta'] }}
                            </button>
                        </h2>
                        <div id="faq-{{ $loop->index }}" class="accordion-collapse collapse" data-bs-parent="#acordeaoAjuda">
                            <div class="accordion-body text-muted">
                                {{ $item['resposta'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="card-body p-4">
            <h6 class="fw-bold text-secondary mb-1">Falar com o Suporte</h6>
            <p class="text-muted small mb-3">Não encontrou o que precisava? Envie sua dúvida e responderemos por e-mail.</p>

            @if ($mensagemSucesso)
                <div class="alert alert-success">{{ $mensagemSucesso }}</div>
            @endif

            <form wire:submit.prevent="enviarMensagem" class="d-flex flex-column gap-3" style="max-width: 520px;">
                <div>
                    <label class="form-label fw-bold small">Assunto</label>
                    <input type="text" wire:model="assunto" class="form-control border-orange" placeholder="Ex: Problema com pagamento da reserva">
                    @error('assunto') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="form-label fw-bold small">Mensagem</label>
                    <textarea wire:model="mensagem" class="form-control border-orange" rows="4" placeholder="Descreva sua dúvida ou problema em detalhes"></textarea>
                    @error('mensagem') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div>
                    <button type="submit" class="btn text-white px-4 shadow-sm" style="background-color: #FF8C00; border-radius: 10px; font-weight: bold;">
                        <i class="bi bi-send me-1"></i> Enviar mensagem
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
