@extends('layouts.app')

@section('title', 'Roles y permisos')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
@endpush

@section('content')
    <section class="tl-card">
        <div class="sol-tabla-wrap">
            <table class="sol-tabla">
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
                                    <a href="{{ route('usuarios.roles.edit', $rol) }}">Editar permisos</a>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
