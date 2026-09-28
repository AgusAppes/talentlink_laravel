@extends('layouts.app')

@section('title', 'Cambiar contraseña')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perfiles.css') }}">
@endpush

@section('content')
    <section class="tl-card pass-caja">
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')

            <div class="sol-campo">
                <label for="password_actual">Contraseña actual</label>
                <input id="password_actual" name="password_actual" type="password" required>
                @error('password_actual')
                    <p class="sol-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sol-campo">
                <label for="password">Nueva contraseña</label>
                <input id="password" name="password" type="password" required>
                <p class="sol-ayuda">Mínimo 8 caracteres.</p>
                @error('password')
                    <p class="sol-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sol-campo">
                <label for="password_confirmation">Repetir nueva contraseña</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required>
                @error('password_confirmation')
                    <p class="sol-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sol-acciones">
                <button class="sol-btn" type="submit">Guardar contraseña</button>
                <a class="sol-btn-sec" href="{{ route(auth()->user()->rutaInicio()) }}">Volver</a>
            </div>
        </form>
    </section>
@endsection
