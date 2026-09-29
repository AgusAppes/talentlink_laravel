@extends('layouts.app')

@section('title', 'Mi empresa')

@section('content')
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3">
        <a class="btn btn-outline-secondary" href="{{ route('password.edit') }}">Cambiar contraseña</a>
    </div>

    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="bg-body-tertiary rounded p-3 mb-3">
                <p class="mb-0">
                    <span class="d-block small text-secondary mb-1">Correo</span>
                    {{ auth()->user()->correo }}
                </p>
            </div>

            <form method="POST" action="{{ route('empresas.perfil.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label" for="nombre">Nombre de la empresa</label>
                    <input class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" type="text" maxlength="100" value="{{ old('nombre', $empresa->nombre) }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Guardar cambios</button>
                </div>
            </form>
        </div>
    </section>
@endsection
