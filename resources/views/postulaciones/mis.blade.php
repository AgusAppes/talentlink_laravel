@extends('layouts.app')

@section('title', 'Mis postulaciones')

@section('content')
    <section class="card shadow-sm border-0">
        @if ($postulaciones->isEmpty())
            <div class="card-body text-center text-secondary py-4">
                <p class="mb-2">Todavía no te postulaste a ninguna oferta.</p>
                <a href="{{ route('ofertas.index') }}">Ofertas disponibles</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Puesto</th>
                            <th>Empresa</th>
                            <th>Modalidad</th>
                            <th>Estado de la oferta</th>
                            <th>Etapa</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($postulaciones as $postulacion)
                            <tr>
                                <td>{{ $postulacion->oferta->busqueda->nombre_puesto }}</td>
                                <td>{{ $postulacion->oferta->busqueda->empresa->nombre }}</td>
                                <td>{{ $postulacion->oferta->busqueda->ficha?->modalidad?->nombre ?? '—' }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ match ((int) $postulacion->oferta->estado_ofertas_id) { 1 => 'text-bg-success', 2, 3 => 'text-bg-secondary', default => 'text-bg-primary' } }}">{{ $postulacion->oferta->estado->nombre }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ match ((int) $postulacion->etapas_id) { 1, 2 => 'text-bg-warning', 3 => 'text-bg-danger', 4 => 'text-bg-success', 5 => 'text-bg-primary', 6 => 'text-bg-secondary', default => 'text-bg-primary' } }}">{{ $postulacion->etapa->nombre }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column align-items-start gap-2">
                                        @php $activa = $postulacion->entrevistaActiva(); @endphp
                                        @if ($activa && $activa->estado === 'solicitada')
                                            <a class="btn btn-sm btn-primary" href="{{ route('entrevistas.elegir', $postulacion) }}">Elegir horario</a>
                                            @include('entrevistas.cancelar', ['entrevista' => $activa])
                                        @elseif ($activa)
                                            <p class="small mb-0">{{ $activa->inicioLocal()->format('d/m/Y H:i') }} · {{ $activa->duracion_minutos }} min</p>
                                            @include('entrevistas.enlace', ['entrevista' => $activa])
                                            @include('entrevistas.cancelar', ['entrevista' => $activa])
                                        @endif
                                        <form method="POST" action="{{ route('postulaciones.destroy', $postulacion->oferta) }}" onsubmit="return confirm('¿Cancelar esta postulación?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-secondary" type="submit">Cancelar postulación</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
