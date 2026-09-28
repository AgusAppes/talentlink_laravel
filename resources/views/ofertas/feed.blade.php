@extends('layouts.app')

@section('title', 'Ofertas disponibles')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/ofertas.css') }}">
@endpush

@section('content')
    @if ($ofertas->isEmpty())
        <section class="tl-card">
            <p class="sol-vacio">No hay ofertas disponibles por el momento.</p>
        </section>
    @else
        <div class="ofe-feed">
            @foreach ($ofertas as $oferta)
                <article class="ofe-card">
                    <h2>{{ $oferta->busqueda->nombre_puesto }}</h2>
                    <p class="ofe-empresa">{{ $oferta->busqueda->empresa->nombre }}</p>
                    <p class="ofe-dato">{{ $oferta->busqueda->detalle?->modalidad?->nombre ?? '—' }}</p>
                    <p class="ofe-dato">
                        @if ($oferta->busqueda->detalle?->ciudad)
                            {{ $oferta->busqueda->detalle->ciudad->nombre }}, {{ $oferta->busqueda->detalle->ciudad->provincia->nombre }}
                        @else
                            —
                        @endif
                    </p>
                    <p class="ofe-dato">{{ $oferta->busqueda->detalle?->cantidad_vacantes ?? '—' }} vacantes</p>
                    <p class="ofe-dato">
                        @if ($oferta->busqueda->detalle?->anios_experiencia === null || (int) $oferta->busqueda->detalle->anios_experiencia === 0)
                            Sin experiencia requerida
                        @elseif ((int) $oferta->busqueda->detalle->anios_experiencia === 1)
                            1 año
                        @else
                            {{ $oferta->busqueda->detalle->anios_experiencia }} años
                        @endif
                    </p>
                    @if ($oferta->busqueda->detalle?->descripcion)
                        <p>{{ Str::limit($oferta->busqueda->detalle->descripcion, 180) }}</p>
                    @endif
                    @if ($oferta->busqueda->habilidades->isNotEmpty())
                        <ul class="ofe-habilidades">
                            @foreach ($oferta->busqueda->habilidades as $habilidad)
                                <li>{{ $habilidad->nombre }}</li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
@endsection
