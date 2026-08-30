<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
                        Ao clicar em Entrar você concorda a <a href="#">Política de Privacidade</a> do AlugaQuadra.
                    </p>
                </div>

                <div class="card-login">
                    <img src="{{ asset('imagens/tela_inicial/bola_marca_dagua.png') }}" alt="" class="login-bola-marca-dagua">

                    <h3 class="login-titulo">Entrar</h3>

                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf

                        <div class="login-input-wrap mb-4">
                            <i class="bi bi-envelope-fill"></i>
                            <input type="email" name="email" value="{{ old('email') }}" class="login-input" placeholder="Digite seu e-mail" required autofocus autocomplete="email">
                        </div>
                        @error('email') <div class="login-erro">{{ $message }}</div> @enderror

                        <div class="login-input-wrap mb-2">
                            <i class="bi bi-lock-fill"></i>
                            <input type="password" name="password" class="login-input" placeholder="Digite sua senha" required autocomplete="current-password">
                        </div>
                        @error('password') <div class="login-erro">{{ $message }}</div> @enderror

                        <a href="{{ route('password.request') }}" class="login-esqueci">Esqueceu sua senha?</a>

                        <button type="submit" class="login-botao">Entrar</button>

                        <a href="{{ route('registro') }}" class="login-criar-conta">Criar uma conta</a>

                        <a href="{{ route('login.dono') }}" class="login-botao-dono">
                            <i class="bi bi-shop"></i> Sou dono de quadra
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
