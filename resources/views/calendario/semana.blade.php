@push('styles')
    <link rel="stylesheet" href="{{ asset('css/calendario.css') }}">
@endpush

<section class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('calendario', ['fecha' => $inicioSemana->copy()->subWeek()->toDateString()]) }}">Anterior</a>
            <p class="fw-semibold mb-0">{{ $inicioSemana->format('d/m/Y') }} – {{ $inicioSemana->copy()->addDays(6)->format('d/m/Y') }}</p>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('calendario', ['fecha' => $inicioSemana->copy()->addWeek()->toDateString()]) }}">Siguiente</a>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge rounded-pill bg-primary-subtle text-dark">Entrevista</span>
            <span class="badge rounded-pill bg-secondary-subtle text-dark">Cancelada</span>
        </div>

        @if ($horas === [])
            <p class="text-secondary mb-0">No hay entrevistas en esta semana.</p>
        @else
        <div class="table-responsive">
            <table class="table table-bordered align-top mb-0 small">
                <thead>
                    <tr>
                        <th>Hora</th>
                        @foreach ($dias as $dia)
                            <th class="{{ $dia->toDateString() === $hoy ? 'tl-hoy tl-hoy-inicio' : '' }}">
                                <span class="d-block">{{ \App\Http\Controllers\CalendarioController::DIAS[$dia->dayOfWeekIso] }}</span>
                                <span class="fw-normal">{{ $dia->format('d/m') }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($horas as $hora)
                        <tr>
                            <td class="text-nowrap">{{ sprintf('%02d:00', $hora) }}</td>
                            @foreach ($dias as $dia)
                                @php
                                    $clave = $dia->toDateString();
                                    $turnos = $entrevistasPorHora[$clave][$hora] ?? [];
                                @endphp
                                <td class="{{ $clave === $hoy ? 'tl-hoy'.($loop->parent->last ? ' tl-hoy-fin' : '') : '' }}">
                                    @foreach ($turnos as $entrevista)
                                        <div class="rounded p-1 mb-1 {{ $entrevista->estado === 'cancelada' ? 'bg-secondary-subtle' : 'bg-primary-subtle' }}">
                                            <span class="d-block fw-semibold">{{ $entrevista->tituloCandidato() }}</span>
                                            <span class="d-block">{{ $entrevista->inicioLocal()->format('H:i') }} · {{ $entrevista->duracion_minutos }} min</span>
                                            @if ($entrevista->estado === 'cancelada')
                                                <span class="d-block">{{ $entrevista->textoCancelacion() }}</span>
                                            @else
                                                @include('entrevistas.cancelar', ['entrevista' => $entrevista])
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</section>
