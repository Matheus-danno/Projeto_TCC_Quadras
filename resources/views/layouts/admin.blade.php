<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('admin-title', 'Admin') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
    @livewireStyles
</head>
<body class="admin-body">
    <header class="admin-header">
        <div class="admin-header__top">
            <a href="{{ route('dashboard') }}" class="admin-brand" aria-label="AlugaQuadra - painel administrativo">
                <img src="{{ asset('imagens/tela_inicial/Logo.png') }}" alt="AlugaQuadra">
            </a>

            <a href="{{ route('perfil') }}" class="admin-user">
                <span class="admin-user__avatar" aria-hidden="true">
                    <i class="bi bi-person-fill"></i>
                </span>
                <span>{{ auth()->user()->name ?? 'Administrador' }}</span>
            </a>
        </div>

        @hasSection('hide-admin-nav')
        @else
            <nav class="admin-nav" aria-label="Navegação administrativa">
                <div class="admin-nav__inner">
                    <a href="{{ route('dashboard') }}" class="admin-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('dashboard') }}#minhas-quadras" class="admin-nav__link">Minhas Quadras</a>
                    <a href="#" class="admin-nav__link">Reservas</a>
                    <a href="#" class="admin-nav__link">Financeiro</a>
                    <a href="#" class="admin-nav__link">Agendamento Manual</a>
                    <a href="{{ route('admin.configuracoes') }}" class="admin-nav__link {{ request()->routeIs('admin.configuracoes') ? 'is-active' : '' }}">Configurações</a>
                </div>
            </nav>
        @endif
    </header>

    <main class="admin-main">
        @yield('content')
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
