@extends('layouts.app')

@section('title', 'Detalle de la postulación')

@section('content')
    <a class="d-inline-block mb-3 small text-decoration-none" href="{{ route('postulaciones.index') }}">← Volver a postulaciones</a>

    <section class="card shadow-sm border-0">
        <div class="card-body">
            <dl class="row g-3 mb-0">
                <div class="col-12 col-md-6">
                    <dt class="text-secondary small fw-normal">Candidato</dt>
                    <dd class="mb-0">
                        @if ($candidato = $postulacion->candidatos->first())
                            {{ $candidato->nombre }} {{ $candidato->apellido }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="text-secondary small fw-normal">Puesto</dt>
                    <dd class="mb-0">{{ $postulacion->oferta->busqueda->nombre_puesto }}</dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="text-secondary small fw-normal">Empresa</dt>
                    <dd class="mb-0">{{ $postulacion->oferta->busqueda->empresa->nombre }}</dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="text-secondary small fw-normal">Estado de la oferta</dt>
                    <dd class="mb-0">
                        <span class="badge rounded-pill {{ match ((int) $postulacion->oferta->estado_ofertas_id) { 1 => 'text-bg-success', 2, 3 => 'text-bg-secondary', default => 'text-bg-primary' } }}">{{ $postulacion->oferta->estado->nombre }}</span>
                    </dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="text-secondary small fw-normal">Etapa</dt>
                    <dd class="mb-0">
                        <span class="badge rounded-pill {{ match ((int) $postulacion->etapas_id) { 1, 2 => 'text-bg-warning', 3 => 'text-bg-danger', 4 => 'text-bg-success', 5 => 'text-bg-primary', 6 => 'text-bg-secondary', default => 'text-bg-primary' } }}">{{ $postulacion->etapa->nombre }}</span>
                    </dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="text-secondary small fw-normal">Entrevista</dt>
                    <dd class="mb-0">
                        @php $activa = $postulacion->entrevistaActiva(); @endphp
                        @if (! $activa)
                            —
                        @elseif ($activa->estado === 'solicitada')
                            Esperando que el candidato elija horario.
                        @else
                            {{ $activa->inicioLocal()->format('d/m/Y H:i') }}
                            @include('entrevistas.enlace', ['entrevista' => $activa])
                        @endif
                    </dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="text-secondary small fw-normal">Compatibilidad</dt>
                    <dd class="mb-0">
                        <span class="badge rounded-pill {{ $postulacion->claseCompatibilidad() }}">{{ $postulacion->textoCompatibilidad() }}</span>
                        @if ($reglas = data_get($postulacion->compatibilidad_detalle, 'reglas'))
                            <ul class="small text-secondary mb-0 ps-3 mt-2">
                                @foreach ($reglas as $regla)
                                    <li>{{ $regla }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="text-secondary small fw-normal">CV</dt>
                    <dd class="mb-0">
                        @if ($postulacion->cv)
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('postulaciones.cv', $postulacion->id) }}" target="_blank">Ver CV</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </section>
@endsection
