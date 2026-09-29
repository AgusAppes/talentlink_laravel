@extends('layouts.app')

@section('title', 'Nueva solicitud')

@section('content')
    <section class="card shadow-sm border-0">
        <div class="card-body">
        <form method="POST" action="{{ route('solicitudes.store') }}">
            @csrf

            <fieldset class="border-0 p-0 mb-4">
                <legend class="fs-6 fw-semibold mb-3">Puesto</legend>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="nombre_puesto">Nombre del puesto</label>
                        <input class="form-control @error('nombre_puesto') is-invalid @enderror" id="nombre_puesto" name="nombre_puesto" type="text" maxlength="100" value="{{ old('nombre_puesto') }}" required>
                        @error('nombre_puesto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label" for="cantidad_vacantes">Cantidad de vacantes</label>
                        <input class="form-control @error('cantidad_vacantes') is-invalid @enderror" id="cantidad_vacantes" name="cantidad_vacantes" type="number" min="1" value="{{ old('cantidad_vacantes') }}" required>
                        @error('cantidad_vacantes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label" for="anios_experiencia">Años de experiencia</label>
                        <input class="form-control @error('anios_experiencia') is-invalid @enderror" id="anios_experiencia" name="anios_experiencia" type="number" min="0" value="{{ old('anios_experiencia') }}">
                        @error('anios_experiencia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label" for="modalidades_id">Modalidad</label>
                        <select class="form-select @error('modalidades_id') is-invalid @enderror" id="modalidades_id" name="modalidades_id" required>
                            <option value="">Seleccioná una modalidad</option>
                            @foreach ($modalidades as $modalidad)
                                <option value="{{ $modalidad->id }}" @selected((string) old('modalidades_id') === (string) $modalidad->id)>
                                    {{ $modalidad->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('modalidades_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <fieldset class="border-0 p-0 mb-4">
                <legend class="fs-6 fw-semibold mb-3">Ubicación</legend>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="provincias_id">Provincia</label>
                        <select class="form-select" id="provincias_id" name="provincias_id">
                            <option value="">Sin ubicación específica</option>
                            @foreach ($provincias as $provincia)
                                <option value="{{ $provincia->id }}" @selected((string) $provinciaElegida === (string) $provincia->id)>
                                    {{ $provincia->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="ciudades_id">Ciudad</label>
                        <select class="form-select @error('ciudades_id') is-invalid @enderror" id="ciudades_id" name="ciudades_id">
                            <option value="">Sin ubicación específica</option>
                        </select>
                        @error('ciudades_id')
                            <div class="invalid-feedback">{{ $message }}</div>
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

            <fieldset class="border-0 p-0 mb-4">
                <legend class="fs-6 fw-semibold mb-3">Descripción y habilidades</legend>
                <div class="mb-3">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" maxlength="500" rows="5">{{ old('descripcion') }}</textarea>
                    <div class="form-text">máx. 500 caracteres</div>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <p class="form-label">Habilidades</p>
                    <div class="row g-2">
                        @foreach ($habilidades as $habilidad)
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="habilidades_ids[]" value="{{ $habilidad->id }}" @checked(collect(old('habilidades_ids', []))->contains($habilidad->id))>
                                    <span class="form-check-label">{{ $habilidad->nombre }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('habilidades_ids')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('habilidades_ids.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="habilidades_nuevas">Agregar otras habilidades (separadas por coma)</label>
                    <input class="form-control @error('habilidades_nuevas') is-invalid @enderror" id="habilidades_nuevas" name="habilidades_nuevas" type="text" maxlength="255" value="{{ old('habilidades_nuevas') }}">
                    @error('habilidades_nuevas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </fieldset>

            <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Registrar solicitud</button>
                <a class="btn btn-outline-secondary" href="{{ route('solicitudes.index') }}">Cancelar</a>
            </div>
        </form>
        </div>
    </section>
@endsection
