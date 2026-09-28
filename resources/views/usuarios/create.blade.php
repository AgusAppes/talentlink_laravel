@extends('layouts.app')

@section('title', 'Nuevo usuario')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
@endpush

@section('content')
    <section class="tl-card">
        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf

            <fieldset class="sol-bloque">
                <legend>Cuenta</legend>
                <div class="sol-grid">
                    <div class="sol-campo">
                        <label for="roles_id">Rol</label>
                        <select id="roles_id" name="roles_id" required>
                            <option value="1" @selected((string) old('roles_id', '1') === '1')>Personal RRHH</option>
                            <option value="2" @selected((string) old('roles_id') === '2')>Empresa</option>
                        </select>
                        @error('roles_id')
                            <p class="sol-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sol-campo">
                        <label for="correo">Correo</label>
                        <input id="correo" name="correo" type="email" maxlength="100" value="{{ old('correo') }}" required>
                        @error('correo')
                            <p class="sol-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sol-campo">
                        <label for="password">Contraseña</label>
                        <input id="password" name="password" type="password" required>
                        @error('password')
                            <p class="sol-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sol-campo">
                        <label for="password_confirmation">Confirmar contraseña</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required>
                    </div>
                </div>
            </fieldset>

            <fieldset class="sol-bloque">
                <legend>Si es Personal RRHH</legend>
                <p class="sol-ayuda">Completá nombre y apellido solo si el rol elegido es Personal RRHH.</p>
                <div class="sol-grid">
                    <div class="sol-campo">
                        <label for="nombre">Nombre</label>
                        <input id="nombre" name="nombre" type="text" maxlength="100" value="{{ old('nombre') }}">
                        @error('nombre')
                            <p class="sol-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sol-campo">
                        <label for="apellido">Apellido</label>
                        <input id="apellido" name="apellido" type="text" maxlength="100" value="{{ old('apellido') }}">
                        @error('apellido')
                            <p class="sol-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <fieldset class="sol-bloque">
                <legend>Si es Empresa</legend>
                <p class="sol-ayuda">Completá el nombre solo si el rol elegido es Empresa.</p>
                <div class="sol-campo">
                    <label for="empresa_nombre">Nombre de la empresa</label>
                    <input id="empresa_nombre" name="empresa_nombre" type="text" maxlength="100" value="{{ old('empresa_nombre') }}">
                    @error('empresa_nombre')
                        <p class="sol-error">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <div class="sol-acciones">
                <button class="sol-btn" type="submit">Crear usuario</button>
                <a class="sol-btn-sec" href="{{ route('usuarios.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
