@extends('layouts.admin')

@section('admin-title', 'Configurações')
@section('admin-active', 'configuracoes')

@section('content')
    <div class="admin-settings-page">
        <header class="admin-settings-heading">
            <h1>Configurações</h1>
        </header>

        <section class="settings-card" aria-labelledby="settings-establishment-title">
            <h2 id="settings-establishment-title">Dados do Estabelecimento</h2>

            <div class="settings-grid settings-grid--two">
                <label class="settings-field">
                    <span>Nome do Estabelecimento</span>
                    <input type="text" value="Arena Sports Bauru">
                </label>

                <label class="settings-field">
                    <span>CNPJ</span>
                    <input type="text" value="12.345.678/0001-90">
                </label>

                <label class="settings-field">
                    <span>Responsável</span>
                    <input type="text" value="João Silva">
                </label>

                <label class="settings-field">
                    <span>Telefone</span>
                    <input type="tel" value="(14) 99999-2222">
                </label>
            </div>

            <button type="button" class="settings-save-button">Salvar</button>
        </section>

        <section class="settings-card" aria-labelledby="settings-hours-title">
            <h2 id="settings-hours-title">Horário de Funcionamento</h2>

            <div class="settings-hours-list">
                <div class="settings-hours-row">
                    <div class="settings-day">
                        <span>Segunda a sexta</span>
                        <button type="button" class="settings-switch is-on" aria-label="Segunda a sexta ativo">
                            <span></span>
                        </button>
                    </div>
                    <label>
                        <span>Início</span>
                        <input type="time" value="08:00">
                    </label>
                    <label>
                        <span>Fim</span>
                        <input type="time" value="22:00">
                    </label>
                </div>

                <div class="settings-hours-row">
                    <div class="settings-day">
                        <span>Sábado</span>
                        <button type="button" class="settings-switch is-on" aria-label="Sábado ativo">
                            <span></span>
                        </button>
                    </div>
                    <label>
                        <span>Início</span>
                        <input type="time" value="08:00">
                    </label>
                    <label>
                        <span>Fim</span>
                        <input type="time" value="22:00">
                    </label>
                </div>

                <div class="settings-hours-row settings-hours-row--closed">
                    <div class="settings-day">
                        <span>Domingo</span>
                        <button type="button" class="settings-switch" aria-label="Domingo fechado">
                            <span></span>
                        </button>
                    </div>
                    <span class="settings-closed-label">Fechado</span>
                </div>
            </div>
        </section>

        <section class="settings-card" aria-labelledby="settings-exceptions-title">
            <div class="settings-section-head">
                <div>
                    <h2 id="settings-exceptions-title">Exceções de data</h2>
                    <p>Fechamentos ou horários especiais para datas específicas, como feriados ou eventos.</p>
                </div>

                <button type="button" class="settings-outline-button">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Adicionar exceção
                </button>
            </div>

            <div class="settings-exceptions">
                <div class="settings-exception-row">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    <span>25/12/2026 - Natal (Fechado o dia todo)</span>
                    <button type="button" aria-label="Remover exceção">&times;</button>
                </div>
                <div class="settings-exception-row">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    <span>01/01/2027 - Feriado nacional (Fechado o dia todo)</span>
                    <button type="button" aria-label="Remover exceção">&times;</button>
                </div>
                <div class="settings-exception-row">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    <span>31/12/2026 - Véspera de ano novo (Horário Especial: 08:00 - 16:00)</span>
                    <button type="button" aria-label="Remover exceção">&times;</button>
                </div>
            </div>
        </section>

        <section class="settings-card" aria-labelledby="settings-pause-title">
            <h2 id="settings-pause-title">Pausar quadra temporariamente</h2>
            <p class="settings-card-copy">Marque uma quadra para não aparecer na plataforma durante um período.</p>

            <div class="settings-pause-grid">
                <label class="settings-field">
                    <span>Motivo</span>
                    <input type="text" placeholder="Digite aqui o motivo">
                </label>

                <label class="settings-field">
                    <span>Até quando</span>
                    <select>
                        <option>Até determinada data</option>
                        <option>Indeterminado</option>
                    </select>
                </label>
            </div>

            <label class="settings-checkbox">
                <input type="checkbox">
                <span>Por tempo indeterminado</span>
            </label>

            <button type="button" class="settings-danger-outline-button">Pausar quadra</button>
        </section>

        <div class="settings-bottom-grid">
            <section class="settings-card settings-card--compact" aria-labelledby="settings-payments-title">
                <h2 id="settings-payments-title">Métodos de pagamentos aceitos</h2>

                <label class="settings-check-row">
                    <input type="checkbox" checked>
                    <span>Cartão de crédito</span>
                </label>
                <label class="settings-check-row">
                    <input type="checkbox" checked>
                    <span>PIX</span>
                </label>
            </section>

            <section class="settings-card settings-card--compact" aria-labelledby="settings-notifications-title">
                <h2 id="settings-notifications-title">Notificações</h2>

                <div class="settings-notification-row">
                    <span>Segundas a sexta</span>
                    <button type="button" class="settings-switch settings-switch--small is-on" aria-label="Notificações segunda a sexta ativas">
                        <span></span>
                    </button>
                </div>
                <div class="settings-notification-row">
                    <span>Cancelamentos</span>
                    <button type="button" class="settings-switch settings-switch--small is-on" aria-label="Notificações de cancelamento ativas">
                        <span></span>
                    </button>
                </div>
                <div class="settings-notification-row">
                    <span>Mensagens de Clientes</span>
                    <button type="button" class="settings-switch settings-switch--small" aria-label="Notificações de mensagens inativas">
                        <span></span>
                    </button>
                </div>
            </section>
        </div>

        <div class="settings-delete-account">
            <p>Excluir sua conta remove todos os registros e reservas associadas permanentemente.</p>
            <button type="button">Excluir conta</button>
        </div>
    </div>
@endsection
