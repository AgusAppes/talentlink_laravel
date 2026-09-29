@extends('layouts.app')

@section('title', auth()->user()->esAdmin() ? 'Solicitudes de personal' : 'Mis solicitudes')

@section('content')
    <div class="d-flex justify-content-end align-items-center gap-3 mb-3">
        <span class="text-secondary small">{{ $busquedas->total() }} {{ $busquedas->total() === 1 ? 'solicitud' : 'solicitudes' }}</span>
        @if (auth()->user()->esEmpresa())
            <a class="btn btn-primary" href="{{ route('solicitudes.create') }}">Nueva solicitud</a>
        @endif
    </div>

    <section class="card shadow-sm border-0">
        @if ($busquedas->isEmpty())
            <div class="card-body text-center text-secondary py-4">
                <p class="mb-2">Todavía no hay solicitudes.</p>
                @if (auth()->user()->esEmpresa())
                    <a href="{{ route('solicitudes.create') }}">Crear la primera</a>
                @endif
            </div>
        @else
            @error('estado_busqueda_id')
                <div class="invalid-feedback d-block px-3 pt-3">{{ $message }}</div>
            @enderror

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                                    <span class="badge rounded-pill {{ match ((int) $busqueda->estado_busqueda_id) { 1 => 'text-bg-warning', 3 => 'text-bg-secondary', default => 'text-bg-primary' } }}">{{ $busqueda->estado->nombre }}</span>
                                    @if (auth()->user()->esAdmin())
                                        <form class="d-flex gap-2 align-items-center mt-2" method="POST" action="{{ route('solicitudes.estado', $busqueda) }}">
                                            @csrf
                                            @method('PATCH')
                                            <select class="form-select form-select-sm w-auto" name="estado_busqueda_id">
                                                @foreach ($estados as $estado)
                                                    <option value="{{ $estado->id }}" @selected((int) $estado->id === (int) $busqueda->estado_busqueda_id)>
                                                        {{ $estado->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-sm btn-outline-secondary flex-shrink-0" type="submit">Guardar</button>
                                        </form>
                                    @endif
                                </td>
                                @if (auth()->user()->esAdmin())
                                    <td>
                                        @if ($busqueda->oferta)
                                            <span class="badge rounded-pill {{ match ((int) $busqueda->oferta->estado_ofertas_id) { 1 => 'text-bg-success', 2 => 'text-bg-warning', 3 => 'text-bg-secondary', default => 'text-bg-primary' } }}">{{ $busqueda->oferta->estado->nombre }}</span>
                                            <a class="d-block small mt-1" href="{{ route('ofertas.edit', $busqueda->oferta) }}">Editar oferta</a>
                                        @elseif ((int) $busqueda->estado_busqueda_id !== 3)
                                            <a class="btn btn-sm btn-primary" href="{{ route('ofertas.create', ['busquedas_id' => $busqueda->id]) }}">Publicar oferta</a>
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
            <div class="card-body py-3">
                {{ $busquedas->links() }}
            </div>
        @endif
    </section>
@endsection
