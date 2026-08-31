@extends('layouts.bootstrap')

@section('titulo', 'Meu Perfil')

@section('conteudo')

<style>
    .custom-tabs .nav-link {
        color: #6c757d;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 50rem; /* Formato de pílula */
        padding: 0.5rem 1.25rem;
        font-size: 0.85rem;
        font-weight: bold;
        white-space: nowrap;
        transition: all 0.3s ease;
    }
    .custom-tabs .nav-link:hover {
        background-color: #e2e6ea;
    }
    .custom-tabs .nav-link.active {
        color: #fff !important;
        background-color: #FF8C00 !important;
        border-color: #FF8C00 !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
</style>

<div class="container my-5">
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center flex-wrap">
                <div class="position-relative me-4 mb-3 mb-md-0">
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="Foto de Perfil" class="rounded-circle border border-4 border-warning" style="width: 130px; height: 130px; object-fit: cover;">
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-1" style="color: #2D3748;">{{ auth()->user()->name }}</h2>
                    <div class="d-flex flex-wrap gap-3 text-secondary mt-2">
                        <span><i class="bi bi-envelope me-1" style="color: #FF8C00;"></i> {{ auth()->user()->email }}</span>
                        <span><i class="bi bi-person-badge me-1" style="color: #FF8C00;"></i> {{ auth()->user()->role->label() }}</span>
                        <span><i class="bi bi-calendar-event me-1" style="color: #FF8C00;"></i> Membro desde {{ auth()->user()->created_at->translatedFormat('F Y') }}</span>
                    </div>
                    <a href="{{ route('perfil.editar') }}" class="btn mt-3 text-white px-4 shadow-sm" style="background-color: #FF8C00; border-radius: 10px; font-weight: bold;">
                        <i class="bi bi-pencil me-1"></i> Editar Perfil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav custom-tabs gap-2 mb-4 flex-wrap border-0" id="perfilTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="reservas-tab" data-bs-toggle="tab" data-bs-target="#reservas" type="button" role="tab">
                <i class="bi bi-clock-history me-1"></i> Minhas Reservas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="salas-tab" data-bs-toggle="tab" data-bs-target="#salas" type="button" role="tab">
                <i class="bi bi-door-open me-1"></i> Minhas Salas
            </button>
        </li>
        @if (auth()->user()->salasCriadas()->exists())
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="mensagens-tab" data-bs-toggle="tab" data-bs-target="#mensagens" type="button" role="tab">
                    <i class="bi bi-chat-dots me-1"></i> Mensagens
                </button>
            </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="notificacoes-tab" data-bs-toggle="tab" data-bs-target="#notificacoes" type="button" role="tab">
                <i class="bi bi-bell me-1"></i> Notificações
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pedidos-tab" data-bs-toggle="tab" data-bs-target="#pedidos" type="button" role="tab">
                <i class="bi bi-bag-check me-1"></i> Meus Pedidos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="favoritas-tab" data-bs-toggle="tab" data-bs-target="#favoritas" type="button" role="tab">
                <i class="bi bi-person me-1"></i> Quadras Favoritas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pagamentos-tab" data-bs-toggle="tab" data-bs-target="#pagamentos" type="button" role="tab">
                <i class="bi bi-credit-card me-1"></i> Pagamentos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="seguranca-tab" data-bs-toggle="tab" data-bs-target="#seguranca" type="button" role="tab">
                <i class="bi bi-key me-1"></i> Segurança
            </button>
        </li>
    </ul>

    <div class="tab-content" id="perfilTabsContent">

        <div class="tab-pane fade show active" id="reservas" role="tabpanel">
            <livewire:perfil.saldo-creditos />

            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <livewire:perfil.minhas-reservas />
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="salas" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <livewire:perfil.minhas-salas />
                </div>
            </div>
        </div>

        @if (auth()->user()->salasCriadas()->exists())
            <div class="tab-pane fade" id="mensagens" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <livewire:perfil.mensagens />
                    </div>
                </div>
            </div>
        @endif

        <div class="tab-pane fade" id="notificacoes" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <livewire:perfil.notificacoes />
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pedidos" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <livewire:perfil.meus-pedidos />
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="favoritas" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4 text-center py-5">
                    <i class="bi bi-star text-warning" style="font-size: 2rem;"></i>
                    <h5 class="fw-bold text-secondary mt-3 mb-1">Quadras Favoritas</h5>
                    <p class="text-muted small mb-0">Essa funcionalidade ainda não existe — fica planejada para uma próxima versão.</p>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pagamentos" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <livewire:perfil.pagamentos />
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="seguranca" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-secondary mb-1">Segurança da Conta</h5>
                    <p class="text-muted small mb-4">Gerencie sua senha e a autenticação de dois fatores</p>

                    <div class="d-flex flex-column gap-3">
                        <a href="{{ route('user-password.edit') }}" class="card border border-light-subtle shadow-none text-decoration-none" style="border-radius: 15px;">
                            <div class="card-body d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h6 class="mb-1 fw-bold" style="color: #2D3748;">Alterar Senha</h6>
                                    <small class="text-muted">Atualize a senha da sua conta</small>
                                </div>
                                <i class="bi bi-chevron-right text-warning"></i>
                            </div>
                        </a>

                        <a href="{{ route('two-factor.show') }}" class="card border border-light-subtle shadow-none text-decoration-none" style="border-radius: 15px;">
                            <div class="card-body d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <h6 class="mb-1 fw-bold" style="color: #2D3748;">Autenticação de Dois Fatores</h6>
                                    <small class="text-muted">Adicione uma camada extra de segurança à sua conta</small>
                                </div>
                                <i class="bi bi-chevron-right text-warning"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection