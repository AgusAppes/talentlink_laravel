@extends('layouts.app')

@section('title', 'Elegir horario')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/calendario.css') }}">
@endpush

@section('content')
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <p class="mb-1">{{ $postulacion->oferta->busqueda->nombre_puesto }}</p>
            <p class="text-secondary small mb-3">Elegí un horario en blanco. Los espacios en gris no están disponibles.</p>

            @error('inicio')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            @if ($sinHuecos)
                <p class="text-secondary small">No hay horarios disponibles en los próximos 14 días.</p>
            @endif

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                @if ($puedeAnterior)
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('entrevistas.elegir', ['postulacion' => $postulacion, 'fecha' => $inicioSemana->copy()->subWeek()->toDateString()]) }}">Anterior</a>
                @else
                    <span class="btn btn-sm btn-outline-secondary disabled">Anterior</span>
                @endif
                <p class="fw-semibold mb-0">{{ $inicioSemana->format('d/m/Y') }} – {{ $inicioSemana->copy()->addDays(6)->format('d/m/Y') }}</p>
                @if ($puedeSiguiente)
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('entrevistas.elegir', ['postulacion' => $postulacion, 'fecha' => $inicioSemana->copy()->addWeek()->toDateString()]) }}">Siguiente</a>
                @else
                    <span class="btn btn-sm btn-outline-secondary disabled">Siguiente</span>
                @endif
            </div>

            <div class="table-responsive tl-grilla">
                <table class="table table-bordered mb-0 small">
                    <thead>
                        <tr>
                            <th></th>
                            @foreach ($dias as $dia)
                                <th class="text-center fw-normal">
                                    <span class="d-block">{{ \App\Http\Controllers\CalendarioController::DIAS[$dia->dayOfWeekIso] }}</span>
                                    <span class="fw-semibold">{{ $dia->format('d/m') }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($slots as $indice => $slot)
                            <tr>
                                <th class="text-secondary fw-normal text-end pe-2">{{ $indice % 2 === 0 ? $slot : '' }}</th>
                                @foreach ($dias as $dia)
                                    @php
                                        $clave = $dia->toDateString().' '.$slot;
                                        $libre = $libres[$clave] ?? false;
                                        $cuando = \App\Http\Controllers\CalendarioController::DIAS[$dia->dayOfWeekIso].' '.$dia->format('d/m').' · '.$slot;
                                    @endphp
                                    <td class="{{ $libre ? 'bg-white' : 'bg-secondary-subtle' }}">
                                        @if ($libre)
                                            <form class="h-100" method="POST" action="{{ route('entrevistas.confirmar', $postulacion) }}">
                                                @csrf
                                                <input type="hidden" name="inicio" value="{{ $clave }}">
                                                <button class="btn w-100 h-100 rounded-0 border-0 p-0" type="submit" aria-label="{{ $cuando }}"></button>
                                            </form>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
