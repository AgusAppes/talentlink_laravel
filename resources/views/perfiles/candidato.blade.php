@extends('layouts.app')

@section('title', 'Mi perfil')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perfiles.css') }}">
@endpush

@section('content')
    <section class="tl-card">
        <div class="per-lectura">
            <p><span>Correo</span>{{ auth()->user()->correo }}</p>
        </div>

        <form method="POST" action="{{ route('candidatos.perfil.update') }}">
            @csrf
            @method('PUT')

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
            </div>

            <div class="sol-acciones">
                <button class="sol-btn" type="submit">Guardar cambios</button>
            </div>
        </form>
    </section>
@endsection
