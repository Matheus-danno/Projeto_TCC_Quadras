@extends('layouts.bootstrap')

@section('titulo', 'Encontre sua Quadra')


@section('conteudo')

@include('partials.sub-nav')

    <main class="principal">
        <section class="principal_section1">
            <h2>Encontre a quadra mais próxima de você.</h2>
            <p>Seu jogo começa aqui! Você escolhe, a gente conecta.</p>
            <nav class="busca_quadra">
                <div class="container-fluid">
                    <h6>Buscar Quadras</h6>
                    <form class="d-flex" role="search" action="{{ route('quadras.index') }}" method="GET">
                        <input class="form-control me-2" type="search" name="busca" placeholder="Digite sua localização ou bairro" aria-label="Buscar" />
                        <button class="btn btn-outline-success" type="submit">Pesquisar</button>
                    </form>
                    <button
                        class="btn btn-location"
                        type="button"
                        x-data
                        @click="
                            navigator.geolocation.getCurrentPosition(
                                (posicao) => { window.location.href = '{{ route('quadras.index') }}?lat=' + posicao.coords.latitude + '&lng=' + posicao.coords.longitude; },
                                () => alert('Não foi possível obter sua localização.')
                            )
                        "
                    >
                        <img src="{{ asset('imagens/tela_inicial/localizacao.png') }}" alt="">
                        Usar minha localização
                    </button>
                </div>
            </nav>
        </section>

        <section class="principal_section2">
            <div id="carouselExampleSlidesOnly" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="{{ asset('imagens\tela_inicial\quadra_volei2.png') }}" class="d-block w-100" alt="Vôlei">
                    </div>
                    <div class="carousel-item">
                        <img src="{{ asset('imagens/tela_inicial/quadra_tenis.png') }}" class="d-block w-100" alt="Tênis">
                    </div>
                    <div class="carousel-item">
                        <img src="{{ asset('imagens/tela_inicial/quadra_futebol.png') }}" class="d-block w-100" alt="Futebol">
                    </div>
                </div>
            </div>
        </section>
    </main>

    <section class="principal_section3">
        <div class="principal_section3_title">
            <h4>Por que nos escolher?</h4>
        </div>

        <div class="principal_section3_columns">
            <div>
                <img src="{{ asset('imagens\tela_inicial\relogio.png') }}" alt="">
                <h6>Reserva 24/7</h6>
                <p>Reserve sua quadra a qualquer hora do dia, todos os dias da semana.</p>
            </div>
            <div>
                <img src="{{ asset('imagens/tela_inicial/escudo.png') }}" alt="">
                <h6>Pagamento Seguro</h6>
                <p>Transações protegidas com os melhores sistemas de segurança.</p>
            </div>
            <div>
                <img src="{{ asset('imagens/tela_inicial/check.png') }}" alt="">
                <h6>Quadras Verificadas</h6>
                <p>Todas as quadras são inspecionadas e aprovadas pela nossa equipe.</p>
            </div>
            <div>
                <img src="{{ asset('imagens/tela_inicial/cancelamento.png') }}" alt="">
                <h6>Cancelamento Flexível</h6>
                <p>Cancele ou reagende suas reservas facilmente quando necessário.</p>
            </div>
        </div>
    </section>
@endsection