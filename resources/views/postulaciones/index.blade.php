@extends('layouts.app')

@section('title', 'Postulaciones')

@section('content')
    <div class="d-flex justify-content-end align-items-center mb-3">
        <span class="text-secondary small">{{ $postulaciones->total() }} {{ $postulaciones->total() === 1 ? 'postulación' : 'postulaciones' }}</span>
    </div>

    <section class="card shadow-sm border-0">
        @if ($postulaciones->isEmpty())
            <p class="text-center text-secondary py-4 mb-0">Todavía no hay postulaciones.</p>
        @else
            @error('etapas_id')
                <div class="invalid-feedback d-block px-3 pt-3">{{ $message }}</div>
            @enderror

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                                    <span class="badge rounded-pill {{ match ((int) $postulacion->oferta->estado_ofertas_id) { 1 => 'text-bg-success', 2, 3 => 'text-bg-secondary', default => 'text-bg-primary' } }}">{{ $postulacion->oferta->estado->nombre }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ match ((int) $postulacion->etapas_id) { 1, 2 => 'text-bg-warning', 3 => 'text-bg-danger', 4 => 'text-bg-success', default => 'text-bg-primary' } }}">{{ $postulacion->etapa->nombre }}</span>
                                    <form class="d-flex gap-2 align-items-center mt-2" method="POST" action="{{ route('postulaciones.etapa', $postulacion) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select class="form-select form-select-sm w-auto" name="etapas_id">
                                            @foreach ($etapas as $etapa)
                                                <option value="{{ $etapa->id }}" @selected((int) $etapa->id === (int) $postulacion->etapas_id)>
                                                    {{ $etapa->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-outline-secondary flex-shrink-0" type="submit">Guardar</button>
                                    </form>
                                </td>
                                <td>
                                    @if ($postulacion->cv)
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('postulaciones.cv', $postulacion->id) }}" target="_blank">Ver CV</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body py-3">
                {{ $postulaciones->links() }}
            </div>
        @endif
    </section>
@endsection
