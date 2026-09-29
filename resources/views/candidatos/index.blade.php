@extends('layouts.app')

@section('title', 'Candidatos')

@section('content')
    <div class="d-flex justify-content-end align-items-center mb-3">
        <span class="text-secondary small">{{ $candidatos->total() }} {{ $candidatos->total() === 1 ? 'candidato' : 'candidatos' }}</span>
    </div>

    <section class="card shadow-sm border-0">
        @if ($candidatos->isEmpty())
            <p class="text-center text-secondary py-4 mb-0">Todavía no hay candidatos registrados.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Ciudad</th>
                            <th>Provincia</th>
                            <th>Fecha de nacimiento</th>
                            <th>Postulaciones</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($candidatos as $candidato)
                            <tr>
                                <td>{{ $candidato->apellido }}, {{ $candidato->nombre }}</td>
                                <td>{{ $candidato->usuario->correo }}</td>
                                <td>{{ $candidato->ciudad?->nombre ?? '—' }}</td>
                                <td>{{ $candidato->ciudad?->provincia?->nombre ?? '—' }}</td>
                                <td>{{ $candidato->fecha_nac?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $candidato->postulaciones_count }}</td>
                                <td>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('candidatos.show', $candidato) }}">Ver detalle</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body py-3">
                {{ $candidatos->links() }}
            </div>
        @endif
    </section>
@endsection
