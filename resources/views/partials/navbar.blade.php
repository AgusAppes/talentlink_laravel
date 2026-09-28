<header class="tl-navbar">
    <div>
        <p class="tl-kicker">Panel</p>
        <h1 class="tl-page-title">@yield('title')</h1>
    </div>
    <div class="tl-user" title="{{ auth()->user()->correo }}">
        <details class="tl-menu">
            <summary class="tl-avatar">{{ auth()->user()->iniciales() }}</summary>
            <div class="tl-menu-lista">
                <a href="{{ route('password.edit') }}">Cambiar contraseña</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Salir</button>
                </form>
            </div>
        </details>
        <div class="tl-user-text">
            <span class="tl-user-name">{{ auth()->user()->nombreVisible() }}</span>
            <span class="tl-user-rol">{{ auth()->user()->rolLegible() }}</span>
        </div>
    </div>
</header>
