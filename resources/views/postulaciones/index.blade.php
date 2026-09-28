@extends('layouts.app')

@section('title', 'Postulaciones')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ofertas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/postulaciones.css') }}">
@endpush

@section('content')
    <div class="sol-toolbar">
        <span class="sol-count">{{ $postulaciones->total() }} {{ $postulaciones->total() === 1 ? 'postulación' : 'postulaciones' }}</span>
    </div>

    <section class="tl-card">
        @if ($postulaciones->isEmpty())
            <p class="sol-vacio">Todavía no hay postulaciones.</p>
        @else
            @error('etapas_id')
                <p class="sol-error">{{ $message }}</p>
            @enderror

            <div class="sol-tabla-wrap">
                <table class="sol-tabla">
                    <thead>
                        <tr>
                            <th>Candidato</th>
                            <th>Puesto</th>
                            <th>Empresa</th>
                            <th>Estado de la oferta</th>
                            <th>Etapa</th>
                            <th>CV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($postulaciones as $postulacion)
                            <tr>
                                <td>
                                    @if ($candidato = $postulacion->candidatos->first())
                                        {{ $candidato->nombre }} {{ $candidato->apellido }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $postulacion->oferta->busqueda->nombre_puesto }}</td>
                                <td>{{ $postulacion->oferta->busqueda->empresa->nombre }}</td>
                                <td>
                                    <span class="ofe-badge ofe-badge-{{ $postulacion->oferta->estado_ofertas_id }}">{{ $postulacion->oferta->estado->nombre }}</span>
                                </td>
                                <td>
                                    <span class="pos-badge pos-badge-{{ $postulacion->etapas_id }}">{{ $postulacion->etapa->nombre }}</span>
                                    <form class="sol-estado" method="POST" action="{{ route('postulaciones.etapa', $postulacion) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="etapas_id">
                                            @foreach ($etapas as $etapa)
                                                <option value="{{ $etapa->id }}" @selected((int) $etapa->id === (int) $postulacion->etapas_id)>
                                                    {{ $etapa->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit">Guardar</button>
                                    </form>
                                </td>
                                <td>
                                    @if ($postulacion->cv)
                                        <a href="{{ route('postulaciones.cv', $postulacion->id) }}" target="_blank">Ver CV</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $postulaciones->links('partials.paginacion') }}
        @endif
    </section>
@endsection
