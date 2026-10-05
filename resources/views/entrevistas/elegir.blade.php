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

            @php
                $meses = [
                    1 => 'enero',
                    2 => 'febrero',
                    3 => 'marzo',
                    4 => 'abril',
                    5 => 'mayo',
                    6 => 'junio',
                    7 => 'julio',
                    8 => 'agosto',
                    9 => 'septiembre',
                    10 => 'octubre',
                    11 => 'noviembre',
                    12 => 'diciembre',
                ];
            @endphp

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
                                        $fin = \Illuminate\Support\Carbon::createFromFormat('H:i', $slot)->addMinutes(\App\Http\Controllers\CalendarioController::DURACION)->format('H:i');
                                        $diaNombre = mb_strtolower(\App\Http\Controllers\CalendarioController::DIAS[$dia->dayOfWeekIso], 'UTF-8');
                                        $cuando = $diaNombre.' '.$dia->format('d').' de '.$meses[(int) $dia->format('n')].' de '.$slot.' a '.$fin.' hs';
                                    @endphp
                                    <td class="{{ $libre ? 'bg-white' : 'bg-secondary-subtle' }}">
                                        @if ($libre)
                                            <button class="btn w-100 h-100 rounded-0 border-0 p-0" type="button" data-bs-toggle="modal" data-bs-target="#confirmarEntrevista" data-inicio="{{ $clave }}" data-cuando="{{ $cuando }}" aria-label="{{ $cuando }}"></button>
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

    <div class="modal fade" id="confirmarEntrevista" tabindex="-1" aria-labelledby="confirmarEntrevistaTitulo" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('entrevistas.confirmar', $postulacion) }}">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title h5" id="confirmarEntrevistaTitulo">Entrevista {{ $postulacion->oferta->busqueda->nombre_puesto }}</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" data-cuando></p>
                    <input type="hidden" name="inicio" value="">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="submit">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const modal = document.getElementById('confirmarEntrevista');

        modal?.addEventListener('show.bs.modal', (evento) => {
            const boton = evento.relatedTarget;

            if (!boton) {
                return;
            }

            modal.querySelector('[name=inicio]').value = boton.dataset.inicio;
            modal.querySelector('[data-cuando]').textContent = boton.dataset.cuando;
        });
    </script>
@endpush
