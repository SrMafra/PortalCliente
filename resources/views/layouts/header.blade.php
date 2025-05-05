<header class="navbar navbar-dark sticky-top bg-primary flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="{{ route('dashboard') }}">
        <i class="fas fa-building me-2"></i> Portal do Cliente
    </a>
    
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="w-100 d-none d-md-block"></div>
    
    <div class="navbar-nav">
        <div class="nav-item text-nowrap">
            @auth
                <span class="nav-link px-5 text-white d-none d-md-inline-block">
                    <i class="fas fa-user me-1"></i> Olá, {{ Auth::user()->name }}
                </span>
                
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="nav-link px-5 bg-transparent border-0">
                        <i class="fas fa-sign-out-alt me-1"></i> Sair
                    </button>
                </form>
            @else
                <a class="nav-link px-3" href="{{ route('login') }}">
                    <i class="fas fa-sign-in-alt me-1"></i> Entrar
                </a>
            @endauth
        </div>
    </div>
</header>