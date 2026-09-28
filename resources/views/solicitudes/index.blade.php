@extends('layouts.app')

@section('title', auth()->user()->esAdmin() ? 'Solicitudes de personal' : 'Mis solicitudes')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ofertas.css') }}">
@endpush

@section('content')
    <div class="sol-toolbar">
        <span class="sol-count">{{ $busquedas->total() }} {{ $busquedas->total() === 1 ? 'solicitud' : 'solicitudes' }}</span>
        @if (auth()->user()->esEmpresa())
            <a class="sol-btn" href="{{ route('solicitudes.create') }}">Nueva solicitud</a>
        @endif
    </div>

    <section class="tl-card">
        @if ($busquedas->isEmpty())
            <p class="sol-vacio">Todavía no hay solicitudes.</p>
            @if (auth()->user()->esEmpresa())
                <a href="{{ route('solicitudes.create') }}">Crear la primera</a>
            @endif
        @else
            @error('estado_busqueda_id')
                <p class="sol-error">{{ $message }}</p>
            @enderror

            <div class="sol-tabla-wrap">
                <table class="sol-tabla">
                    <thead>
                        <tr>
                            <th>Puesto</th>
                            @if (auth()->user()->esAdmin())
                                <th>Empresa</th>
                            @endif
                            <th>Vacantes</th>
                            <th>Experiencia</th>
                            <th>Modalidad</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            @if (auth()->user()->esAdmin())
                                <th>Oferta</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($busquedas as $busqueda)
                            <tr>
                                <td>{{ $busqueda->nombre_puesto }}</td>
                                @if (auth()->user()->esAdmin())
                                    <td>{{ $busqueda->empresa->nombre }}</td>
                                @endif
                                <td>{{ $busqueda->detalle?->cantidad_vacantes ?? '—' }}</td>
                                <td>
                                    @if ($busqueda->detalle?->anios_experiencia === null)
                                        —
                                    @elseif ((int) $busqueda->detalle->anios_experiencia === 1)
                                        1 año
                                    @else
                                        {{ $busqueda->detalle->anios_experiencia }} años
                                    @endif
                                </td>
                                <td>{{ $busqueda->detalle?->modalidad?->nombre ?? '—' }}</td>
                                <td>
                                    @if ($busqueda->detalle?->ciudad)
                                        {{ $busqueda->detalle->ciudad->nombre }}, {{ $busqueda->detalle->ciudad->provincia->nombre }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="sol-badge sol-badge-{{ $busqueda->estado_busqueda_id }}">{{ $busqueda->estado->nombre }}</span>
                                    @if (auth()->user()->esAdmin())
                                        <form class="sol-estado" method="POST" action="{{ route('solicitudes.estado', $busqueda) }}">
                                            @csrf
                                            @method('PATCH')
                                            <select name="estado_busqueda_id">
                                                @foreach ($estados as $estado)
                                                    <option value="{{ $estado->id }}" @selected((int) $estado->id === (int) $busqueda->estado_busqueda_id)>
                                                        {{ $estado->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit">Guardar</button>
                                        </form>
                                    @endif
                                </td>
                                @if (auth()->user()->esAdmin())
                                    <td>
                                        @if ($busqueda->oferta)
                                            <span class="ofe-badge ofe-badge-{{ $busqueda->oferta->estado_ofertas_id }}">{{ $busqueda->oferta->estado->nombre }}</span>
                                            <a href="{{ route('ofertas.edit', $busqueda->oferta) }}">Editar oferta</a>
                                        @elseif ((int) $busqueda->estado_busqueda_id !== 3)
                                            <a class="sol-btn" href="{{ route('ofertas.create', ['busquedas_id' => $busqueda->id]) }}">Publicar oferta</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $busquedas->links('partials.paginacion') }}
        @endif
    </section>
@endsection
