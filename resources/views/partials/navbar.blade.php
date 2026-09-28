<header class="tl-navbar">
    <div>
        <p class="tl-kicker">Panel</p>
        <h1 class="tl-page-title">@yield('title')</h1>
    </div>
    <div class="tl-user" title="{{ auth()->user()->correo }}">
        <span class="tl-avatar">{{ auth()->user()->iniciales() }}</span>
        <div class="tl-user-text">
            <span class="tl-user-name">{{ auth()->user()->nombreVisible() }}</span>
            <span class="tl-user-rol">{{ auth()->user()->rolLegible() }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="tl-salir" type="submit">Salir</button>
        </form>
    </div>
</header>
