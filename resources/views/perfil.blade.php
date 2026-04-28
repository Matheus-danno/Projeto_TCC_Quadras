@extends('layouts.app')

@section('titulo', 'Meu Perfil')

@section('conteudo')

<style>
    .custom-tabs .nav-link {
        color: #6c757d;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 50rem; /* Formato de pílula */
        padding: 0.5rem 1.5rem;
        font-weight: bold;
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
                    <img src="https://i.pravatar.cc/150?u=joaosilva" alt="Foto de Perfil" class="rounded-circle border border-4 border-warning" style="width: 130px; height: 130px; object-fit: cover;">
                    <button class="btn btn-warning btn-sm position-absolute bottom-0 end-0 rounded-circle shadow d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <i class="bi bi-pencil-fill text-white"></i>
                    </button>
                </div>

                <div class="flex-grow-1">
                    <h2 class="fw-bold mb-1" style="color: #2D3748;">João Silva</h2>
                    <div class="d-flex flex-wrap gap-3 text-secondary mt-2">
                        <span><i class="bi bi-envelope me-1" style="color: #FF8C00;"></i> joao.silva@email.com</span>
                        <span><i class="bi bi-telephone me-1" style="color: #FF8C00;"></i> (11) 98765-4321</span>
                        <span><i class="bi bi-geo-alt me-1" style="color: #FF8C00;"></i> São Paulo, SP</span>
                        <span><i class="bi bi-calendar-event me-1" style="color: #FF8C00;"></i> Membro desde Janeiro 2024</span>
                    </div>
                    <button class="btn mt-3 text-white px-4 shadow-sm" style="background-color: #FF8C00; border-radius: 10px; font-weight: bold;">
                        <i class="bi bi-pencil me-1"></i> Editar Perfil
                    </button>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav custom-tabs gap-2 mb-4 flex-nowrap overflow-auto pb-2 border-0" id="perfilTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="reservas-tab" data-bs-toggle="tab" data-bs-target="#reservas" type="button" role="tab">
                <i class="bi bi-clock-history me-1"></i> Minhas Reservas
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
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-secondary mb-1">Histórico de Reservas</h5>
                    <p class="text-muted small mb-4">Confira todas as suas reservas anteriores e futuras</p>

                    <div class="d-flex flex-column gap-3">
                        <div class="card border border-light-subtle shadow-none" style="border-radius: 15px;">
                            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <h6 class="fw-bold mb-1" style="color: #2D3748;">Quadra Arena Sports</h6>
                                    <div class="text-muted small">
                                        <span class="me-2"><i class="bi bi-calendar-check me-1 text-warning"></i> 15/03/2026</span>
                                        <span class="me-2">• 18:00 - 19:00</span>
                                        <span>• Futebol</span>
                                    </div>
                                </div>
                                <div class="text-md-end d-flex flex-column align-items-md-end align-items-start">
                                    <span class="badge rounded-pill bg-success text-white px-3 mb-2">confirmada</span>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="fw-bold fs-5" style="color: #FF8C00;">R$ 120,00</span>
                                        <button class="btn btn-outline-warning btn-sm px-3 rounded-pill fw-bold" style="border-color: #FF8C00; color: #FF8C00;">Ver Detalhes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="favoritas" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-secondary mb-1">Quadras Favoritas</h5>
                    <p class="text-muted small mb-4">Suas quadras preferidas para acesso rápido</p>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card h-100 border-light-subtle" style="border-radius: 15px;">
                                <div class="card-body">
                                    <h6 class="fw-bold" style="color: #2D3748;">Arena Sports Complex</h6>
                                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt text-warning me-1"></i> Vila Mariana, SP</p>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-secondary small">Futebol</span>
                                        <span class="text-warning fw-bold small"><i class="bi bi-star-fill"></i> 4.8</span>
                                    </div>
                                    <button class="btn w-100 text-white fw-bold" style="background-color: #FF8C00; border-radius: 10px;">Fazer Reserva</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card h-100 border-light-subtle" style="border-radius: 15px;">
                                <div class="card-body">
                                    <h6 class="fw-bold" style="color: #2D3748;">Beach Club Ipanema</h6>
                                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt text-warning me-1"></i> Ipanema, RJ</p>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-secondary small">Vôlei</span>
                                        <span class="text-warning fw-bold small"><i class="bi bi-star-fill"></i> 4.9</span>
                                    </div>
                                    <button class="btn w-100 text-white fw-bold" style="background-color: #FF8C00; border-radius: 10px;">Fazer Reserva</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card h-100 border-light-subtle" style="border-radius: 15px;">
                                <div class="card-body">
                                    <h6 class="fw-bold" style="color: #2D3748;">Quadra Central Tennis</h6>
                                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt text-warning me-1"></i> Jardins, SP</p>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-secondary small">Tênis</span>
                                        <span class="text-warning fw-bold small"><i class="bi bi-star-fill"></i> 4.7</span>
                                    </div>
                                    <button class="btn w-100 text-white fw-bold" style="background-color: #FF8C00; border-radius: 10px;">Fazer Reserva</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pagamentos" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-secondary mb-1">Métodos de Pagamento</h5>
                    <p class="text-muted small mb-4">Gerencie seus cartões e formas de pagamento</p>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="card border border-light-subtle shadow-none" style="border-radius: 10px;">
                            <div class="card-body d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary text-white px-2 py-1 rounded fw-bold" style="font-size: 0.8rem;">VISA</div>
                                    <div>
                                        <div class="fw-bold text-secondary">**** **** **** 4532</div>
                                        <div class="text-muted small">Expira em 12/2027</div>
                                    </div>
                                </div>
                                <span class="badge bg-success rounded-pill px-3 py-2">Principal</span>
                            </div>
                        </div>

                        <div class="card border border-light-subtle shadow-none" style="border-radius: 10px;">
                            <div class="card-body d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-danger text-white px-2 py-1 rounded fw-bold" style="font-size: 0.8rem;">MASTER</div>
                                    <div>
                                        <div class="fw-bold text-secondary">**** **** **** 8765</div>
                                        <div class="text-muted small">Expira em 08/2026</div>
                                    </div>
                                </div>
                                <button class="btn btn-link text-muted p-0 text-decoration-none">Remover</button>
                            </div>
                        </div>
                    </div>

                    <button class="btn w-100 py-3 fw-bold" style="border: 2px dashed #FF8C00; color: #FF8C00; background-color: #fff; border-radius: 10px;">
                        + Adicionar Novo Cartão
                    </button>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="seguranca" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    
                    <h5 class="fw-bold text-secondary mb-1">Segurança da Conta</h5>
                    <p class="text-muted small mb-4">Mantenha sua conta segura</p>

                    <h6 class="fw-bold text-secondary mb-3">Alterar Senha</h6>
                    <form action="#" class="mb-4 pb-4 border-bottom">
                        <div class="mb-3">
                            <input type="password" class="form-control rounded-pill border-secondary-subtle" placeholder="Senha atual">
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control rounded-pill border-secondary-subtle" placeholder="Nova senha">
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control rounded-pill border-secondary-subtle" placeholder="Confirmar nova senha">
                        </div>
                        <button type="submit" class="btn text-white fw-bold px-4 rounded-pill" style="background-color: #FF8C00;">
                            Atualizar Senha
                        </button>
                    </form>

                    <h6 class="fw-bold text-secondary mb-1">Autenticação de Dois Fatores</h6>
                    <p class="text-muted small mb-3">Adicione uma camada extra de segurança à sua conta</p>
                    <button class="btn bg-white fw-bold px-4 rounded-pill mb-4 pb-4 border-bottom w-100 text-start d-inline-block" style="border: 1px solid #FF8C00; color: #FF8C00; max-width: max-content;">
                        Ativar 2FA
                    </button>

                    <h6 class="fw-bold text-secondary mb-1 mt-4">Sessões Ativas</h6>
                    <p class="text-muted small mb-3">Gerencie os dispositivos onde você está conectado</p>
                    
                    <div class="card border-0 bg-light rounded shadow-none">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold" style="color: #2D3748;">Chrome - Windows</h6>
                                <small class="text-muted">São Paulo, Brasil • Ativo agora</small>
                            </div>
                            <span class="badge bg-success rounded-pill px-3 py-2">Atual</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

@endsection