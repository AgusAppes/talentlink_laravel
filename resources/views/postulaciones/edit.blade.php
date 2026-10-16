@extends('layouts.app')

@section('title', 'Editar postulación')

@section('content')
    <a class="d-inline-block mb-3 small text-decoration-none" href="{{ route('postulaciones.index') }}">← Volver a postulaciones</a>

    <section class="card shadow-sm border-0">
        <div class="card-body">
            <p class="mb-2">
                <span class="d-block text-secondary small">Candidato</span>
                <span class="fw-semibold">
                    @if ($candidato = $postulacion->candidatos->first())
                        {{ $candidato->nombre }} {{ $candidato->apellido }}
                    @else
                        —
                    @endif
                </span>
            </p>
            <p class="mb-3"><span class="d-block text-secondary small">Puesto</span><span class="fw-semibold">{{ $postulacion->oferta->busqueda->nombre_puesto }} · {{ $postulacion->oferta->busqueda->empresa->nombre }}</span></p>

            <form method="POST" action="{{ route('postulaciones.etapa', $postulacion) }}">
                @csrf
                @method('PATCH')

                <label class="form-label" for="etapas_id">Etapa</label>
                <div class="d-flex gap-2 align-items-start">
                    <select class="form-select w-auto @error('etapas_id') is-invalid @enderror" id="etapas_id" name="etapas_id">
                        @foreach ($etapas as $etapa)
                            <option value="{{ $etapa->id }}" @selected((int) $etapa->id === (int) $postulacion->etapas_id)>
                                {{ $etapa->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary flex-shrink-0" type="submit">Guardar</button>
                </div>
                @error('etapas_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </form>
        </div>
    </section>

    @if (auth()->user()->personalRrhh)
        <section class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <h2 class="h5 fw-semibold mb-3">Entrevista</h2>

                @php $activa = $postulacion->entrevistaActiva(); @endphp
                @if (! $activa)
                    @if ($tieneHorario)
                        <form method="POST" action="{{ route('entrevistas.store', $postulacion) }}">
                            @csrf
                            <button class="btn btn-sm btn-primary" type="submit">Solicitar entrevista</button>
                        </form>
                    @else
                        <p class="text-secondary small mb-2">Todavía no cargaste tus horarios. Guardalos en <a href="{{ route('perfil.horario') }}">Mi perfil</a> para poder solicitar entrevistas.</p>
                        <button class="btn btn-sm btn-primary" type="button" disabled>Solicitar entrevista</button>
                    @endif
                @else
                    @if ($activa->estado === 'solicitada')
                        <p class="small text-secondary mb-1">Esperando que el candidato elija horario.</p>
                    @else
                        <p class="small mb-1">{{ $activa->inicioLocal()->format('d/m/Y H:i') }}</p>
                        @include('entrevistas.enlace', ['entrevista' => $activa])
                    @endif
                    @if ((int) $activa->personal_rrhh_id === (int) auth()->user()->personalRrhh->id)
                        <div class="mt-2">
                            @include('entrevistas.cancelar', ['entrevista' => $activa])
                        </div>
                    @endif
                @endif
            </div>
        </section>
    @endif
@endsection
