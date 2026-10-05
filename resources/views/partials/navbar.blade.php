<header class="d-flex align-items-center justify-content-between gap-3 px-3 px-lg-4 py-3 bg-white border-bottom">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-primary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral" aria-controls="menuLateral">Menú</button>
        <div>
            <p class="small text-secondary mb-0">Panel</p>
            <h1 class="h5 mb-0">@yield('title')</h1>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2" title="{{ auth()->user()->correo }}">
        <div class="dropdown tl-campana">
            <button class="btn btn-link {{ $notificaciones->isNotEmpty() ? 'text-primary' : 'text-secondary' }} text-decoration-none position-relative p-1 lh-1 me-2" id="campana" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notificaciones">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                </svg>
                <span id="campana-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger {{ $notificaciones->isEmpty() ? 'd-none' : '' }}">{{ $notificaciones->count() }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end overflow-hidden p-0" id="campana-lista">
                @forelse ($notificaciones as $notificacion)
                    <li class="border-bottom"><span class="dropdown-item-text small text-wrap py-2 lh-sm" data-aviso>{{ $notificacion->texto }}</span></li>
                @empty
                    <li><span class="dropdown-item-text small text-secondary py-2">No tenés notificaciones.</span></li>
                @endforelse
                @if ($notificaciones->isNotEmpty())
                    <li>
                        <form method="POST" action="{{ route('notificaciones.leer') }}">
                            @csrf
                            <button class="dropdown-item text-center small text-primary py-2" type="submit">Marcar leídas</button>
                        </form>
                    </li>
                @endif
            </ul>
        </div>
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

@push('scripts')
    <script>
        // Esta función pide las notificaciones y actualiza la campana
        // fetch consulta /notificaciones y el JSON trae los textos no leídos
        // Si la pestaña no está a la vista, no consulta
        function actualizarNotificaciones() {
            if (document.hidden) {
                return;
            }

            fetch('{{ route('notificaciones.index') }}', { headers: { 'Accept': 'application/json' } })
                .then(function (respuesta) { return respuesta.json(); })
                .then(function (datos) {
                    var textos = datos.textos || [];
                    var lista = document.getElementById('campana-lista');
                    var actuales = Array.from(lista.querySelectorAll('[data-aviso]')).map(function (elemento) {
                        return elemento.textContent;
                    });

                    if (actuales.length === textos.length && actuales.every(function (texto, i) {
                        return texto === textos[i];
                    })) {
                        return;
                    }

                    var boton = document.getElementById('campana');
                    var badge = document.getElementById('campana-badge');

                    boton.classList.toggle('text-primary', textos.length > 0);
                    boton.classList.toggle('text-secondary', textos.length === 0);
                    badge.textContent = textos.length;
                    badge.classList.toggle('d-none', textos.length === 0);

                    lista.replaceChildren();

                    if (textos.length === 0) {
                        var vacio = document.createElement('li');
                        var avisoVacio = document.createElement('span');
                        avisoVacio.className = 'dropdown-item-text small text-secondary py-2';
                        avisoVacio.textContent = 'No tenés notificaciones.';
                        vacio.appendChild(avisoVacio);
                        lista.appendChild(vacio);
                        return;
                    }

                    textos.forEach(function (texto) {
                        var item = document.createElement('li');
                        item.className = 'border-bottom';
                        var aviso = document.createElement('span');
                        aviso.className = 'dropdown-item-text small text-wrap py-2 lh-sm';
                        aviso.dataset.aviso = '';
                        aviso.textContent = texto;
                        item.appendChild(aviso);
                        lista.appendChild(item);
                    });

                    var accion = document.createElement('li');
                    var formulario = document.createElement('form');
                    formulario.method = 'POST';
                    formulario.action = '{{ route('notificaciones.leer') }}';

                    var token = document.createElement('input');
                    token.type = 'hidden';
                    token.name = '_token';
                    token.value = '{{ csrf_token() }}';

                    var enviar = document.createElement('button');
                    enviar.className = 'dropdown-item text-center small text-primary py-2';
                    enviar.type = 'submit';
                    enviar.textContent = 'Marcar leídas';

                    formulario.appendChild(token);
                    formulario.appendChild(enviar);
                    accion.appendChild(formulario);
                    lista.appendChild(accion);
                });
        }

        setInterval(actualizarNotificaciones, 3000);
    </script>
@endpush
