@extends('layouts.app')

@section('title', 'Candidatos')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
@endpush

@section('content')
    <div class="sol-toolbar">
        // Muestra el total de candidatos con la palabra correcta (candidato o candidatos) dependiendo de la cantidad
        <span class="sol-count">{{ $candidatos->total() }} {{ $candidatos->total() === 1 ? 'candidato' : 'candidatos' }}</span>
    </div>

    <section class="tl-card">
        @if ($candidatos->isEmpty())
            <p class="sol-vacio">Todavía no hay candidatos registrados.</p>
        @else
            <div class="sol-tabla-wrap">
                <table class="sol-tabla">
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
                                    <a href="{{ route('candidatos.show', $candidato) }}">Ver detalle</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $candidatos->links('partials.paginacion') }}
        @endif
    </section>
@endsection
