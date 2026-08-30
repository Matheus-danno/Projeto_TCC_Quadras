<nav class="sub-nav">
    <div class="container-fluid d-flex justify-content-center align-items-center gap-4 py-1 flex-wrap">
        <a href="{{ route('home') }}" class="sub-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="bi bi-house-door"></i> Home
        </a>
        <a href="{{ route('quadras.proximas') }}" class="sub-nav-link {{ request()->routeIs('quadras.proximas') ? 'active' : '' }}">
            <i class="bi bi-geo-alt"></i> Quadras Próximas
        </a>
        <a href="{{ route('quadras.index') }}" class="sub-nav-link {{ request()->routeIs('quadras.index') ? 'active' : '' }}">
            <i class="bi bi-layers"></i> Todas as Quadras
        </a>
        <a href="{{ route('encontre_time') }}" class="sub-nav-link {{ request()->routeIs('encontre_time') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Encontre um Time
        </a>
        @auth
            <a href="{{ route('criar_sala') }}" class="sub-nav-link {{ request()->routeIs('criar_sala') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> Criar Sala
            </a>
        @else
            <a href="#" class="sub-nav-link" data-bs-toggle="modal" data-bs-target="#modalLoginNecessario" data-mensagem="Você precisa entrar para criar uma sala.">
                <i class="bi bi-plus-circle"></i> Criar Sala
            </a>
        @endauth
        <a href="{{ route('loja') }}" class="sub-nav-link {{ request()->routeIs('loja') || request()->routeIs('loja.*') ? 'active' : '' }}">
            <i class="bi bi-shop"></i> Loja
        </a>
    </div>
</nav>
