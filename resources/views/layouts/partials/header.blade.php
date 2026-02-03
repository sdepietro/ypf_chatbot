<header class="header header-sticky p-0 mb-4">
    <div class="container-fluid border-bottom px-4">
        <button class="header-toggler" type="button"
                onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()"
                style="margin-inline-start: -14px;">
            <i class="fas fa-bars icon icon-lg"></i>
        </button>
        <ul class="header-nav ms-auto">
            {{-- Theme Switcher --}}
            <li class="nav-item dropdown">
                <button class="btn btn-link nav-link py-2 px-2 dropdown-toggle d-flex align-items-center"
                        id="bd-theme" type="button" aria-expanded="false"
                        data-coreui-toggle="dropdown" data-coreui-display="static">
                    <i class="fas fa-circle-half-stroke theme-icon-active"></i>
                    <span class="d-lg-none ms-2" id="bd-theme-text">Toggle theme</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme">
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center"
                                data-bs-theme-value="light">
                            <i class="fas fa-sun me-2 opacity-50"></i>
                            Claro
                            <i class="fas fa-check ms-auto d-none"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center"
                                data-bs-theme-value="dark">
                            <i class="fas fa-moon me-2 opacity-50"></i>
                            Oscuro
                            <i class="fas fa-check ms-auto d-none"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center active"
                                data-bs-theme-value="auto">
                            <i class="fas fa-circle-half-stroke me-2 opacity-50"></i>
                            Auto
                            <i class="fas fa-check ms-auto d-none"></i>
                        </button>
                    </li>
                </ul>
            </li>
        </ul>
        <ul class="header-nav">
            {{-- User Dropdown --}}
            <li class="nav-item dropdown">
                <a class="nav-link py-0 pe-0" data-coreui-toggle="dropdown" href="#" role="button"
                   aria-haspopup="true" aria-expanded="false">
                    <div class="avatar avatar-md">
                        <i class="fas fa-user-circle fa-2x text-body-secondary"></i>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end pt-0">
                    <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold rounded-top mb-2">
                        <div class="fw-semibold">Usuario</div>
                    </div>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-user me-2"></i> Perfil
                    </a>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-cog me-2"></i> Configuracion
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline w-100">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesion
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
    <div class="container-fluid px-4">
        {{-- Breadcrumb (optional) --}}
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb my-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('chat.index') }}">Home</a>
                </li>
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @endif
            </ol>
        </nav>
    </div>
</header>
