@extends('layouts.app')

@section('title', 'Detalle del candidato')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/candidatos.css') }}">
@endpush

@section('content')
    <a class="d-inline-block mb-3 small text-decoration-none" href="{{ route('candidatos.index') }}">← Volver a candidatos</a>

    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="per-foto-fila">
                @if ($candidato->foto)
                    <img class="per-foto" src="{{ asset('storage/'.$candidato->foto) }}" alt="Foto de {{ $candidato->nombre }}">
                @else
                    <span class="per-avatar">{{ $candidato->usuario->iniciales() }}</span>
                @endif
            </div>

            <dl class="row g-3 mb-0">
                <div class="col-12 col-md-6">
                    <dt class="text-secondary small fw-normal">Nombre y apellido</dt>
                    <dd class="mb-0">{{ $candidato->nombre }} {{ $candidato->apellido }}</dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="text-secondary small fw-normal">Correo</dt>
                    <dd class="mb-0">{{ $candidato->usuario->correo }}</dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="text-secondary small fw-normal">Fecha de nacimiento</dt>
                    <dd class="mb-0">{{ $candidato->fecha_nac?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div class="col-12 col-md-6">
                    <dt class="text-secondary small fw-normal">Ubicación</dt>
                    <dd class="mb-0">
                        @if ($candidato->ciudad)
                            {{ $candidato->ciudad->nombre }}, {{ $candidato->ciudad->provincia->nombre }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </section>

    <section class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <h2 class="h5 fw-semibold mb-3">Acerca de</h2>
            @if ($candidato->descripcion)
                <p class="mb-0">{!! nl2br(e($candidato->descripcion)) !!}</p>
            @else
                <p class="text-center text-secondary py-4 mb-0">Sin descripción.</p>
            @endif
        </div>
    </section>

    <section class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <h2 class="h5 fw-semibold mb-3">Habilidades</h2>
            @if ($candidato->habilidades->isEmpty())
                <p class="text-center text-secondary py-4 mb-0">Sin habilidades cargadas.</p>
            @else
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($candidato->habilidades as $habilidad)
                        <span class="tag">{{ $habilidad->nombre }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <h2 class="h5 fw-semibold mb-3">Experiencia laboral</h2>
            @include('candidatos._experiencias', [
                'experiencias' => $candidato->experiencias,
                'editable' => false,
                'vacio' => 'Sin experiencias cargadas.',
            ])
        </div>
    </section>

    <section class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <h2 class="h5 fw-semibold mb-3">Postulaciones</h2>

            @if ($candidato->postulaciones->isEmpty())
                <p class="text-center text-secondary py-4 mb-0">Este candidato todavía no se postuló a ninguna oferta.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
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
                                        <span class="badge rounded-pill {{ match ((int) $postulacion->oferta->estado_ofertas_id) { 1 => 'text-bg-success', 2, 3 => 'text-bg-secondary', default => 'text-bg-primary' } }}">{{ $postulacion->oferta->estado->nombre }}</span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill {{ match ((int) $postulacion->etapas_id) { 1, 2 => 'text-bg-warning', 3 => 'text-bg-danger', 4 => 'text-bg-success', default => 'text-bg-primary' } }}">{{ $postulacion->etapa->nombre }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
@endsection
