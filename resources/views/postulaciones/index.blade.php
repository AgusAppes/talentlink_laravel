@extends('layouts.app')

@section('title', 'Postulaciones')

@section('content')
    <div class="d-flex justify-content-end align-items-center mb-3">
        <span class="text-secondary small">{{ $postulaciones->total() }} {{ $postulaciones->total() === 1 ? 'postulación' : 'postulaciones' }}</span>
    </div>

    <section class="card shadow-sm border-0">
        @if ($postulaciones->isEmpty())
            <p class="text-center text-secondary py-4 mb-0">Todavía no hay postulaciones.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Candidato</th>
                            <th>Puesto</th>
                            <th>Empresa</th>
                            <th>Estado de la oferta</th>
                            <th>Etapa</th>
                            <th>Entrevista</th>
                            <th>Compatibilidad</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($postulaciones as $postulacion)
                            <tr>
                                <td>
                                    @if ($candidato = $postulacion->candidatos->first())
                                        {{ $candidato->nombre }} {{ $candidato->apellido }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $postulacion->oferta->busqueda->nombre_puesto }}</td>
                                <td>{{ $postulacion->oferta->busqueda->empresa->nombre }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ match ((int) $postulacion->oferta->estado_ofertas_id) { 1 => 'text-bg-success', 2, 3 => 'text-bg-secondary', default => 'text-bg-primary' } }}">{{ $postulacion->oferta->estado->nombre }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ match ((int) $postulacion->etapas_id) { 1, 2 => 'text-bg-warning', 3 => 'text-bg-danger', 4 => 'text-bg-success', 5 => 'text-bg-primary', 6 => 'text-bg-secondary', default => 'text-bg-primary' } }}">{{ $postulacion->etapa->nombre }}</span>
                                </td>
                                <td>
                                    @php $activa = $postulacion->entrevistaActiva(); @endphp
                                    @if (! $activa)
                                        —
                                    @elseif ($activa->estado === 'solicitada')
                                        <span class="small text-secondary">Esperando horario</span>
                                    @else
                                        {{ $activa->inicioLocal()->format('d/m/Y H:i') }}
                                    @endif
                                </td>
                                <td>
                                    <form class="d-flex align-items-center gap-1" method="POST" action="{{ route('postulaciones.compatibilidad', $postulacion) }}">
                                        @csrf
                                        <span class="badge rounded-pill {{ $postulacion->claseCompatibilidad() }}">{{ $postulacion->textoCompatibilidad() }}</span>
                                        <button class="btn btn-sm btn-link text-secondary p-1 lh-1" type="submit" data-bs-toggle="tooltip" title="Recalcular" aria-label="Recalcular compatibilidad">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M20 12a8 8 0 1 1-2.34-5.66"/>
                                                <path d="M20 4v4h-4"/>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-secondary text-decoration-none fs-5 lh-1 px-2" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" aria-label="Acciones">&#8942;</button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ route('postulaciones.show', $postulacion) }}">Ver</a></li>
                                            <li><a class="dropdown-item" href="{{ route('postulaciones.edit', $postulacion) }}">Editar</a></li>
                                            @if ($postulacion->cv)
                                                <li><a class="dropdown-item" href="{{ route('postulaciones.cv', $postulacion->id) }}" target="_blank">Ver CV</a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body py-3">
                {{ $postulaciones->links() }}
            </div>
        @endif
    </section>
@endsection
