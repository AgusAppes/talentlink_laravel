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
            @if (auth()->user()->esEmpresa())
                <span class="badge rounded-pill bg-info-subtle text-dark">Reunión</span>
            @else
                <span class="badge rounded-pill bg-primary-subtle text-dark">Entrevista con la consultora</span>
                <span class="badge rounded-pill bg-info-subtle text-dark">Entrevista con la empresa</span>
                <span class="badge rounded-pill bg-secondary-subtle text-dark">Cancelada</span>
            @endif
        </div>

        @if ($slots === [])
            <p class="text-secondary mb-0">{{ auth()->user()->esEmpresa() ? 'No hay reuniones agendadas en esta semana.' : 'No hay entrevistas en esta semana.' }}</p>
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
                    @foreach ($slots as $slot)
                        <tr>
                            <td class="text-nowrap">{{ $slot }}</td>
                            @foreach ($dias as $dia)
                                @php
                                    $clave = $dia->toDateString();
                                    $turnos = $entrevistasPorSlot[$clave][$slot] ?? [];
                                @endphp
                                <td class="{{ $clave === $hoy ? 'tl-hoy'.($loop->parent->last ? ' tl-hoy-fin' : '') : '' }}">
                                    @foreach ($turnos as $entrevista)
                                        @if ($entrevista instanceof \App\Models\EntrevistaEmpresa)
                                            <div class="rounded p-1 mb-1 bg-info-subtle">
                                                <span class="d-block fw-semibold">{{ auth()->user()->esEmpresa() ? $entrevista->tituloEmpresa() : $entrevista->tituloCandidato() }}</span>
                                                <span class="d-block">{{ $entrevista->inicioLocal()->format('H:i') }}</span>
                                                @if ($entrevista->nota)
                                                    <span class="d-block">{{ $entrevista->nota }}</span>
                                                @endif
                                                @if ($entrevista->enlace)
                                                    @include('entrevistas.enlace', ['entrevista' => $entrevista])
                                                @elseif (auth()->user()->esCandidato() && ! $entrevista->yaEmpezo())
                                                    <form method="POST" action="{{ route('envios.entrevista.confirmar', $entrevista->envio) }}">
                                                        @csrf
                                                        <button class="btn btn-sm btn-primary mt-1" type="submit">Confirmar</button>
                                                    </form>
                                                @endif
                                            </div>
                                        @else
                                        <div class="rounded p-1 mb-1 {{ $entrevista->estado === 'cancelada' ? 'bg-secondary-subtle' : 'bg-primary-subtle' }}">
                                            <span class="d-block fw-semibold">{{ $entrevista->tituloCandidato() }}</span>
                                            <span class="d-block">{{ $entrevista->inicioLocal()->format('H:i') }} · {{ $entrevista->duracion_minutos }} min</span>
                                            @if ($entrevista->estado === 'cancelada')
                                                <span class="d-block">{{ $entrevista->textoCancelacion() }}</span>
                                            @else
                                                @include('entrevistas.enlace', ['entrevista' => $entrevista])
                                                @unless ($entrevista->yaEmpezo())
                                                    @include('entrevistas.cancelar', ['entrevista' => $entrevista])
                                                @endunless
                                            @endif
                                        </div>
                                        @endif
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
