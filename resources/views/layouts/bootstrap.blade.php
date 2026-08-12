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
            <div class="button">
                @guest
                    <a href="{{ route('login') }}" class="navbar_btn_login" style="text-decoration: none;">
                        Entrar
                    </a>

                    <a href="{{ route('registro') }}" class="navbar_btn_register" style="text-decoration: none;">
                        Cadastrar-se
                    </a>
                @else
                    <a href="{{ route('perfil') }}" class="navbar_btn_register" style="text-decoration: none;">
                        Meu perfil
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="navbar_btn_login border-0" style="text-decoration: none;">
                            Sair
                        </button>
                    </form>
                @endguest
            </div>

    </nav>

    <main>
        @yield('conteudo')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} - {{ config('app.name') }}</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
