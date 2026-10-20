<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - {{ config('app.name') }}</title>
    @vite(['resources/css/site.css', 'resources/js/site.js'])
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="tela-login">
    <div class="login-arcos login-arcos-topo">
        <span></span><span></span><span></span><span></span>
    </div>
    <div class="login-arcos login-arcos-base">
        <span></span><span></span><span></span><span></span>
    </div>

    <div class="container-fluid">
        <div class="login-page-row d-flex justify-content-center align-items-center">
            <div class="login-grupo d-flex align-items-center flex-wrap justify-content-center">
                <div class="login-coluna-logo text-center text-lg-start">
                    <a href="{{ route('home') }}" class="d-inline-block text-decoration-none mb-4">
                        <img src="{{ asset('imagens/tela_inicial/logo_login.png') }}" alt="AlugaQuadra" class="login-logo-img">
                    </a>
                    <p class="login-legal mx-auto mx-lg-0">
                        Ao clicar em Entrar você concorda a <a href="{{ route('politica.privacidade') }}" target="_blank">Política de Privacidade</a> do AlugaQuadra.
                    </p>
                </div>

                <div class="card-login">
                    <img src="{{ asset('imagens/tela_inicial/bola_marca_dagua.png') }}" alt="" class="login-bola-marca-dagua">

                    <h3 class="login-titulo">Entrar</h3>

                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf
                        <input type="hidden" name="contexto" value="jogador">

                        <div class="login-input-wrap mb-4">
                            <i class="bi bi-envelope-fill"></i>
                            <input type="email" name="email" value="{{ old('email') }}" class="login-input" placeholder="Digite seu e-mail" required autofocus autocomplete="email">
                        </div>
                        @error('email') <div class="login-erro">{{ $message }}</div> @enderror

                        <x-login-senha />
                        @error('password') <div class="login-erro">{{ $message }}</div> @enderror

                        <a href="{{ route('password.request') }}" class="login-esqueci">Esqueceu sua senha?</a>

                        <button type="submit" class="login-botao">Entrar</button>

                        <a href="{{ route('registro') }}" class="login-criar-conta">Criar uma conta</a>

                        <a href="{{ route('login.dono') }}" class="login-botao-dono">
                            <i class="bi bi-shop"></i> Sou dono de quadra
                        </a>

                        <a href="{{ route('login.admin') }}" class="login-link-admin">
                            Sou administrador
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
