@extends('layouts.app')

@section('title', 'Mi perfil')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
@endpush

@section('content')
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3">
        <a class="btn btn-outline-secondary" href="{{ route('password.edit') }}">Cambiar contraseña</a>
    </div>

    <section class="card shadow-sm border-0">
        <div class="card-body">
        <div class="bg-body-secondary rounded p-3 mb-3">
            <p class="mb-0 text-secondary"><span class="d-block small">Correo</span>{{ auth()->user()->correo }}</p>
        </div>

        <form method="POST" action="{{ route('candidatos.perfil.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 mb-3">
                @if ($candidato->foto)
                    <img class="per-foto" src="{{ asset('storage/'.$candidato->foto) }}" alt="Foto de perfil">
                @else
                    <span class="per-avatar">{{ auth()->user()->iniciales() }}</span>
                @endif

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
                    <input class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" type="text" maxlength="45" value="{{ old('nombre', $candidato->nombre) }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="apellido">Apellido</label>
                    <input class="form-control @error('apellido') is-invalid @enderror" id="apellido" name="apellido" type="text" maxlength="45" value="{{ old('apellido', $candidato->apellido) }}" required>
                    @error('apellido')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                    <select class="form-select @error('ciudades_id') is-invalid @enderror" id="ciudades_id" name="ciudades_id">
                        <option value="" @selected((string) old('ciudades_id', $candidato->ciudades_id) === '')>Sin especificar</option>
                        @foreach ($ciudades as $ciudad)
                            <option value="{{ $ciudad->id }}" @selected((string) old('ciudades_id', $candidato->ciudades_id) === (string) $ciudad->id)>
                                {{ $ciudad->nombre }} — {{ $ciudad->provincia->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('ciudades_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="descripcion">Acerca de mí</label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="5" maxlength="1000" placeholder="Contá brevemente tu perfil profesional, qué hacés y qué buscás.">{{ old('descripcion', $candidato->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="tag-input">Habilidades principales</label>
                    <div class="tag-campo form-control d-flex flex-wrap align-items-center gap-2 h-auto @if ($errors->has('habilidades') || $errors->has('habilidades.*')) is-invalid @endif">
                        <div id="tags">
                            @foreach (old('habilidades', $candidato->habilidades->pluck('nombre')->all()) as $habilidad)
                                <span class="tag">{{ $habilidad }} <button type="button" class="tag-quitar">×</button><input type="hidden" name="habilidades[]" value="{{ $habilidad }}"></span>
                            @endforeach
                        </div>
                        <input type="text" id="tag-input" placeholder="Escribí una habilidad y presioná coma o Enter">
                    </div>
                    <div class="form-text">Máximo 10 habilidades.</div>
                    @error('habilidades')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @foreach ($errors->get('habilidades.*') as $mensajes)
                        @foreach ($mensajes as $mensaje)
                            <div class="invalid-feedback d-block">{{ $mensaje }}</div>
                        @endforeach
                    @endforeach
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
                    <input class="form-control @error('empresa') is-invalid @enderror" id="empresa" name="empresa" type="text" maxlength="100" value="{{ old('empresa') }}" required>
                    @error('empresa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="puesto">Puesto</label>
                    <input class="form-control @error('puesto') is-invalid @enderror" id="puesto" name="puesto" type="text" maxlength="100" value="{{ old('puesto') }}">
                    @error('puesto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="fecha_desde">Desde</label>
                    <input class="form-control @error('fecha_desde') is-invalid @enderror" id="fecha_desde" name="fecha_desde" type="month" value="{{ old('fecha_desde') }}">
                    @error('fecha_desde')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion_experiencia" name="descripcion" rows="4" maxlength="1000">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary" type="submit">Agregar experiencia</button>
            </div>
        </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const tags = document.getElementById('tags');
        const tagInput = document.getElementById('tag-input');

        function agregarTag(texto) {
            texto = texto.trim();
            if (texto === '' || tags.children.length >= 10) return;

            const tag = document.createElement('span');
            tag.className = 'tag';
            tag.textContent = texto + ' ';

            const quitar = document.createElement('button');
            quitar.type = 'button';
            quitar.className = 'tag-quitar';
            quitar.textContent = '×';

            const oculto = document.createElement('input');
            oculto.type = 'hidden';
            oculto.name = 'habilidades[]';
            oculto.value = texto;

            tag.append(quitar, oculto);
            tags.appendChild(tag);
        }

        tagInput.addEventListener('keydown', function (e) {
            if (e.key === ',' || e.key === 'Enter') {
                e.preventDefault();
                agregarTag(tagInput.value);
                tagInput.value = '';
            }
        });

        tags.addEventListener('click', function (e) {
            if (e.target.classList.contains('tag-quitar')) {
                e.target.parentElement.remove();
            }
        });

        tagInput.form.addEventListener('submit', function () {
            agregarTag(tagInput.value);
        });
    </script>
@endpush
