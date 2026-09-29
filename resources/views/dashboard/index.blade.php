@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <p class="fs-5 mb-3">Hola, {{ auth()->user()->nombreVisible() }}</p>

    <div class="row g-3">
        <div class="col-12 col-md-6 col-lg-4">
            <article class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-secondary small mb-2">Solicitudes pendientes</p>
                    <span class="d-block fs-2 fw-bold text-primary lh-1 mb-3" data-metrica="solicitudes_pendientes">{{ $metricas['solicitudes_pendientes'] }}</span>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('solicitudes.index') }}">Ver</a>
                </div>
            </article>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <article class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-secondary small mb-2">Ofertas publicadas</p>
                    <span class="d-block fs-2 fw-bold text-primary lh-1 mb-3" data-metrica="ofertas_publicadas">{{ $metricas['ofertas_publicadas'] }}</span>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('ofertas.index') }}">Ver</a>
                </div>
            </article>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <article class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-secondary small mb-2">Postulaciones por revisar</p>
                    <span class="d-block fs-2 fw-bold text-primary lh-1 mb-3" data-metrica="postulaciones_pendientes">{{ $metricas['postulaciones_pendientes'] }}</span>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('postulaciones.index') }}">Ver</a>
                </div>
            </article>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <article class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-secondary small mb-2">Candidatos</p>
                    <span class="d-block fs-2 fw-bold text-primary lh-1 mb-3" data-metrica="candidatos">{{ $metricas['candidatos'] }}</span>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('candidatos.index') }}">Ver</a>
                </div>
            </article>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <article class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-secondary small mb-2">Empresas</p>
                    <span class="d-block fs-2 fw-bold text-primary lh-1 mb-0" data-metrica="empresas">{{ $metricas['empresas'] }}</span>
                </div>
            </article>
        </div>
    </div>

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
        function actualizarMetricas() {
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
        // En este caso, se ejecuta la función actualizarMetricas cada 1.5 segundos
        setInterval(actualizarMetricas, 1500);
    </script>
@endpush
