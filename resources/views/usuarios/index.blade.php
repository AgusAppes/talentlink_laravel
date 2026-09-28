@extends('layouts.app')

@section('title', 'Usuarios')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
@endpush

@section('content')
    <div class="sol-toolbar">
        <span class="sol-count">{{ $usuarios->total() }} {{ $usuarios->total() === 1 ? 'usuario' : 'usuarios' }}</span>
        @if (auth()->user()->puede('usuarios.administrar'))
            <a class="sol-btn" href="{{ route('usuarios.create') }}">Nuevo usuario</a>
        @endif
    </div>

    <section class="tl-card">
        @if ($usuarios->isEmpty())
            <p class="sol-vacio">Todavía no hay usuarios.</p>
        @else
            <div class="sol-tabla-wrap">
                <table class="sol-tabla">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->nombreVisible() }}</td>
                                <td>{{ $usuario->correo }}</td>
                                <td>
                                    <span class="sol-badge sol-badge-{{ $usuario->roles_id }}">{{ $usuario->rolLegible() }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $usuarios->links('partials.paginacion') }}
        @endif
    </section>
@endsection
