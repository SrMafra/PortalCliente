<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <!-- Adicionando uma classe para melhorar o espaçamento superior -->
        <ul class="nav flex-column mt-2">
            <li class="nav-item">
                <!-- Adicionando padding superior adicional ao primeiro item -->
                <a class="nav-link pt-5 {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}"
                    href="{{ route('dashboard') }}" aria-current="page">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>

            <!-- Seção do Cliente -->
            @if(Auth::user()->isCliente())
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'cliente.dados' ? 'active' : '' }}"
                    href="{{ route('cliente.dados') }}">
                    <i class="fas fa-user-circle me-2"></i>
                    Meus Dados
                </a>
            </li>
            @endif

            <!-- Seção Financeiro -->
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>Financeiro</span>
            </h6>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'titulos.em-aberto' ? 'active' : '' }}"
                    href="{{ route('titulos.em-aberto') }}">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Títulos em Aberto
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'titulos.pagos' ? 'active' : '' }}"
                    href="{{ route('titulos.pagos') }}">
                    <i class="fas fa-check-circle me-2"></i>
                    Boletos Pagos
                </a>
            </li>

            <!-- Seção Administrativa (somente para admin) -->
            @if(Auth::user()->isAdmin())
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>Administração</span>
            </h6>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-users me-2"></i>
                    Gerenciar Usuários
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-cog me-2"></i>
                    Configurações
                </a>
            </li>
            @endif
        </ul>

        <!-- Suporte -->
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Suporte</span>
        </h6>

        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="#" target="_blank">
                    <i class="fas fa-question-circle me-2"></i>
                    Ajuda
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#" target="_blank">
                    <i class="fas fa-headset me-2"></i>
                    Contato
                </a>
            </li>
        </ul>
    </div>
</nav>