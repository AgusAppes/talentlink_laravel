<aside class="offcanvas-lg offcanvas-start tl-sidebar flex-shrink-0" tabindex="-1" id="menuLateral" aria-label="Menú principal">
    <div class="offcanvas-header">
        <span class="offcanvas-title">Menú</span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#menuLateral" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-3">
        <a class="d-block mb-3 p-2 rounded bg-white bg-opacity-10" href="{{ route(auth()->user()->rutaInicio()) }}">
            <img class="img-fluid rounded" src="{{ asset('images/logo.jpg') }}" alt="TalentLink">
        </a>

        <p class="small text-uppercase text-white-50 px-2 mb-1">Principal</p>
        <nav class="nav flex-column gap-1">
            @if (auth()->user()->puede('dashboard.ver'))
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            @endif

            @if (auth()->user()->puede('solicitudes.ver'))
                <a href="{{ route('solicitudes.index') }}" class="nav-link {{ request()->routeIs('solicitudes.*') ? 'active' : '' }}">Solicitudes</a>
            @endif

            @if (auth()->user()->esEmpresa() && auth()->user()->puede('solicitudes.crear'))
                <a href="{{ route('solicitudes.index') }}" class="nav-link {{ request()->routeIs('solicitudes.index') ? 'active' : '' }}">Mis solicitudes</a>
            @endif

            @if (auth()->user()->esEmpresa() && auth()->user()->puede('solicitudes.crear'))
                <a href="{{ route('solicitudes.create') }}" class="nav-link {{ request()->routeIs('solicitudes.create') ? 'active' : '' }}">Nueva solicitud</a>
            @endif

            @if (auth()->user()->puede('ofertas.ver'))
                <a href="{{ route('ofertas.index') }}" class="nav-link {{ request()->routeIs('ofertas.*') ? 'active' : '' }}">
                    {{ auth()->user()->esCandidato() ? 'Ofertas disponibles' : 'Ofertas laborales' }}
                </a>
            @endif

            @if (auth()->user()->puede('candidatos.ver'))
                <a href="{{ route('candidatos.index') }}" class="nav-link {{ request()->routeIs('candidatos.index', 'candidatos.show') ? 'active' : '' }}">Candidatos</a>
            @endif

            @if (auth()->user()->puede('postulaciones.ver'))
                <a href="{{ route('postulaciones.index') }}" class="nav-link {{ request()->routeIs('postulaciones.*') ? 'active' : '' }}">
                    {{ auth()->user()->esCandidato() ? 'Mis postulaciones' : 'Postulaciones' }}
                </a>
            @endif

            @if (auth()->user()->esCandidato() && auth()->user()->puede('candidatos.editar'))
                <a href="{{ route('candidatos.perfil') }}" class="nav-link {{ request()->routeIs('candidatos.perfil') ? 'active' : '' }}">Mi perfil</a>
            @endif

            @if (auth()->user()->esEmpresa() && auth()->user()->puede('empresas.editar'))
                <a href="{{ route('empresas.perfil') }}" class="nav-link {{ request()->routeIs('empresas.perfil') ? 'active' : '' }}">Mi empresa</a>
            @endif
        </nav>

        @if (auth()->user()->puede('usuarios.ver'))
            <p class="small text-uppercase text-white-50 px-2 mt-3 mb-1">Reportes</p>
            <nav class="nav flex-column gap-1">
                <a href="{{ route('usuarios.index') }}" class="nav-link {{ request()->routeIs('usuarios.index', 'usuarios.create') ? 'active' : '' }}">Usuarios</a>
                <a class="nav-link ps-4 {{ request()->routeIs('usuarios.roles', 'usuarios.roles.edit') ? 'active' : '' }}" href="{{ route('usuarios.roles') }}">Roles y permisos</a>
            </nav>
        @endif
    </div>
</aside>
