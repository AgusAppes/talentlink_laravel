@extends('layouts.guest')

@section('title', 'Registro — TalentLink')

@section('content')
    <div class="card shadow-sm w-100">
        <div class="card-body p-4">
            <h2 class="h4 mb-3">Crear cuenta</h2>

            @if (session('ok'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('ok') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('registro.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control @error('nombre') is-invalid @enderror" type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" maxlength="45" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="apellido">Apellido</label>
                    <input class="form-control @error('apellido') is-invalid @enderror" type="text" id="apellido" name="apellido" value="{{ old('apellido') }}" maxlength="45" required>
                    @error('apellido')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="correo">Correo</label>
                    <input class="form-control @error('correo') is-invalid @enderror" type="email" id="correo" name="correo" value="{{ old('correo') }}" maxlength="100" required>
                    @error('correo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Contraseña</label>
                    <input class="form-control @error('password') is-invalid @enderror" type="password" id="password" name="password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                    <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="fecha_nac">Fecha de nacimiento</label>
                    <input class="form-control @error('fecha_nac') is-invalid @enderror" type="date" id="fecha_nac" name="fecha_nac" value="{{ old('fecha_nac') }}">
                    @error('fecha_nac')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="ciudades_id">Ciudad</label>
                    <select class="form-select @error('ciudades_id') is-invalid @enderror" id="ciudades_id" name="ciudades_id">
                        <option value="">Sin especificar</option>
                        @foreach ($ciudades as $ciudad)
                            <option value="{{ $ciudad->id }}" @selected((string) old('ciudades_id') === (string) $ciudad->id)>
                                {{ $ciudad->nombre }} — {{ $ciudad->provincia->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('ciudades_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="cf-turnstile w-100" data-sitekey="{{ config('services.turnstile.site_key') }}" data-size="flexible" data-theme="light"></div>
                    @error('cf-turnstile-response')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary w-100" type="submit">Crear cuenta</button>
            </form>

            <p class="text-center mt-3 mb-0">
                <a href="{{ route('login') }}">¿Ya tenés cuenta? Iniciá sesión</a>
            </p>
        </div>
    </div>
@endsection
