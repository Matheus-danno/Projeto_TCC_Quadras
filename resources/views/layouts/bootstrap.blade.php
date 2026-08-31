<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @livewireStyles
</head>
<body>
    <nav class="navbar">
        <div class="navbar_logo">
            <img src="{{ asset('imagens/tela_inicial/Logo.png') }}" alt="Logo">
        </div>

        @auth
            @if (request()->routeIs('quadras.index'))
                <form action="{{ route('quadras.index') }}" method="GET" class="navbar_busca">
                    <i class="bi bi-search"></i>
                    <input type="search" name="busca" value="{{ request('busca') }}" placeholder="Pesquisar quadra" class="navbar_busca_input">
                </form>
            @endif

            <div class="navbar_icones">
                <a href="{{ route('perfil') }}#ajuda" class="navbar_icone-btn" title="Ajuda">
                    <i class="bi bi-question-circle"></i>
                </a>
                <a href="{{ route('carrinho.index') }}" class="navbar_icone-btn" title="Carrinho">
                    <i class="bi bi-cart3"></i>
                </a>
                <a href="{{ route('perfil') }}" class="navbar_icone-usuario" title="Minha conta">
                    <i class="bi bi-person-fill"></i>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="navbar_icone-btn border-0 bg-transparent p-0" title="Sair">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        @else
            <div class="button">
                <a href="{{ route('login') }}" class="navbar_btn_login" style="text-decoration: none;">
                    Entrar
                </a>

                <a href="{{ route('registro') }}" class="navbar_btn_register" style="text-decoration: none;">
                    Cadastrar-se
                </a>
            </div>
        @endauth

    </nav>

    <main>
        @yield('conteudo')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} - {{ config('app.name') }}</p>
    </footer>

    @guest
        <x-modal-login-necessario />
    @endguest

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
