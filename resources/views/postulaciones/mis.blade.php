@extends('layouts.app')

@section('title', 'Mis postulaciones')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ofertas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/postulaciones.css') }}">
@endpush

@section('content')
    <section class="tl-card">
        @if ($postulaciones->isEmpty())
            <p class="sol-vacio">Todavía no te postulaste a ninguna oferta.</p>
            <a href="{{ route('ofertas.index') }}">Ofertas disponibles</a>
        @else
            <div class="sol-tabla-wrap">
                <table class="sol-tabla">
                    <thead>
                        <tr>
                            <th>Puesto</th>
                            <th>Empresa</th>
                            <th>Modalidad</th>
                            <th>Estado de la oferta</th>
                            <th>Etapa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($postulaciones as $postulacion)
                            <tr>
                                <td>{{ $postulacion->oferta->busqueda->nombre_puesto }}</td>
                                <td>{{ $postulacion->oferta->busqueda->empresa->nombre }}</td>
                                <td>{{ $postulacion->oferta->busqueda->detalle?->modalidad?->nombre ?? '—' }}</td>
                                <td>
                                    <span class="ofe-badge ofe-badge-{{ $postulacion->oferta->estado_ofertas_id }}">{{ $postulacion->oferta->estado->nombre }}</span>
                                </td>
                                <td>
                                    <span class="pos-badge pos-badge-{{ $postulacion->etapas_id }}">{{ $postulacion->etapa->nombre }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
