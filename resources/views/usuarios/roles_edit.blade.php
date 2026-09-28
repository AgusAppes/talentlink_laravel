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

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/usuarios.css') }}">
@endpush

@section('content')
    <section class="tl-card">
        <form method="POST" action="{{ route('usuarios.roles.update', $rol) }}">
            @csrf
            @method('PUT')

            @error('permisos')
                <p class="sol-error">{{ $message }}</p>
            @enderror
            @error('permisos.*')
                <p class="sol-error">{{ $message }}</p>
            @enderror

            <div class="usu-grupos">
                @foreach ($prefijos as $prefijo)
                    <section class="usu-grupo">
                        <h3>{{ $titulos[$prefijo] ?? $prefijo }}</h3>
                        @foreach ($grupos[$prefijo] as $permiso)
                            @php
                                $obligatorio = (int) $rol->id === 1
                                    && in_array($permiso->nombre, ['usuarios.ver', 'usuarios.administrar'], true);
                            @endphp
                            <label class="sol-check">
                                <input
                                    type="checkbox"
                                    name="permisos[]"
                                    value="{{ $permiso->id }}"
                                    @checked($obligatorio || $seleccionados->contains($permiso->id))
                                    @disabled($obligatorio)
                                >
                                <span>{{ $permiso->nombre }}</span>
                            </label>
                            @if ($obligatorio)
                                <p class="usu-nota">Obligatorio para el rol admin</p>
                            @endif
                        @endforeach
                    </section>
                @endforeach
            </div>

            <div class="sol-acciones">
                <button class="sol-btn" type="submit">Guardar permisos</button>
                <a class="sol-btn-sec" href="{{ route('usuarios.roles') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
