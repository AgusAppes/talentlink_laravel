@extends('layouts.app')

@section('title', 'Mi empresa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perfiles.css') }}">
@endpush

@section('content')
    <div class="sol-toolbar">
        <a class="sol-btn-sec" href="{{ route('password.edit') }}">Cambiar contraseña</a>
    </div>

    <section class="tl-card">
        <div class="per-lectura">
            <p><span>Correo</span>{{ auth()->user()->correo }}</p>
        </div>

        <form method="POST" action="{{ route('empresas.perfil.update') }}">
            @csrf
            @method('PUT')

            <div class="sol-campo">
                <label for="nombre">Nombre de la empresa</label>
                <input id="nombre" name="nombre" type="text" maxlength="100" value="{{ old('nombre', $empresa->nombre) }}" required>
                @error('nombre')
                    <p class="sol-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sol-acciones">
                <button class="sol-btn" type="submit">Guardar cambios</button>
            </div>
        </form>
    </section>
@endsection
