@extends('layouts.app')

@section('title', 'Nueva solicitud')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
@endpush

@section('content')
    <section class="tl-card">
        <form method="POST" action="{{ route('solicitudes.store') }}">
            @csrf

            <fieldset class="sol-bloque">
                <legend>Puesto</legend>
                <div class="sol-grid">
                    <div class="sol-campo sol-span">
                        <label for="nombre_puesto">Nombre del puesto</label>
                        <input id="nombre_puesto" name="nombre_puesto" type="text" maxlength="100" value="{{ old('nombre_puesto') }}" required>
                        @error('nombre_puesto')
                            <p class="sol-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sol-campo">
                        <label for="cantidad_vacantes">Cantidad de vacantes</label>
                        <input id="cantidad_vacantes" name="cantidad_vacantes" type="number" min="1" value="{{ old('cantidad_vacantes') }}" required>
                        @error('cantidad_vacantes')
                            <p class="sol-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sol-campo">
                        <label for="anios_experiencia">Años de experiencia</label>
                        <input id="anios_experiencia" name="anios_experiencia" type="number" min="0" value="{{ old('anios_experiencia') }}">
                        @error('anios_experiencia')
                            <p class="sol-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sol-campo">
                        <label for="modalidades_id">Modalidad</label>
                        <select id="modalidades_id" name="modalidades_id" required>
                            <option value="">Seleccioná una modalidad</option>
                            @foreach ($modalidades as $modalidad)
                                <option value="{{ $modalidad->id }}" @selected((string) old('modalidades_id') === (string) $modalidad->id)>
                                    {{ $modalidad->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('modalidades_id')
                            <p class="sol-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <fieldset class="sol-bloque">
                <legend>Ubicación</legend>
                <div class="sol-grid">
                    <div class="sol-campo">
                        <label for="provincias_id">Provincia</label>
                        <select id="provincias_id" name="provincias_id">
                            <option value="">Sin ubicación específica</option>
                            @foreach ($provincias as $provincia)
                                <option value="{{ $provincia->id }}" @selected((string) $provinciaElegida === (string) $provincia->id)>
                                    {{ $provincia->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sol-campo">
                        <label for="ciudades_id">Ciudad</label>
                        <select id="ciudades_id" name="ciudades_id">
                            <option value="">Sin ubicación específica</option>
                        </select>
                        @error('ciudades_id')
                            <p class="sol-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <script>
                const ciudadesPorProvincia = @json($ciudades);
                const provinciaSelect = document.getElementById('provincias_id');
                const ciudadSelect = document.getElementById('ciudades_id');
                const ciudadElegida = @json(old('ciudades_id'));

                function listarCiudades(conservar) {
                    const provinciaId = provinciaSelect.value;
                    const elegida = conservar ? String(ciudadElegida ?? '') : '';

                    ciudadSelect.innerHTML = '';

                    const vacia = document.createElement('option');
                    vacia.value = '';
                    vacia.textContent = 'Sin ubicación específica';
                    ciudadSelect.appendChild(vacia);

                    if (!provinciaId) {
                        return;
                    }

                    ciudadesPorProvincia
                        .filter((ciudad) => String(ciudad.provincias_id) === String(provinciaId))
                        .forEach((ciudad) => {
                            const opcion = document.createElement('option');
                            opcion.value = ciudad.id;
                            opcion.textContent = ciudad.nombre;
                            if (elegida !== '' && elegida === String(ciudad.id)) {
                                opcion.selected = true;
                            }
                            ciudadSelect.appendChild(opcion);
                        });
                }

                provinciaSelect.addEventListener('change', () => listarCiudades(false));
                listarCiudades(true);
            </script>

            <fieldset class="sol-bloque">
                <legend>Descripción y habilidades</legend>
                <div class="sol-campo">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" maxlength="500" rows="5">{{ old('descripcion') }}</textarea>
                    <p class="sol-ayuda">máx. 500 caracteres</p>
                    @error('descripcion')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo">
                    <p class="sol-label">Habilidades</p>
                    <div class="sol-checks">
                        @foreach ($habilidades as $habilidad)
                            <label class="sol-check">
                                <input type="checkbox" name="habilidades_ids[]" value="{{ $habilidad->id }}" @checked(collect(old('habilidades_ids', []))->contains($habilidad->id))>
                                {{ $habilidad->nombre }}
                            </label>
                        @endforeach
                    </div>
                    @error('habilidades_ids')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                    @error('habilidades_ids.*')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sol-campo">
                    <label for="habilidades_nuevas">Agregar otras habilidades (separadas por coma)</label>
                    <input id="habilidades_nuevas" name="habilidades_nuevas" type="text" maxlength="255" value="{{ old('habilidades_nuevas') }}">
                    @error('habilidades_nuevas')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <div class="sol-acciones">
                <button class="sol-btn" type="submit">Registrar solicitud</button>
                <a class="sol-btn-sec" href="{{ route('solicitudes.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
