@extends('layouts.app')

@section('titulo', 'Encontre um Time')

@section('conteudo')
<div class="container-fluid mb-4 bg-laranja-principal">
    <div class="container">
        <div class="d-flex justify-content-center gap-4 py-3 overflow-auto text-nowrap menu-secundario">
            
            <a href="/"><i class="bi bi-house-door me-1"></i> Home</a>
            <a href="#"><i class="bi bi-geo-alt me-1"></i> Quadras Próximas</a>
            <a href="#"><i class="bi bi-layers me-1"></i> Todas as Quadras</a>
            <a href="#" class="ativo"><i class="bi bi-people me-1"></i> Encontre um Time</a>
            <a href="#"><i class="bi bi-plus-circle me-1"></i> Criar Sala</a>
            <a href="#"><i class="bi bi-shop me-1"></i> Loja</a>
            
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4">
        
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm mb-3 card-arredondado">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4">FILTROS</h6>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Esporte</label>
                        <select class="form-select border-secondary-subtle rounded-3">
                            <option>Vôlei</option>
                            <option>Futebol</option>
                            <option>Tênis</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Nível</label>
                        <select class="form-select border-secondary-subtle rounded-3">
                            <option>Intermediário</option>
                            <option>Iniciante</option>
                            <option>Avançado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Distância</label>
                        <select class="form-select border-secondary-subtle rounded-3">
                            <option>5km</option>
                            <option>10km</option>
                            <option>15km</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Horas</label>
                        <select class="form-select border-secondary-subtle rounded-3">
                            <option>20h00</option>
                            <option>19h00</option>
                            <option>21h00</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">Data</label>
                        <input type="date" class="form-control border-secondary-subtle rounded-3" value="2027-08-09">
                    </div>

                    <button class="btn btn-laranja w-100 fw-bold mb-2 shadow-sm">
                        Filtrar
                    </button>
                    <div class="text-center">
                        <a href="#" class="text-muted text-decoration-none small">Limpar Filtros</a>
                    </div>
                </div>
            </div>

            <button class="btn btn-laranja w-100 fw-bold py-3 mb-2 shadow-sm d-flex flex-column align-items-center justify-content-center card-arredondado">
                Encontrar uma sala próxima de mim!
                <i class="bi bi-geo-alt fs-5 mt-1"></i>
            </button>
            <div class="mapa-container shadow-sm">
                <img src="https://via.placeholder.com/400x300?text=Mapa" alt="Mapa">
            </div>
        </div>

        <div class="col-lg-9">
            <div class="d-flex flex-column gap-3">

                <div class="card border-0 shadow-sm position-relative card-partida">
                    <span class="badge-destaque">
                        <i class="bi bi-star-fill me-1"></i> DESTAQUE
                    </span>
                    <div class="card-body p-4 d-flex flex-column flex-md-row gap-4 align-items-md-center">
                        <div class="icone-esporte bg-warning">
                            <i class="bi bi-dribbble text-white fs-1"></i> 
                        </div>
                        
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-1 texto-escuro">Vôlei Areia - Intermediário</h5>
                            <p class="text-muted small mb-1"><i class="bi bi-geo-alt text-warning me-1"></i> Saquarema Beach</p>
                            <p class="text-muted small mb-2"><i class="bi bi-clock text-warning me-1"></i> Hoje, 20:00 - 21:30</p>
                            
                            <div class="d-flex gap-2 mb-2">
                                <span class="badge bg-info text-white rounded-pill px-3 py-2">8/15 vagas</span>
                                <span class="badge bg-warning text-white rounded-pill px-3 py-2">52% - 28min</span>
                            </div>

                            <div class="fw-bold mb-1">R$8,00 / pessoa</div>
                            <div class="text-muted small d-flex align-items-center gap-1">
                                Administrador: João Silva <span class="text-warning"><i class="bi bi-star-fill"></i> 4.8</span>
                            </div>
                        </div>

                        <div class="d-flex flex-column align-items-md-end justify-content-between h-100">
                            <div class="grupo-avatares mb-3 mb-md-0">
                                <img src="https://i.pravatar.cc/150?img=11" alt="Jogador">
                                <img src="https://i.pravatar.cc/150?img=12" alt="Jogador">
                                <div class="avatar-extra">+1</div>
                            </div>
                            <button class="btn btn-outline-laranja fw-bold px-4 mt-auto rounded-pill">Ver Detalhes</button>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm card-partida">
                    <div class="card-body p-4 d-flex flex-column flex-md-row gap-4 align-items-md-center">
                        <div class="icone-esporte bg-info">
                            <i class="bi bi-vinyl text-white fs-1"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-1 texto-escuro">Futebol - Iniciante</h5>
                            <p class="text-muted small mb-1"><i class="bi bi-geo-alt text-warning me-1"></i> Bom da Bola - Vila Souto</p>
                            <p class="text-muted small mb-2"><i class="bi bi-clock text-warning me-1"></i> Hoje, 20:00 - 21:30</p>
                            
                            <div class="d-flex gap-2 mb-2">
                                <span class="badge bg-info text-white rounded-pill px-3 py-2">13/22 vagas</span>
                                <span class="badge bg-warning text-white rounded-pill px-3 py-2">52% - 28min</span>
                            </div>

                            <div class="fw-bold mb-1">R$12,00 / pessoa</div>
                            <div class="text-muted small d-flex align-items-center gap-1">
                                Administrador: Pedro <span class="text-warning"><i class="bi bi-star-fill"></i> 4.9</span>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-md-end justify-content-between h-100">
                            <div class="grupo-avatares mb-3 mb-md-0">
                                <img src="https://i.pravatar.cc/150?img=33" alt="Jogador">
                                <img src="https://i.pravatar.cc/150?img=44" alt="Jogador">
                                <div class="avatar-extra">+1</div>
                            </div>
                            <button class="btn btn-outline-laranja fw-bold px-4 mt-auto rounded-pill">Ver Detalhes</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection