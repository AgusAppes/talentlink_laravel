@extends('layouts.app')

@section('title', 'Candidatos recibidos')

@section('content')
    <a class="d-inline-block mb-3 small text-decoration-none" href="{{ route('solicitudes.index') }}">← Volver a mis solicitudes</a>

    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h1 class="h4 mb-0">{{ $busqueda->nombre_puesto }}</h1>
        <span class="text-secondary small">{{ $envios->count() }} {{ $envios->count() === 1 ? 'candidato recibido' : 'candidatos recibidos' }}</span>
    </div>

    @forelse ($envios as $envio)
        @php $candidato = $envio->postulacion->candidatos->first(); @endphp
        <article class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h2 class="h5 fw-semibold mb-1">
                            @if ($candidato)
                                {{ $candidato->nombre }} {{ $candidato->apellido }}
                            @else
                                Candidato sin datos
                            @endif
                        </h2>
                        <p class="text-secondary small mb-0">{{ $candidato?->usuario?->correo ?? '—' }}</p>
                    </div>
                    <span class="badge rounded-pill {{ $envio->postulacion->claseCompatibilidad() }}">{{ $envio->postulacion->textoCompatibilidad() }}</span>
                </div>

                <dl class="row g-3 mt-2 mb-0">
                    <div class="col-12 col-md-6">
                        <dt class="text-secondary small fw-normal">Teléfono</dt>
                        <dd class="mb-0">{{ $envio->telefono ?? '—' }}</dd>
                    </div>
                    <div class="col-12">
                        <dt class="text-secondary small fw-normal">Descripción</dt>
                        <dd class="mb-0">
                            @if ($envio->descripcion)
                                {!! nl2br(e($envio->descripcion)) !!}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="col-12">
                        <dt class="text-secondary small fw-normal">Comentarios de entrevista</dt>
                        <dd class="mb-0">
                            @if ($envio->comentario)
                                {!! nl2br(e($envio->comentario)) !!}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    @if ($reglas = data_get($envio->postulacion->compatibilidad_detalle, 'reglas'))
                        <div class="col-12">
                            <dt class="text-secondary small fw-normal">Compatibilidad con los requisitos</dt>
                            <dd class="mb-0">
                                <ul class="small text-secondary mb-0 ps-3">
                                    @foreach ($reglas as $regla)
                                        <li>{{ $regla }}</li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @endif
                </dl>

                @if ($candidato)
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('solicitudes.perfil', [$busqueda, $candidato]) }}">Ver perfil</a>
                        <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#entrevista-{{ $envio->id }}">Solicitar entrevista</button>
                    </div>
                @endif

                @if ($envio->entrevistaEmpresa)
                    <p class="small text-secondary mb-0 mt-3">
                        {{ $envio->entrevistaEmpresa->enlace ? 'Entrevista confirmada' : 'Entrevista propuesta' }}
                        para el {{ $envio->entrevistaEmpresa->inicioLocal()->format('d/m/Y H:i') }}.
                    </p>
                    @include('entrevistas.enlace', ['entrevista' => $envio->entrevistaEmpresa])
                @endif
            </div>
        </article>

        @if ($candidato)
            <div class="modal fade" id="entrevista-{{ $envio->id }}" tabindex="-1" aria-labelledby="entrevistaTitulo-{{ $envio->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('envios.entrevista', $envio) }}">
                        @csrf
                        <input type="hidden" name="envio_id" value="{{ $envio->id }}">
                        <div class="modal-header">
                            <h2 class="modal-title h5" id="entrevistaTitulo-{{ $envio->id }}">Entrevista con {{ $candidato->nombre }} {{ $candidato->apellido }}</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            @php $esteEnvio = (string) old('envio_id') === (string) $envio->id; @endphp
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="fecha-{{ $envio->id }}">Día</label>
                                    <input class="form-control {{ $esteEnvio && $errors->has('fecha') ? 'is-invalid' : '' }}" type="date" id="fecha-{{ $envio->id }}" name="fecha" required min="{{ now(\App\Http\Controllers\CalendarioController::ZONA)->format('Y-m-d') }}" value="{{ $esteEnvio ? old('fecha') : '' }}">
                                    @if ($esteEnvio)
                                        @error('fecha')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="hora-{{ $envio->id }}">Horario</label>
                                    <input class="form-control {{ $esteEnvio && $errors->has('hora') ? 'is-invalid' : '' }}" type="time" id="hora-{{ $envio->id }}" name="hora" required value="{{ $esteEnvio ? old('hora') : '' }}">
                                    @if ($esteEnvio)
                                        @error('hora')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="nota-{{ $envio->id }}">Nota</label>
                                    <textarea class="form-control {{ $esteEnvio && $errors->has('nota') ? 'is-invalid' : '' }}" id="nota-{{ $envio->id }}" name="nota" rows="3" maxlength="500">{{ $esteEnvio ? old('nota') : '' }}</textarea>
                                    @if ($esteEnvio)
                                        @error('nota')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                            <button class="btn btn-primary" type="submit">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @empty
        <section class="card shadow-sm border-0">
            <p class="text-center text-secondary py-4 mb-0">Todavía no recibiste candidatos para esta solicitud.</p>
        </section>
    @endforelse
@endsection

@push('scripts')
    @if ($errors->hasAny(['fecha', 'hora', 'nota']))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('entrevista-{{ old('envio_id') }}');

                if (modal) {
                    window.bootstrap?.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    @endif
@endpush
