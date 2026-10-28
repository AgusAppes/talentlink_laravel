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

    @if (auth()->user()->personalRrhh && $candidato)
        <section class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <h2 class="h5 fw-semibold mb-3">Envío a la empresa</h2>

                @if ($postulacion->puedeEnviarse())
                    @if ($postulacion->envio)
                        <p class="small text-secondary mb-2">Enviado el {{ $postulacion->envio->enviadoEl() }}.</p>
                    @endif
                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#enviarEmpresa">
                        {{ $postulacion->envio ? 'Revisar envío' : 'Enviar a la empresa' }}
                    </button>
                @else
                    @if ($postulacion->envio)
                        <p class="small text-secondary mb-0">Enviado el {{ $postulacion->envio->enviadoEl() }}. Para modificar la ficha, la etapa tiene que ser Aprobada o En entrevista.</p>
                    @else
                        <p class="text-secondary small mb-0">Vas a poder enviarlo cuando la etapa sea Aprobada o En entrevista.</p>
                    @endif
                @endif
            </div>
        </section>
    @endif

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

    @if (auth()->user()->personalRrhh && $candidato && $postulacion->puedeEnviarse())
        <div class="modal fade" id="enviarEmpresa" tabindex="-1" aria-labelledby="enviarEmpresaTitulo" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('postulaciones.enviar', $postulacion) }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title h5" id="enviarEmpresaTitulo">Esto va a ver la empresa</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <dl class="row g-3 mb-0">
                            <div class="col-12 col-md-6">
                                <dt class="text-secondary small fw-normal">Candidato</dt>
                                <dd class="mb-0">{{ $candidato->nombre }} {{ $candidato->apellido }}</dd>
                            </div>
                            <div class="col-12 col-md-6">
                                <dt class="text-secondary small fw-normal">Correo</dt>
                                <dd class="mb-0">{{ $candidato->usuario?->correo ?? '—' }}</dd>
                            </div>
                            <div class="col-12 col-md-6">
                                <dt class="text-secondary small fw-normal">Puesto</dt>
                                <dd class="mb-0">{{ $postulacion->oferta->busqueda->nombre_puesto }} · {{ $postulacion->oferta->busqueda->empresa->nombre }}</dd>
                            </div>
                            <div class="col-12">
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
                        </dl>

                        <p class="text-secondary small mt-4 mb-3">Teléfono, descripción y comentario son opcionales. Si los completás, la empresa los ve en esta solicitud. El perfil del candidato no cambia.</p>

                        <div class="mb-3">
                            <label class="form-label" for="telefono">Teléfono</label>
                            <input class="form-control @error('telefono') is-invalid @enderror" type="text" id="telefono" name="telefono" maxlength="40" value="{{ old('telefono', $postulacion->envio?->telefono) }}">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="descripcion">Descripción del candidato</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="3" maxlength="2000">{{ old('descripcion', $postulacion->envio ? $postulacion->envio->descripcion : $candidato->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="comentario">Comentarios de entrevista</label>
                            <textarea class="form-control @error('comentario') is-invalid @enderror" id="comentario" name="comentario" rows="3" maxlength="2000">{{ old('comentario', $postulacion->envio?->comentario) }}</textarea>
                            @error('comentario')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary" type="submit">{{ $postulacion->envio ? 'Guardar envío' : 'Enviar' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    @if ($errors->hasAny(['telefono', 'descripcion', 'comentario']))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('enviarEmpresa');

                if (modal) {
                    window.bootstrap?.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    @endif
@endpush
