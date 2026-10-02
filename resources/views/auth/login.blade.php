@extends('layouts.guest')

@section('title', 'Iniciar sesión — TalentLink')

@section('content')
    <div class="card shadow-sm w-100">
        <div class="card-body p-4">
            <h2 class="h4 mb-3">Iniciar sesión</h2>

            @if (session('ok'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('ok') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

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
                    <div class="cf-turnstile w-100" data-sitekey="{{ config('services.turnstile.site_key') }}" data-size="flexible" data-theme="light"></div>
                    @error('cf-turnstile-response')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary w-100" type="submit">Iniciar sesión</button>
            </form>

            <p class="text-center mt-3 mb-0">
                <a href="{{ route('registro') }}">¿No tenés cuenta? Registrate</a>
            </p>
        </div>
    </div>
@endsection
