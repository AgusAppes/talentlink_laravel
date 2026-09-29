@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')
    <section class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf

                <fieldset class="mb-4">
                    <legend class="fs-6 fw-semibold mb-3">Cuenta</legend>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="roles_id">Rol</label>
                            <select class="form-select @error('roles_id') is-invalid @enderror" id="roles_id" name="roles_id" required>
                                <option value="1" @selected((string) old('roles_id', '1') === '1')>Personal RRHH</option>
                                <option value="2" @selected((string) old('roles_id') === '2')>Empresa</option>
                            </select>
                            @error('roles_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="correo">Correo</label>
                            <input class="form-control @error('correo') is-invalid @enderror" id="correo" name="correo" type="email" maxlength="100" value="{{ old('correo') }}" required>
                            @error('correo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="password">Contraseña</label>
                            <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mb-4">
                    <legend class="fs-6 fw-semibold mb-2">Si es Personal RRHH</legend>
                    <p class="form-text mb-3">Completá nombre y apellido solo si el rol elegido es Personal RRHH.</p>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="nombre">Nombre</label>
                            <input class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" type="text" maxlength="100" value="{{ old('nombre') }}">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="apellido">Apellido</label>
                            <input class="form-control @error('apellido') is-invalid @enderror" id="apellido" name="apellido" type="text" maxlength="100" value="{{ old('apellido') }}">
                            @error('apellido')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mb-4">
                    <legend class="fs-6 fw-semibold mb-2">Si es Empresa</legend>
                    <p class="form-text mb-3">Completá el nombre solo si el rol elegido es Empresa.</p>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="empresa_nombre">Nombre de la empresa</label>
                            <input class="form-control @error('empresa_nombre') is-invalid @enderror" id="empresa_nombre" name="empresa_nombre" type="text" maxlength="100" value="{{ old('empresa_nombre') }}">
                            @error('empresa_nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Crear usuario</button>
                    <a class="btn btn-outline-secondary" href="{{ route('usuarios.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </section>
@endsection
