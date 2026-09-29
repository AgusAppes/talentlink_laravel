@extends('layouts.app')

@section('title', 'Ofertas laborales')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-secondary small">{{ $ofertas->total() }} {{ $ofertas->total() === 1 ? 'oferta' : 'ofertas' }}</span>
        @if (auth()->user()->esAdmin())
            <a class="btn btn-primary" href="{{ route('ofertas.create') }}">Publicar oferta</a>
        @endif
    </div>

    <section class="card shadow-sm border-0">
        @if ($ofertas->isEmpty())
            <p class="text-center text-secondary py-4 mb-0">Todavía no hay ofertas publicadas.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                                        <span class="badge rounded-pill text-bg-primary ms-1">Requiere CV</span>
                                    @endif
                                </td>
                                @if (auth()->user()->esAdmin())
                                    <td>{{ $oferta->busqueda->empresa->nombre }}</td>
                                @endif
                                <td>{{ $oferta->busqueda->ficha?->cantidad_vacantes ?? '—' }}</td>
                                <td>{{ $oferta->busqueda->ficha?->modalidad?->nombre ?? '—' }}</td>
                                <td>
                                    @if ($oferta->busqueda->ficha?->ciudad)
                                        {{ $oferta->busqueda->ficha->ciudad->nombre }}, {{ $oferta->busqueda->ficha->ciudad->provincia->nombre }}
                                    @else
                                        —
                                    @endif
                                </td>
                                @if (auth()->user()->esAdmin())
                                    <td>{{ $oferta->personalRrhh->nombre }} {{ $oferta->personalRrhh->apellido }}</td>
                                @endif
                                <td>
                                    <span class="badge rounded-pill {{ match ((int) $oferta->estado_ofertas_id) { 1 => 'text-bg-success', 2, 3 => 'text-bg-secondary', default => 'text-bg-primary' } }}">{{ $oferta->estado->nombre }}</span>
                                </td>
                                @if (auth()->user()->esAdmin())
                                    <td>
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('ofertas.edit', $oferta) }}">Editar</a>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body py-3">
                {{ $ofertas->links() }}
            </div>
        @endif
    </section>
@endsection
