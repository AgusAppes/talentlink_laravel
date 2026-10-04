@extends('layouts.app')

@section('title', 'Mi perfil')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
@endpush

@section('content')
    @php($editando = $errors->any())

    <div class="d-flex justify-content-end align-items-center gap-2 mb-3">
        <button class="btn btn-outline-secondary {{ $editando ? 'd-none' : '' }}" type="button" id="btn-editar-perfil" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Editar perfil" aria-label="Editar perfil">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
            </svg>
        </button>
        <button class="btn btn-outline-secondary {{ $editando ? '' : 'd-none' }}" type="button" id="btn-cancelar-edicion">Cancelar</button>
        <a class="btn btn-outline-secondary" href="{{ route('password.edit') }}">Cambiar contraseña</a>
    </div>

    <div id="vista-perfil" class="{{ $editando ? 'd-none' : '' }}">
        <section class="card shadow-sm border-0">
            <div class="card-body">
                <div class="per-foto-fila">
                    @if ($candidato->foto)
                        <img class="per-foto" src="{{ $candidato->urlFoto() }}" alt="Foto de {{ $candidato->nombre }}">
                    @else
                        <span class="per-avatar">{{ auth()->user()->iniciales() }}</span>
                    @endif
                </div>

                <dl class="row g-3 mb-0">
                    <div class="col-12 col-md-6">
                        <dt class="text-secondary small fw-normal">Nombre y apellido</dt>
                        <dd class="mb-0">{{ $candidato->nombre }} {{ $candidato->apellido }}</dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-secondary small fw-normal">Correo</dt>
                        <dd class="mb-0">{{ auth()->user()->correo }}</dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-secondary small fw-normal">Fecha de nacimiento</dt>
                        <dd class="mb-0">{{ $candidato->fecha_nac?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="col-12 col-md-6">
                        <dt class="text-secondary small fw-normal">Ubicación</dt>
                        <dd class="mb-0">
                            @if ($candidato->ciudad)
                                {{ $candidato->ciudad->nombre }}, {{ $candidato->ciudad->provincia->nombre }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <h2 class="h5 fw-semibold mb-3">Acerca de</h2>
                @if ($candidato->descripcion)
                    <p class="mb-0">{!! nl2br(e($candidato->descripcion)) !!}</p>
                @else
                    <p class="text-center text-secondary py-4 mb-0">Sin descripción.</p>
                @endif
            </div>
        </section>

        <section class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <h2 class="h5 fw-semibold mb-3">Habilidades</h2>
                @if ($candidato->habilidades->isEmpty())
                    <p class="text-center text-secondary py-4 mb-0">Sin habilidades cargadas.</p>
                @else
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($candidato->habilidades as $habilidad)
                            <span class="tag">{{ $habilidad }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <h2 class="h5 fw-semibold mb-3">Experiencia laboral</h2>
                @include('candidatos._experiencias', [
                    'experiencias' => $candidato->experiencias,
                    'editable' => false,
                    'vacio' => 'Sin experiencias cargadas.',
                ])
            </div>
        </section>
    </div>

    <div id="editar-perfil" class="{{ $editando ? '' : 'd-none' }}">
    <section class="card shadow-sm border-0">
        <div class="card-body">
        <div class="bg-body-secondary rounded p-3 mb-3">
            <p class="mb-0 text-secondary"><span class="d-block small">Correo</span>{{ auth()->user()->correo }}</p>
        </div>

        <form method="POST" action="{{ route('candidatos.perfil.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 mb-3">
                <div>
                    <img class="per-foto {{ $candidato->foto ? '' : 'd-none' }}" id="preview-foto-img" alt="Vista previa de la foto de perfil" @if ($candidato->foto) src="{{ $candidato->urlFoto() }}" data-actual="{{ $candidato->urlFoto() }}" @endif>
                    <span class="per-avatar {{ $candidato->foto ? 'd-none' : '' }}" id="preview-foto-iniciales">{{ auth()->user()->iniciales() }}</span>
                </div>

                <div class="flex-grow-1">
                    <label class="form-label" for="foto">Foto de perfil</label>
                    <input class="form-control @error('foto') is-invalid @enderror" id="foto" name="foto" type="file" accept=".jpg,.jpeg,.png">
                    @error('foto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if ($candidato->foto)
                        <label class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="quitar_foto" value="1" @checked(old('quitar_foto'))>
                            <span class="form-check-label">Quitar foto</span>
                        </label>
                    @endif
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control" id="nombre" name="nombre" type="text" maxlength="45" value="{{ old('nombre', $candidato->nombre) }}" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="apellido">Apellido</label>
                    <input class="form-control" id="apellido" name="apellido" type="text" maxlength="45" value="{{ old('apellido', $candidato->apellido) }}" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="fecha_nac">Fecha de nacimiento</label>
                    <input class="form-control @error('fecha_nac') is-invalid @enderror" id="fecha_nac" name="fecha_nac" type="date" value="{{ old('fecha_nac', $candidato->fecha_nac?->format('Y-m-d')) }}">
                    @error('fecha_nac')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="ciudades_id">Ciudad</label>
                    <select class="form-select" id="ciudades_id" name="ciudades_id">
                        <option value="" @selected((string) old('ciudades_id', $candidato->ciudades_id) === '')>Sin especificar</option>
                        @foreach ($ciudades as $ciudad)
                            <option value="{{ $ciudad->id }}" @selected((string) old('ciudades_id', $candidato->ciudades_id) === (string) $ciudad->id)>
                                {{ $ciudad->nombre }} — {{ $ciudad->provincia->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label" for="descripcion">Acerca de mí</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="5" maxlength="1000" placeholder="Contá brevemente tu perfil profesional, qué hacés y qué buscás.">{{ old('descripcion', $candidato->descripcion) }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label" for="tag-input">Habilidades principales</label>
                    <div class="tag-campo form-control d-flex flex-wrap align-items-center gap-2 h-auto" data-max="10" data-nombre="habilidades[]">
                        <div class="tags">
                            @foreach (old('habilidades', $candidato->habilidades->all()) as $habilidad)
                                <span class="tag">{{ $habilidad }} <button type="button" class="tag-quitar">×</button><input type="hidden" name="habilidades[]" value="{{ $habilidad }}"></span>
                            @endforeach
                        </div>
                        <input type="text" id="tag-input" placeholder="Escribí una habilidad y presioná coma o Enter">
                    </div>
                    <div class="form-text">Máximo 10 habilidades.</div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary" type="submit">Guardar perfil</button>
            </div>
        </form>
        </div>
    </section>

    <section class="card shadow-sm border-0 mt-3">
        <div class="card-body">
        <h2 class="h5 fw-semibold mb-3">Experiencia laboral</h2>
        @include('candidatos._experiencias', [
            'experiencias' => $candidato->experiencias,
            'editable' => true,
            'vacio' => 'Todavía no cargaste experiencias laborales.',
        ])
        </div>
    </section>

    <section class="card shadow-sm border-0 mt-3">
        <div class="card-body">
        <h2 class="h5 fw-semibold mb-3">Agregar experiencia</h2>
        <form method="POST" action="{{ route('perfil.experiencias.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="empresa">Empresa</label>
                    <input class="form-control" id="empresa" name="empresa" type="text" maxlength="100" value="{{ old('empresa') }}" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="puesto">Puesto</label>
                    <input class="form-control" id="puesto" name="puesto" type="text" maxlength="100" value="{{ old('puesto') }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="fecha_desde">Desde</label>
                    <input class="form-control" id="fecha_desde" name="fecha_desde" type="month" value="{{ old('fecha_desde') }}">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="fecha_hasta">Hasta</label>
                    <input class="form-control @error('fecha_hasta') is-invalid @enderror" id="fecha_hasta" name="fecha_hasta" type="month" value="{{ old('fecha_hasta') }}">
                    <div class="form-text">Dejá "Hasta" vacío si es tu trabajo actual.</div>
                    @error('fecha_hasta')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="descripcion_experiencia">Descripción</label>
                    <textarea class="form-control" id="descripcion_experiencia" name="descripcion" rows="4" maxlength="1000">{{ old('descripcion') }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label" for="tag-input-experiencia">Habilidades de esta experiencia</label>
                    <div class="tag-campo form-control d-flex flex-wrap align-items-center gap-2 h-auto" data-max="10" data-nombre="habilidades_experiencia[]">
                        <div class="tags">
                            @foreach (old('habilidades_experiencia', []) as $habilidad)
                                <span class="tag">{{ $habilidad }} <button type="button" class="tag-quitar">×</button><input type="hidden" name="habilidades_experiencia[]" value="{{ $habilidad }}"></span>
                            @endforeach
                        </div>
                        <input type="text" id="tag-input-experiencia" placeholder="Escribí una habilidad y presioná coma o Enter">
                    </div>
                    <div class="form-text">Máximo 10 habilidades.</div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary" type="submit">Agregar experiencia</button>
            </div>
        </form>
        </div>
    </section>
    </div>
@endsection

@push('scripts')
    @include('partials.tags')
    <script>
        const vistaPerfil = document.getElementById('vista-perfil');
        const editarPerfil = document.getElementById('editar-perfil');
        const botonEditar = document.getElementById('btn-editar-perfil');
        const botonCancelar = document.getElementById('btn-cancelar-edicion');

        botonEditar.addEventListener('click', function () {
            vistaPerfil.classList.add('d-none');
            editarPerfil.classList.remove('d-none');
            botonEditar.classList.add('d-none');
            botonCancelar.classList.remove('d-none');
        });

        botonCancelar.addEventListener('click', function () {
            editarPerfil.classList.add('d-none');
            vistaPerfil.classList.remove('d-none');
            botonCancelar.classList.add('d-none');
            botonEditar.classList.remove('d-none');
        });

        const inputFoto = document.getElementById('foto');
        const imgPreview = document.getElementById('preview-foto-img');
        const inicialesPreview = document.getElementById('preview-foto-iniciales');
        const quitarFoto = document.querySelector('input[name="quitar_foto"]');
        let vistaPreviaUrl = null;

        function mostrarFoto(src) {
            imgPreview.src = src;
            imgPreview.classList.remove('d-none');
            inicialesPreview.classList.add('d-none');
        }

        function mostrarIniciales() {
            imgPreview.classList.add('d-none');
            inicialesPreview.classList.remove('d-none');
        }

        function soltarVistaPrevia() {
            if (vistaPreviaUrl) {
                URL.revokeObjectURL(vistaPreviaUrl);
                vistaPreviaUrl = null;
            }
        }

        function restaurarFoto() {
            soltarVistaPrevia();
            if (quitarFoto && quitarFoto.checked) {
                mostrarIniciales();
                return;
            }
            if (imgPreview.dataset.actual) {
                mostrarFoto(imgPreview.dataset.actual);
                return;
            }
            mostrarIniciales();
        }

        inputFoto.addEventListener('change', function () {
            soltarVistaPrevia();
            const archivo = inputFoto.files && inputFoto.files[0];
            if (!archivo || !archivo.type.startsWith('image/')) {
                restaurarFoto();
                return;
            }
            if (quitarFoto) {
                quitarFoto.checked = false;
            }
            vistaPreviaUrl = URL.createObjectURL(archivo);
            mostrarFoto(vistaPreviaUrl);
        });

        if (quitarFoto) {
            quitarFoto.addEventListener('change', function () {
                if (quitarFoto.checked) {
                    inputFoto.value = '';
                    soltarVistaPrevia();
                    mostrarIniciales();
                    return;
                }
                restaurarFoto();
            });

            if (quitarFoto.checked) {
                mostrarIniciales();
            }
        }
    </script>
@endpush
