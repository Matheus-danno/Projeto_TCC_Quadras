<nav class="sub-nav" aria-label="Navegação principal">
    <div class="sub-nav__inner">
        <a href="#" class="sub-nav-link">
            <i class="bi bi-geo-alt"></i>
            <span>Quadras Próximas</span>
        </a>
        <a href="{{ route('quadras.index') }}" class="sub-nav-link {{ request()->routeIs('quadras.index') ? 'active' : '' }}">
            <i class="bi bi-layers"></i>
            <span>Todas as Quadras</span>
        </a>
        <a href="{{ route('encontre_time') }}" class="sub-nav-link {{ request()->routeIs('encontre_time') ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            <span>Encontre um Time</span>
        </a>
        <a href="{{ route('criar_sala') }}" class="sub-nav-link {{ request()->routeIs('criar_sala') ? 'active' : '' }}">
            <i class="bi bi-box"></i>
            <span>Criar Sala</span>
        </a>
        <a href="{{ route('loja') }}" class="sub-nav-link {{ request()->routeIs('loja') ? 'active' : '' }}">
            <i class="bi bi-bag"></i>
            <span>Loja</span>
        </a>
    </div>
</nav>
