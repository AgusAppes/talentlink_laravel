<aside class="tl-sidebar">
    <a class="tl-logo" href="{{ route(auth()->user()->rutaInicio()) }}">
        <img src="{{ asset('images/logo.jpg') }}" alt="TalentLink">
    </a>

    <p class="tl-section">Principal</p>
    <nav class="tl-nav">
        @if (auth()->user()->puede('dashboard.ver'))
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
        @endif

        @if (auth()->user()->puede('solicitudes.ver'))
            <a href="{{ route('solicitudes.index') }}" class="{{ request()->routeIs('solicitudes.*') ? 'active' : '' }}">Solicitudes</a>
        @endif

        @if (auth()->user()->esEmpresa() && auth()->user()->puede('solicitudes.crear'))
            <a href="{{ route('solicitudes.index') }}" class="{{ request()->routeIs('solicitudes.index') ? 'active' : '' }}">Mis solicitudes</a>
        @endif

        @if (auth()->user()->esEmpresa() && auth()->user()->puede('solicitudes.crear'))
            <a href="{{ route('solicitudes.create') }}" class="{{ request()->routeIs('solicitudes.create') ? 'active' : '' }}">Nueva solicitud</a>
        @endif

        @if (auth()->user()->puede('ofertas.ver') && ! auth()->user()->esEmpresa())
            <a href="{{ route('ofertas.index') }}" class="{{ request()->routeIs('ofertas.*') ? 'active' : '' }}">
                {{ auth()->user()->esCandidato() ? 'Ofertas disponibles' : 'Ofertas laborales' }}
            </a>
        @endif

        @if (auth()->user()->puede('candidatos.ver'))
            <a href="{{ route('candidatos.index') }}" class="{{ request()->routeIs('candidatos.index', 'candidatos.show') ? 'active' : '' }}">Candidatos</a>
        @endif

        @if (auth()->user()->puede('postulaciones.ver') && ! auth()->user()->esEmpresa())
            <a href="{{ route('postulaciones.index') }}" class="{{ request()->routeIs('postulaciones.*') ? 'active' : '' }}">
                {{ auth()->user()->esCandidato() ? 'Mis postulaciones' : 'Postulaciones' }}
            </a>
        @endif

        @if (auth()->user()->esCandidato() && auth()->user()->puede('candidatos.editar'))
            <a href="{{ route('candidatos.perfil') }}" class="{{ request()->routeIs('candidatos.perfil') ? 'active' : '' }}">Mi perfil</a>
        @endif

        @if (auth()->user()->esEmpresa() && auth()->user()->puede('empresas.editar'))
            <a href="{{ route('empresas.perfil') }}" class="{{ request()->routeIs('empresas.perfil') ? 'active' : '' }}">Mi empresa</a>
        @endif
    </nav>

    @if (auth()->user()->puede('usuarios.ver'))
        <p class="tl-section">Reportes</p>
        <nav class="tl-nav">
            <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">Usuarios</a>
        </nav>
    @endif
</aside>
