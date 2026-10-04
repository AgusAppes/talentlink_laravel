@extends('layouts.app')

@php
    $rolNombre = match ((int) $rol->id) {
        1 => 'Personal RRHH',
        2 => 'Empresa',
        3 => 'Candidato',
        default => $rol->nombre,
    };
    $orden = ['dashboard', 'solicitudes', 'candidatos', 'empresas', 'ofertas', 'postulaciones', 'reportes', 'usuarios'];
    $titulos = [
        'dashboard' => 'Dashboard',
        'solicitudes' => 'Solicitudes',
        'candidatos' => 'Candidatos',
        'empresas' => 'Empresas',
        'ofertas' => 'Ofertas',
        'postulaciones' => 'Postulaciones',
        'reportes' => 'Reportes',
        'usuarios' => 'Usuarios',
    ];
    $grupos = $permisos->groupBy(fn ($permiso) => explode('.', $permiso->nombre, 2)[0]);
    $prefijos = collect($orden)->filter(fn ($prefijo) => $grupos->has($prefijo));
    $prefijos = $prefijos->concat($grupos->keys()->diff($prefijos)->sort()->values());
    $seleccionados = collect(old('permisos', $rol->permisos->pluck('id')->all()))->map(fn ($id) => (int) $id);
@endphp

@section('title', 'Permisos: '.$rolNombre)

@section('content')
    <section class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('usuarios.roles.update', $rol) }}">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    @foreach ($prefijos as $prefijo)
                        <div class="col-12 col-md-6 col-lg-4">
                            <section class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h3 class="fs-6 fw-semibold mb-3">{{ $titulos[$prefijo] ?? $prefijo }}</h3>
                                    @foreach ($grupos[$prefijo] as $permiso)
                                        @php
                                            $obligatorio = (int) $rol->id === 1
                                                && in_array($permiso->nombre, ['usuarios.ver', 'usuarios.administrar'], true);
                                        @endphp
                                        <div class="form-check mb-2">
                                            <label class="form-check-label">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="permisos[]"
                                                    value="{{ $permiso->id }}"
                                                    @checked($obligatorio || $seleccionados->contains($permiso->id))
                                                    @disabled($obligatorio)
                                                >
                                                {{ $permiso->nombre }}
                                            </label>
                                        </div>
                                        @if ($obligatorio)
                                            <p class="form-text mt-0 mb-2">Obligatorio para el rol admin</p>
                                        @endif
                                    @endforeach
                                </div>
                            </section>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Guardar permisos</button>
                    <a class="btn btn-outline-secondary" href="{{ route('usuarios.roles') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </section>
@endsection
