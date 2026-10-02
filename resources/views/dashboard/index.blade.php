@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <p class="fs-5 mb-3">Hola, {{ auth()->user()->nombreVisible() }}</p>

    @php
        $tarjetas = [
            ['clave' => 'solicitudes_pendientes', 'titulo' => 'Solicitudes pendientes', 'color' => 'warning', 'ruta' => route('solicitudes.index')],
            ['clave' => 'ofertas_publicadas', 'titulo' => 'Ofertas publicadas', 'color' => 'success', 'ruta' => route('ofertas.index')],
            ['clave' => 'postulaciones_pendientes', 'titulo' => 'Postulaciones por revisar', 'color' => 'info', 'ruta' => route('postulaciones.index')],
            ['clave' => 'candidatos', 'titulo' => 'Candidatos', 'color' => 'primary', 'ruta' => route('candidatos.index')],
            ['clave' => 'empresas', 'titulo' => 'Empresas', 'color' => 'secondary', 'ruta' => null],
        ];
    @endphp

    <div class="row g-3">
        @foreach ($tarjetas as $tarjeta)
            <div class="col-12 col-md-6 col-lg-4">
                <article class="card shadow-sm border-0 border-start border-4 border-{{ $tarjeta['color'] }} h-100">
                    <div class="card-body d-flex align-items-center justify-content-between gap-3 p-4">
                        <div>
                            <h2 class="fs-5 fw-semibold text-body-emphasis mb-2">{{ $tarjeta['titulo'] }}</h2>
                            <span class="d-block display-6 fw-bold text-{{ $tarjeta['color'] }}-emphasis lh-1" data-metrica="{{ $tarjeta['clave'] }}">{{ $metricas[$tarjeta['clave']] }}</span>
                        </div>
                        @if ($tarjeta['ruta'])
                            <a class="btn btn-outline-{{ $tarjeta['color'] }} rounded-circle p-2 lh-1" href="{{ $tarjeta['ruta'] }}" data-bs-toggle="tooltip" title="Ir" aria-label="Ir a {{ $tarjeta['titulo'] }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </article>
            </div>
        @endforeach
    </div>

    <article class="card shadow-sm border-0 border-start border-4 border-primary mt-3">
        <div class="card-body p-4">
            <h2 class="fs-5 fw-semibold text-body-emphasis mb-3">Candidatos registrados</h2>
            <div class="row g-3 text-center">
                @foreach (['candidatos_hoy' => 'Hoy', 'candidatos_semana' => 'Esta semana', 'candidatos_mes' => 'Este mes'] as $clave => $periodo)
                    <div class="col-12 col-md-4">
                        <div class="bg-primary-subtle rounded-3 py-3">
                            <span class="d-block display-6 fw-bold text-primary-emphasis lh-1 mb-1" data-metrica="{{ $clave }}">{{ $metricas[$clave] }}</span>
                            <span class="fw-semibold text-body-secondary">{{ $periodo }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </article>

    <p class="small text-secondary mt-3 mb-0">Actualizado: <span id="dash-hora">{{ now()->format('H:i:s') }}</span></p>
@endsection

@push('scripts')
    <script>
        // AJAX (Asynchronous JavaScript And XML) es una técnica que permite hacer peticiones a una API sin recargar la página
        // Esta función actualiza las métricas en el dashboard
        // Como funciona?
        // fetch es una función que permite hacer peticiones a una API
        // En este caso, se hace una petición a la ruta /dashboard/metricas
        // La cual consulta la base de datos y devuelve las métricas en JSON
        // El JSON se recibe en la variable datos y se actualiza en el dashboard sin recargar la página
        // Si la pestaña no está a la vista (document.hidden), no consulta para no cargar el servidor
        function actualizarMetricas() {
            if (document.hidden) {
                return;
            }

            fetch('{{ route('dashboard.metricas') }}', { headers: { 'Accept': 'application/json' } })
                .then(function (respuesta) { return respuesta.json(); })
                .then(function (datos) {
                    document.querySelectorAll('[data-metrica]').forEach(function (elemento) {
                        elemento.textContent = datos[elemento.dataset.metrica];
                    });
                    document.getElementById('dash-hora').textContent = new Date().toLocaleTimeString();
                });
        }

        // setInterval es una función que permite ejecutar una función cada cierto tiempo
        // En este caso, se ejecuta la función actualizarMetricas cada 1 segundo
        setInterval(actualizarMetricas, 1000);
    </script>
@endpush
