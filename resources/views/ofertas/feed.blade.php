@extends('layouts.app')

@section('title', 'Ofertas disponibles')

@section('content')
    @if ($ofertas->isEmpty())
        <section class="card shadow-sm border-0">
            <p class="text-center text-secondary py-4 mb-0">No hay ofertas disponibles por el momento.</p>
        </section>
    @else
        <div class="row g-3">
            @foreach ($ofertas as $oferta)
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column">
                            <h2 class="h5 mb-1">{{ $oferta->busqueda->nombre_puesto }}</h2>
                            <p class="fw-semibold mb-2">{{ $oferta->busqueda->empresa->nombre }}</p>
                            <p class="text-secondary small mb-1">{{ $oferta->busqueda->ficha?->modalidad?->nombre ?? '—' }}</p>
                            <p class="text-secondary small mb-1">
                                @if ($oferta->busqueda->ficha?->ciudad)
                                    {{ $oferta->busqueda->ficha->ciudad->nombre }}, {{ $oferta->busqueda->ficha->ciudad->provincia->nombre }}
                                @else
                                    —
                                @endif
                            </p>
                            <p class="text-secondary small mb-1">{{ $oferta->busqueda->ficha?->cantidad_vacantes ?? '—' }} vacantes</p>
                            <p class="text-secondary small mb-2">
                                @if ($oferta->busqueda->ficha?->anios_experiencia === null || (int) $oferta->busqueda->ficha->anios_experiencia === 0)
                                    Sin experiencia requerida
                                @elseif ((int) $oferta->busqueda->ficha->anios_experiencia === 1)
                                    1 año
                                @else
                                    {{ $oferta->busqueda->ficha->anios_experiencia }} años
                                @endif
                            </p>
                            @if ($oferta->busqueda->ficha?->descripcion)
                                <p class="mb-2">{{ Str::limit($oferta->busqueda->ficha->descripcion, 180) }}</p>
                            @endif
                            @if (collect($oferta->busqueda->ficha?->habilidades)->isNotEmpty())
                                <ul class="list-unstyled d-flex flex-wrap gap-1 mb-2">
                                    @foreach ($oferta->busqueda->ficha->habilidades as $habilidad)
                                        <li><span class="badge rounded-pill text-bg-primary">{{ $habilidad }}</span></li>
                                    @endforeach
                                </ul>
                            @endif
                            @if ($oferta->requiere_cv)
                                <p class="mb-2"><span class="badge rounded-pill text-bg-primary">Requiere CV</span></p>
                            @endif
                            @if ($postuladas->contains($oferta->id))
                                <p class="mb-0 mt-auto pt-3"><span class="badge rounded-pill text-bg-success">Ya te postulaste</span></p>
                            @elseif ($oferta->requiere_cv)
                                <form class="mt-auto pt-3" method="POST" action="{{ route('postulaciones.store', $oferta) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label" for="cv-{{ $oferta->id }}">Adjuntá tu CV (PDF, máx. 5 MB)</label>
                                        <input class="form-control @if ((string) session('cv_oferta') === (string) $oferta->id) @error('cv') is-invalid @enderror @endif" id="cv-{{ $oferta->id }}" type="file" name="cv" accept="application/pdf" required>
                                        @if ((string) session('cv_oferta') === (string) $oferta->id)
                                            @error('cv')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif
                                    </div>
                                    <button class="btn btn-primary" type="submit">Postularme</button>
                                </form>
                            @else
                                <form class="mt-auto pt-3" method="POST" action="{{ route('postulaciones.store', $oferta) }}">
                                    @csrf
                                    <button class="btn btn-primary" type="submit">Postularme</button>
                                </form>
                            @endif
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
        <div class="mt-3">
            {{ $ofertas->links() }}
        </div>
    @endif
@endsection
