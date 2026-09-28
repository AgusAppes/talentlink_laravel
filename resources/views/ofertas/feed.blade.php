@extends('layouts.app')

@section('title', 'Ofertas disponibles')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/solicitudes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ofertas.css') }}">
    <link rel="stylesheet" href="{{ asset('css/postulaciones.css') }}">
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
                    @if ($oferta->requiere_cv)
                        <p class="ofe-cv-fila"><span class="ofe-badge ofe-cv">Requiere CV</span></p>
                    @endif
                    @if ($postuladas->contains($oferta->id))
                        <p class="pos-ya">Ya te postulaste</p>
                    @elseif ($oferta->requiere_cv)
                        <form class="pos-postular" method="POST" action="{{ route('postulaciones.store', $oferta) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="sol-campo">
                                <label for="cv-{{ $oferta->id }}">Adjuntá tu CV (PDF, máx. 5 MB)</label>
                                <input id="cv-{{ $oferta->id }}" type="file" name="cv" accept="application/pdf" required>
                                @if ((string) session('cv_oferta') === (string) $oferta->id)
                                    @error('cv')
                                        <p class="sol-error">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                            <button class="sol-btn" type="submit">Postularme</button>
                        </form>
                    @else
                        <form class="pos-postular" method="POST" action="{{ route('postulaciones.store', $oferta) }}">
                            @csrf
                            <button class="sol-btn" type="submit">Postularme</button>
                        </form>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
@endsection
