@extends('layouts.guest')

@section('title', 'Registro — TalentLink')

@section('content')
    <div class="lg-card">
        <h2 class="lg-card-title">Crear cuenta</h2>

        @if (session('ok'))
            <p class="lg-ok">{{ session('ok') }}</p>
        @endif

        <form method="POST" action="{{ route('registro.store') }}">
            @csrf

            <div class="lg-field">
                <label class="lg-label" for="nombre">Nombre</label>
                <input class="lg-input" type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" maxlength="45" required>
                @error('nombre')
                    <p class="lg-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="lg-field">
                <label class="lg-label" for="apellido">Apellido</label>
                <input class="lg-input" type="text" id="apellido" name="apellido" value="{{ old('apellido') }}" maxlength="45" required>
                @error('apellido')
                    <p class="lg-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="lg-field">
                <label class="lg-label" for="correo">Correo</label>
                <input class="lg-input" type="email" id="correo" name="correo" value="{{ old('correo') }}" maxlength="100" required>
                @error('correo')
                    <p class="lg-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="lg-field">
                <label class="lg-label" for="password">Contraseña</label>
                <input class="lg-input" type="password" id="password" name="password" required>
                @error('password')
                    <p class="lg-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="lg-field">
                <label class="lg-label" for="password_confirmation">Confirmar contraseña</label>
                <input class="lg-input" type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <div class="lg-field">
                <label class="lg-label" for="fecha_nac">Fecha de nacimiento</label>
                <input class="lg-input" type="date" id="fecha_nac" name="fecha_nac" value="{{ old('fecha_nac') }}">
                @error('fecha_nac')
                    <p class="lg-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="lg-field">
                <label class="lg-label" for="ciudades_id">Ciudad</label>
                <select class="lg-input" id="ciudades_id" name="ciudades_id">
                    <option value="">Sin especificar</option>
                    @foreach ($ciudades as $ciudad)
                        <option value="{{ $ciudad->id }}" @selected((string) old('ciudades_id') === (string) $ciudad->id)>
                            {{ $ciudad->nombre }} — {{ $ciudad->provincia->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('ciudades_id')
                    <p class="lg-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="lg-btn" type="submit">Crear cuenta</button>
        </form>

        <p class="lg-switch">
            <a href="{{ route('login') }}">¿Ya tenés cuenta? Iniciá sesión</a>
        </p>
    </div>
@endsection
