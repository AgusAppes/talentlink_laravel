@extends('layouts.app')

@section('title', 'Ofertas laborales')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ofertas.css') }}">
@endpush

@section('content')
    <div class="sol-toolbar">
        <span class="sol-count">{{ $ofertas->total() }} {{ $ofertas->total() === 1 ? 'oferta' : 'ofertas' }}</span>
        @if (auth()->user()->esAdmin())
            <a class="sol-btn" href="{{ route('ofertas.create') }}">Publicar oferta</a>
        @endif
    </div>

    <section class="tl-card">
        @if ($ofertas->isEmpty())
            <p class="sol-vacio">Todavía no hay ofertas publicadas.</p>
        @else
            <div class="sol-tabla-wrap">
                <table class="sol-tabla">
                    <thead>
                        <tr>
                            <th>Puesto</th>
                            @if (auth()->user()->esAdmin())
                                <th>Empresa</th>
                            @endif
                            <th>Vacantes</th>
                            <th>Modalidad</th>
                            <th>Ubicación</th>
                            @if (auth()->user()->esAdmin())
                                <th>Publicada por</th>
                            @endif
                            <th>Estado</th>
                            @if (auth()->user()->esAdmin())
                                <th>Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ofertas as $oferta)
                            <tr>
                                <td>
                                    {{ $oferta->busqueda->nombre_puesto }}
                                    @if (auth()->user()->esAdmin() && $oferta->requiere_cv)
                                        <span class="ofe-badge ofe-cv">Requiere CV</span>
                                    @endif
                                </td>
                                @if (auth()->user()->esAdmin())
                                    <td>{{ $oferta->busqueda->empresa->nombre }}</td>
                                @endif
                                <td>{{ $oferta->busqueda->detalle?->cantidad_vacantes ?? '—' }}</td>
                                <td>{{ $oferta->busqueda->detalle?->modalidad?->nombre ?? '—' }}</td>
                                <td>
                                    @if ($oferta->busqueda->detalle?->ciudad)
                                        {{ $oferta->busqueda->detalle->ciudad->nombre }}, {{ $oferta->busqueda->detalle->ciudad->provincia->nombre }}
                                    @else
                                        —
                                    @endif
                                </td>
                                @if (auth()->user()->esAdmin())
                                    <td>{{ $oferta->personalRrhh->nombre }} {{ $oferta->personalRrhh->apellido }}</td>
                                @endif
                                <td>
                                    <span class="ofe-badge ofe-badge-{{ $oferta->estado_ofertas_id }}">{{ $oferta->estado->nombre }}</span>
                                </td>
                                @if (auth()->user()->esAdmin())
                                    <td>
                                        <a href="{{ route('ofertas.edit', $oferta) }}">Editar</a>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $ofertas->links('partials.paginacion') }}
        @endif
    </section>
@endsection
