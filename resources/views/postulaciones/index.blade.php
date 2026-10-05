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
            @error('etapas_id')
                <div class="invalid-feedback d-block px-3 pt-3">{{ $message }}</div>
            @enderror

            @if (auth()->user()->personalRrhh && ! $tieneHorario)
                <p class="text-secondary small px-3 pt-3 mb-0">Todavía no cargaste tus horarios. Guardalos en <a href="{{ route('perfil.horario') }}">Mi perfil</a> para poder solicitar entrevistas.</p>
            @endif

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
                            <th>CV</th>
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
                                    <form class="d-flex gap-2 align-items-center mt-2" method="POST" action="{{ route('postulaciones.etapa', $postulacion) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select class="form-select form-select-sm w-auto" name="etapas_id">
                                            @foreach ($etapas as $etapa)
                                                <option value="{{ $etapa->id }}" @selected((int) $etapa->id === (int) $postulacion->etapas_id)>
                                                    {{ $etapa->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-outline-secondary flex-shrink-0" type="submit">Guardar</button>
                                    </form>
                                </td>
                                <td>
                                    @if (auth()->user()->personalRrhh)
                                        @php $activa = $postulacion->entrevistaActiva(); @endphp
                                        @if (! $activa)
                                            @if ($tieneHorario)
                                                <form method="POST" action="{{ route('entrevistas.store', $postulacion) }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-primary" type="submit">Solicitar entrevista</button>
                                                </form>
                                            @else
                                                <button class="btn btn-sm btn-primary" type="button" disabled>Solicitar entrevista</button>
                                            @endif
                                        @elseif ($activa->estado === 'solicitada')
                                            <p class="small text-secondary mb-1">Esperando que el candidato elija horario.</p>
                                            @if ((int) $activa->personal_rrhh_id === (int) auth()->user()->personalRrhh->id)
                                                @include('entrevistas.cancelar', ['entrevista' => $activa])
                                            @endif
                                        @else
                                            <p class="small mb-1">{{ $activa->inicioLocal()->format('d/m/Y H:i') }} · {{ $activa->duracion_minutos }} min</p>
                                            @include('entrevistas.enlace', ['entrevista' => $activa])
                                            @if ((int) $activa->personal_rrhh_id === (int) auth()->user()->personalRrhh->id)
                                                @include('entrevistas.cancelar', ['entrevista' => $activa])
                                            @endif
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column align-items-start gap-2">
                                        <span class="badge rounded-pill {{ $postulacion->claseCompatibilidad() }}">{{ $postulacion->textoCompatibilidad() }}</span>
                                        @if ($reglas = data_get($postulacion->compatibilidad_detalle, 'reglas'))
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#compat-{{ $postulacion->id }}">Detalle</button>
                                            <div class="collapse" id="compat-{{ $postulacion->id }}">
                                                <ul class="small text-secondary mb-0 ps-3">
                                                    @foreach ($reglas as $regla)
                                                        <li>{{ $regla }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <form method="POST" action="{{ route('postulaciones.compatibilidad', $postulacion) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary" type="submit">{{ $postulacion->compatibilidad_detalle ? 'Recalcular' : 'Calcular' }}</button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    @if ($postulacion->cv)
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('postulaciones.cv', $postulacion->id) }}" target="_blank">Ver CV</a>
                                    @else
                                        —
                                    @endif
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
