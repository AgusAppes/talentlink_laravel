@extends('layouts.guest')

@section('title', 'Iniciar sesión — TalentLink')

@section('content')
    <div class="lg-card">
        <h2 class="lg-card-title">Iniciar sesión</h2>

        @if (session('ok'))
            <p class="lg-ok">{{ session('ok') }}</p>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

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

            <button class="lg-btn" type="submit">Iniciar sesión</button>
        </form>

        <p class="lg-switch">
            <a href="{{ route('registro') }}">¿No tenés cuenta? Registrate</a>
        </p>
    </div>
@endsection
