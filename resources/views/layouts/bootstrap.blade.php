<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-shell.css') }}">
    @stack('styles')
    @livewireStyles
</head>
<body>
    <header class="site-header">
        <div class="site-header__inner">
            <a href="{{ route('home') }}" class="site-brand" aria-label="AlugaQuadra - início">
                <img src="{{ asset('imagens/tela_inicial/Logo.png') }}" alt="AlugaQuadra">
            </a>

            <div class="site-search" role="search" aria-label="Pesquisar quadra">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" placeholder="Pesquisar quadra" aria-label="Pesquisar quadra">
            </div>

            <nav class="site-actions" aria-label="Ações da conta">
                <button class="site-action" type="button" title="Ajuda" aria-label="Ajuda">
                    <i class="bi bi-question-circle"></i>
                </button>
                <a href="{{ route('loja') }}" class="site-action" title="Loja" aria-label="Loja">
                    <i class="bi bi-cart3"></i>
                </a>

                @guest
                    <a href="{{ route('login') }}" class="site-profile" title="Entrar" aria-label="Entrar">
                        <i class="bi bi-person-fill"></i>
                    </a>
                @else
                    <a href="{{ route('perfil') }}" class="site-profile" title="Meu perfil" aria-label="Meu perfil">
                        <i class="bi bi-person-fill"></i>
                    </a>
                @endguest
            </nav>
        </div>
    </header>

    <main>
        @yield('conteudo')
    </main>

    <footer class="site-footer">
        <div>
            <strong>AlugaQuadra</strong>
            <span>&copy; {{ date('Y') }} - {{ config('app.name') }}</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    @livewireScripts
</body>
</html>
