{{-- <x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar> --}}

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Projeto Quadras')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <nav class="navbar">
        <div class="navbar_logo">
            <img src="{{ asset('imagens/tela_inicial/Logo.png') }}" alt="Logo">
        </div>
            <div class="button">
                <a href="{{ route('login') }}" class="navbar_btn_login" style="text-decoration: none;">
                    Entrar
                </a>

                <a href="{{ route('registro') }}" class="navbar_btn_register" style="text-decoration: none;">
                    Cadastrar-se
                </a>

                 <a href="{{ route('perfil') }}" class="navbar_btn_register" style="text-decoration: none;">
                    Meu perfil
                </a>
            </div>
            
    </nav>

    <main>
        @yield('conteudo')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} - Aluga Quadras Online </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
