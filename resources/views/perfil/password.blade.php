@extends('layouts.app')

@section('title', 'Cambiar contraseña')

@section('content')
    <div class="row">
        <div class="col-12 col-md-8 col-lg-6">
            <section class="card shadow-sm border-0">
                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="password_actual">Contraseña actual</label>
                            <input id="password_actual" name="password_actual" type="password" class="form-control @error('password_actual') is-invalid @enderror" required>
                            @error('password_actual')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Nueva contraseña</label>
                            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
                            <p class="form-text">Mínimo 8 caracteres.</p>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password_confirmation">Repetir nueva contraseña</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" required>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Guardar contraseña</button>
                            <a class="btn btn-outline-secondary" href="{{ route(auth()->user()->rutaInicio()) }}">Volver</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection
