@extends('layouts.app')

@section('title', 'Mi perfil')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perfiles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
@endpush

@section('content')
    <div class="sol-toolbar">
        <a class="sol-btn-sec" href="{{ route('password.edit') }}">Cambiar contraseña</a>
    </div>

    <section class="tl-card">
        <div class="per-lectura">
            <p><span>Correo</span>{{ auth()->user()->correo }}</p>
        </div>

        <form method="POST" action="{{ route('candidatos.perfil.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="per-foto-fila">
                @if ($candidato->foto)
                    <img class="per-foto" src="{{ asset('storage/'.$candidato->foto) }}" alt="Foto de perfil">
                @else
                    <span class="per-avatar">{{ auth()->user()->iniciales() }}</span>
                @endif

                <div class="sol-campo">
                    <label for="foto">Foto de perfil</label>
                    <input id="foto" name="foto" type="file" accept=".jpg,.jpeg,.png">
                    @error('foto')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                    @if ($candidato->foto)
                        <label class="per-quitar">
                            <input type="checkbox" name="quitar_foto" value="1" @checked(old('quitar_foto'))>
                            Quitar foto
                        </label>
                    @endif
                </div>
            </div>

            <div class="sol-grid">
                <div class="sol-campo">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" type="text" maxlength="45" value="{{ old('nombre', $candidato->nombre) }}" required>
                    @error('nombre')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo">
                    <label for="apellido">Apellido</label>
                    <input id="apellido" name="apellido" type="text" maxlength="45" value="{{ old('apellido', $candidato->apellido) }}" required>
                    @error('apellido')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo">
                    <label for="fecha_nac">Fecha de nacimiento</label>
                    <input id="fecha_nac" name="fecha_nac" type="date" value="{{ old('fecha_nac', $candidato->fecha_nac?->format('Y-m-d')) }}">
                    @error('fecha_nac')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo">
                    <label for="ciudades_id">Ciudad</label>
                    <select id="ciudades_id" name="ciudades_id">
                        <option value="" @selected((string) old('ciudades_id', $candidato->ciudades_id) === '')>Sin especificar</option>
                        @foreach ($ciudades as $ciudad)
                            <option value="{{ $ciudad->id }}" @selected((string) old('ciudades_id', $candidato->ciudades_id) === (string) $ciudad->id)>
                                {{ $ciudad->nombre }} — {{ $ciudad->provincia->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('ciudades_id')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo sol-span">
                    <label for="descripcion">Acerca de mí</label>
                    <textarea id="descripcion" name="descripcion" rows="5" maxlength="1000" placeholder="Contá brevemente tu perfil profesional, qué hacés y qué buscás.">{{ old('descripcion', $candidato->descripcion) }}</textarea>
                    @error('descripcion')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo sol-span">
                    <label for="tag-input">Habilidades principales</label>
                    <div class="tag-campo">
                        <div id="tags">
                            @foreach (old('habilidades', $candidato->habilidades->pluck('nombre')->all()) as $habilidad)
                                <span class="tag">{{ $habilidad }} <button type="button" class="tag-quitar">×</button><input type="hidden" name="habilidades[]" value="{{ $habilidad }}"></span>
                            @endforeach
                        </div>
                        <input type="text" id="tag-input" placeholder="Escribí una habilidad y presioná coma o Enter">
                    </div>
                    <p class="sol-ayuda">Máximo 10 habilidades.</p>
                    @error('habilidades')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                    @foreach ($errors->get('habilidades.*') as $mensajes)
                        @foreach ($mensajes as $mensaje)
                            <p class="sol-error">{{ $mensaje }}</p>
                        @endforeach
                    @endforeach
                </div>
            </div>

            <div class="sol-acciones">
                <button class="sol-btn" type="submit">Guardar perfil</button>
            </div>
        </form>
    </section>

    <section class="tl-card per-bloque">
        <h2>Experiencia laboral</h2>
        @include('candidatos._experiencias', [
            'experiencias' => $candidato->experiencias,
            'editable' => true,
            'vacio' => 'Todavía no cargaste experiencias laborales.',
        ])
    </section>

    <section class="tl-card per-bloque">
        <h2>Agregar experiencia</h2>
        <form method="POST" action="{{ route('perfil.experiencias.store') }}">
            @csrf

            <div class="sol-grid">
                <div class="sol-campo">
                    <label for="empresa">Empresa</label>
                    <input id="empresa" name="empresa" type="text" maxlength="100" value="{{ old('empresa') }}" required>
                    @error('empresa')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo">
                    <label for="puesto">Puesto</label>
                    <input id="puesto" name="puesto" type="text" maxlength="100" value="{{ old('puesto') }}">
                    @error('puesto')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo">
                    <label for="fecha_desde">Desde</label>
                    <input id="fecha_desde" name="fecha_desde" type="month" value="{{ old('fecha_desde') }}">
                    @error('fecha_desde')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo">
                    <label for="fecha_hasta">Hasta</label>
                    <input id="fecha_hasta" name="fecha_hasta" type="month" value="{{ old('fecha_hasta') }}">
                    <p class="sol-ayuda">Dejá "Hasta" vacío si es tu trabajo actual.</p>
                    @error('fecha_hasta')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo sol-span">
                    <label for="descripcion_experiencia">Descripción</label>
                    <textarea id="descripcion_experiencia" name="descripcion" rows="4" maxlength="1000">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="sol-acciones">
                <button class="sol-btn" type="submit">Agregar experiencia</button>
            </div>
        </form>
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
