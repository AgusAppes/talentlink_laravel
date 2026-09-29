@extends('layouts.app')

@section('title', 'Roles y permisos')

@section('content')
    <section class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th>Usuarios</th>
                            <th>Permisos</th>
                            @if (auth()->user()->puede('usuarios.administrar'))
                                <th></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $rol)
                            <tr>
                                <td>
                                    @if ((int) $rol->id === 1)
                                        Personal RRHH
                                    @elseif ((int) $rol->id === 2)
                                        Empresa
                                    @elseif ((int) $rol->id === 3)
                                        Candidato
                                    @else
                                        {{ $rol->nombre }}
                                    @endif
                                </td>
                                <td>{{ $rol->usuarios_count }}</td>
                                <td>{{ $rol->permisos_count }}</td>
                                @if (auth()->user()->puede('usuarios.administrar'))
                                    <td>
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('usuarios.roles.edit', $rol) }}">Editar permisos</a>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
