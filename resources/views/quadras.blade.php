@extends('layouts.app')

@section('titulo', 'Todas as Quadras')

@section('conteudo')
    {{-- Sub-nav com o item "Todas as Quadras" ativo --}}
    <nav class="menu-sub-nav bg-orange text-white py-2">
        <div class="container-fluid d-flex justify-content-center align-items-center gap-3">
            <a href="#" class="nav-item-link-custom"><i class="bi bi-geo-alt"></i> Quadras Próximas</a>
            <a href="{{ route('quadras.index') }}" class="nav-item-link-custom active"><i class="bi bi-layers"></i> Todas as Quadras</a>
            <a href="#" class="nav-item-link-custom"><i class="bi bi-people"></i> Encontre um Time</a>
            <a href="#" class="nav-item-link-custom"><i class="bi bi-box-seam"></i> Criar Sala</a>
            <a href="#" class="nav-item-link-custom"><i class="bi bi-bag"></i> Loja</a>
        </div>
    </nav>

    <div class="container mt-4">
        {{-- Seção de Filtros --}}
        <div class="row mb-4 align-items-center">
            <div class="col-auto"><strong>FILTROS</strong></div>
            <div class="col"><select class="form-select border-orange"><option>Cidade</option></select></div>
            <div class="col"><select class="form-select border-orange"><option>Bairro</option></select></div>
            <div class="col"><select class="form-select border-orange"><option>Esporte</option></select></div>
            <div class="col"><select class="form-select border-orange"><option>Quadra</option></select></div>
            <div class="col"><select class="form-select border-orange"><option>Cobertura</option></select></div>
        </div>

        {{-- Grid de Quadras --}}
        <div class="row g-4">
            @foreach(range(1, 6) as $item) {{-- Simulando 6 cards --}}
            <div class="col-md-6">
                <div class="card card-quadra h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="row g-0">
                        <div class="col-5 position-relative">
                            <img src="{{ asset('imagens/tela_inicial/quadra_volei2.png') }}" class="img-fluid h-100 object-fit-cover" alt="Arena">
                            {{-- Setas de navegação simuladas --}}
                            <button class="btn-arrow left"><i class="bi bi-chevron-left"></i></button>
                            <button class="btn-arrow right"><i class="bi bi-chevron-right"></i></button>
                        </div>
                        <div class="col-7 p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="text-orange fw-bold">Arena Sports</h5>
                                <div class="text-end">
                                    <span class="fs-4 fw-bold">R$ 50,00</span><br>
                                    <span class="badge bg-light-green text-success">Valor Hora</span>
                                </div>
                            </div>
                            
                            <div class="mt-2 small">
                                <p class="mb-1 text-orange fw-bold">Descrição</p>
                                <p class="text-muted mb-2">Quadra de Areia | Descoberta | Bar | Banheiro</p>
                            </div>

                            <div class="mt-3">
                                <p class="mb-0 fw-bold small">5Km de distância de você</p>
                                <p class="text-muted small mb-3"><i class="bi bi-geo-alt"></i> Rua Correia Júnior - Centro</p>
                                
                                <div class="d-flex align-items-center gap-2">
                                    <label class="small">Qtd. de Horas:</label>
                                    <select class="form-select form-select-sm w-auto border-orange">
                                        <option>1</option>
                                    </select>
                                    <button class="btn btn-orange-action btn-sm px-4 rounded-pill text-white fw-bold">Agendar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
@endsection