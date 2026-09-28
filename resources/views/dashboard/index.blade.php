@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
    <p class="dash-saludo">Hola, {{ auth()->user()->nombreVisible() }}</p>

    <div class="dash-grilla">
        <article class="dash-tarjeta">
            <p class="dash-etiqueta">Solicitudes pendientes</p>
            <span class="dash-numero" data-metrica="solicitudes_pendientes">{{ $metricas['solicitudes_pendientes'] }}</span>
            <a class="dash-ver" href="{{ route('solicitudes.index') }}">Ver</a>
        </article>

        <article class="dash-tarjeta">
            <p class="dash-etiqueta">Ofertas publicadas</p>
            <span class="dash-numero" data-metrica="ofertas_publicadas">{{ $metricas['ofertas_publicadas'] }}</span>
            <a class="dash-ver" href="{{ route('ofertas.index') }}">Ver</a>
        </article>

        <article class="dash-tarjeta">
            <p class="dash-etiqueta">Postulaciones por revisar</p>
            <span class="dash-numero" data-metrica="postulaciones_pendientes">{{ $metricas['postulaciones_pendientes'] }}</span>
            <a class="dash-ver" href="{{ route('postulaciones.index') }}">Ver</a>
        </article>

        <article class="dash-tarjeta">
            <p class="dash-etiqueta">Candidatos</p>
            <span class="dash-numero" data-metrica="candidatos">{{ $metricas['candidatos'] }}</span>
            <a class="dash-ver" href="{{ route('candidatos.index') }}">Ver</a>
        </article>

        <article class="dash-tarjeta">
            <p class="dash-etiqueta">Empresas</p>
            <span class="dash-numero" data-metrica="empresas">{{ $metricas['empresas'] }}</span>
        </article>
    </div>

    <p class="dash-hora">Actualizado: <span id="dash-hora">{{ now()->format('H:i:s') }}</span></p>
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
