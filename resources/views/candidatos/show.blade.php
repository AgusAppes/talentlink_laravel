@extends('layouts.app')

@section('title', 'Detalle del candidato')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ofertas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/postulaciones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/candidatos.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
@endpush

@section('content')
    <a class="can-volver" href="{{ route('candidatos.index') }}">← Volver a candidatos</a>

    <section class="tl-card">
        <div class="per-foto-fila">
            @if ($candidato->foto)
                <img class="per-foto" src="{{ asset('storage/'.$candidato->foto) }}" alt="Foto de {{ $candidato->nombre }}">
            @else
                <span class="per-avatar">{{ $candidato->usuario->iniciales() }}</span>
            @endif
        </div>

        <dl class="can-datos">
            <div>
                <dt>Nombre y apellido</dt>
                <dd>{{ $candidato->nombre }} {{ $candidato->apellido }}</dd>
            </div>
            <div>
                <dt>Correo</dt>
                <dd>{{ $candidato->usuario->correo }}</dd>
            </div>
            <div>
                <dt>Fecha de nacimiento</dt>
                <dd>{{ $candidato->fecha_nac?->format('d/m/Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt>Ubicación</dt>
                <dd>
                    @if ($candidato->ciudad)
                        {{ $candidato->ciudad->nombre }}, {{ $candidato->ciudad->provincia->nombre }}
                    @else
                        —
                    @endif
                </dd>
            </div>
        </dl>
    </section>

    <section class="tl-card can-bloque">
        <h2>Acerca de</h2>
        @if ($candidato->descripcion)
            <p class="exp-texto">{!! nl2br(e($candidato->descripcion)) !!}</p>
        @else
            <p class="sol-vacio">Sin descripción.</p>
        @endif
    </section>

    <section class="tl-card can-bloque">
        <h2>Habilidades</h2>
        @if ($candidato->habilidades->isEmpty())
            <p class="sol-vacio">Sin habilidades cargadas.</p>
        @else
            <div class="tag-lista">
                @foreach ($candidato->habilidades as $habilidad)
                    <span class="tag">{{ $habilidad->nombre }}</span>
                @endforeach
            </div>
        @endif
    </section>

    <section class="tl-card can-bloque">
        <h2>Experiencia laboral</h2>
        @include('candidatos._experiencias', [
            'experiencias' => $candidato->experiencias,
            'editable' => false,
            'vacio' => 'Sin experiencias cargadas.',
        ])
    </section>

    <section class="tl-card can-bloque">
        <h2>Postulaciones</h2>

        @if ($candidato->postulaciones->isEmpty())
            <p class="sol-vacio">Este candidato todavía no se postuló a ninguna oferta.</p>
        @else
            <div class="sol-tabla-wrap">
                <table class="sol-tabla">
                    <thead>
                        <tr>
                            <th>Puesto</th>
                            <th>Empresa</th>
                            <th>Estado de la oferta</th>
                            <th>Etapa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($candidato->postulaciones as $postulacion)
                            <tr>
                                <td>{{ $postulacion->oferta->busqueda->nombre_puesto }}</td>
                                <td>{{ $postulacion->oferta->busqueda->empresa->nombre }}</td>
                                <td>
                                    <span class="ofe-badge ofe-badge-{{ $postulacion->oferta->estado_ofertas_id }}">{{ $postulacion->oferta->estado->nombre }}</span>
                                </td>
                                <td>
                                    <span class="pos-badge pos-badge-{{ $postulacion->etapas_id }}">{{ $postulacion->etapa->nombre }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
