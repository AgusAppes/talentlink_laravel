<header class="d-flex align-items-center justify-content-between gap-3 px-3 px-lg-4 py-3 bg-white border-bottom">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-primary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral" aria-controls="menuLateral">Menú</button>
        <div>
            <p class="small text-secondary mb-0">Panel</p>
            <h1 class="h5 mb-0">@yield('title')</h1>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2" title="{{ auth()->user()->correo }}">
        <div class="dropdown">
            <button class="tl-avatar" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú de usuario">{{ auth()->user()->iniciales() }}</button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('password.edit') }}">Cambiar contraseña</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">Salir</button>
                    </form>
                </li>
            </ul>
        </div>
        <div class="d-none d-sm-flex flex-column lh-sm">
            <span class="fw-semibold small">{{ auth()->user()->nombreVisible() }}</span>
            <span class="text-secondary small">{{ auth()->user()->rolLegible() }}</span>
        </div>
    </div>
</header>
