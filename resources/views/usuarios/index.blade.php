@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-secondary small">{{ $usuarios->total() }} {{ $usuarios->total() === 1 ? 'usuario' : 'usuarios' }}</span>
        @if (auth()->user()->puede('usuarios.administrar'))
            <a class="btn btn-primary" href="{{ route('usuarios.create') }}">Nuevo usuario</a>
        @endif
    </div>

    <section class="card border-0 shadow-sm">
        <div class="card-body">
            @if ($usuarios->isEmpty())
                <p class="text-center text-secondary py-4 mb-0">Todavía no hay usuarios.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
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
                                        <span class="badge rounded-pill text-bg-primary">{{ $usuario->rolLegible() }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $usuarios->links() }}</div>
            @endif
        </div>
    </section>
@endsection
