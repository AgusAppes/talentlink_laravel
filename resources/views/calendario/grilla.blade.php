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

        @if ($sinHorario)
            <p class="text-secondary small">Todavía no cargaste tus horarios. Los candidatos no van a poder elegir un turno hasta que los guardes en <a href="{{ route('perfil.horario') }}">Mi perfil</a>.</p>
        @endif

        @if ($errors->any() && old('tipo'))
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $mensaje)
                        <li>{{ $mensaje }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge rounded-pill bg-white text-dark border">Disponible</span>
            <span class="badge rounded-pill bg-secondary-subtle text-dark">No disponible</span>
            <span class="badge rounded-pill bg-primary-subtle text-dark">Entrevista</span>
            <span class="badge rounded-pill bg-info-subtle text-dark">Agendado</span>
            <span class="badge rounded-pill bg-secondary-subtle text-dark border">Cancelada</span>
        </div>

        <div class="table-responsive tl-grilla">
            <table class="table table-bordered mb-0 small">
                <thead>
                    <tr>
                        <th></th>
                        @foreach ($dias as $dia)
                            <th class="text-center fw-normal {{ $dia->toDateString() === $hoy ? 'tl-hoy tl-hoy-inicio' : '' }}">
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
                                    $fecha = $dia->toDateString();
                                    $cubierto = $cubiertos[$fecha][$slot] ?? false;
                                @endphp
                                @if ($cubierto)
                                    @continue
                                @endif
                                @php
                                    $celda = $celdas[$fecha][$slot];
                                    $evento = $celda['evento'] ?? null;
                                    $span = $celda['span'] ?? 1;
                                    $finSlot = \Illuminate\Support\Carbon::createFromFormat('H:i', $slot)->addMinutes(30)->format('H:i');
                                    $cuando = \App\Http\Controllers\CalendarioController::DIAS[$dia->dayOfWeekIso].' '.$dia->format('d/m').' · '.$slot;
                                @endphp
                                <td rowspan="{{ $span }}" class="align-top {{ $dia->toDateString() === $hoy ? 'tl-hoy'.(($indice + $span) >= count($slots) ? ' tl-hoy-fin' : '') : ($evento ? '' : (($celda['disponible'] ?? false) ? 'bg-white' : 'bg-secondary-subtle')) }}">
                                    @if ($evento)
                                        @if ($evento['tipo'] === 'entrevista')
                                            @php $entrevista = $evento['entrevista']; @endphp
                                            <div class="rounded p-1 h-100 {{ $entrevista->estado === 'cancelada' ? 'bg-secondary-subtle' : 'bg-primary-subtle' }}">
                                                <span class="d-block fw-semibold">{{ $entrevista->titulo() }}</span>
                                                <span class="d-block">{{ $entrevista->inicioLocal()->format('H:i') }} · {{ $entrevista->duracion_minutos }} min</span>
                                                @if ($entrevista->estado === 'cancelada')
                                                    <span class="d-block">{{ $entrevista->textoCancelacion() }}</span>
                                                @else
                                                    @include('entrevistas.cancelar', ['entrevista' => $entrevista])
                                                @endif
                                            </div>
                                        @else
                                            @php $bloqueo = $evento['bloqueo']; @endphp
                                            <div class="rounded p-1 h-100 {{ $evento['tipo'] === 'agenda' ? 'bg-info-subtle' : 'bg-secondary-subtle' }}">
                                                <span class="d-block fw-semibold">{{ $evento['tipo'] === 'agenda' ? $bloqueo->nota : 'No disponible' }}</span>
                                                @if ($evento['tipo'] !== 'agenda' && $bloqueo->nota && $bloqueo->nota !== 'No disponible')
                                                    <span class="d-block">{{ $bloqueo->nota }}</span>
                                                @endif
                                                @if (! $bloqueo->esDiaEntero())
                                                    <span class="d-block">{{ substr($bloqueo->hora_inicio, 0, 5) }}–{{ substr($bloqueo->hora_fin, 0, 5) }}</span>
                                                @endif
                                                <form method="POST" action="{{ route('calendario.bloqueos.destroy', $bloqueo) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="fecha_actual" value="{{ $fechaActual->toDateString() }}">
                                                    <button class="btn btn-sm btn-outline-secondary mt-1" type="submit">Quitar</button>
                                                </form>
                                            </div>
                                        @endif
                                    @else
                                        <button class="btn w-100 h-100 rounded-0 border-0 p-0" type="button" data-bs-toggle="modal" data-bs-target="#accionCalendario" data-fecha="{{ $fecha }}" data-hora="{{ $slot }}" data-fin="{{ $finSlot }}" data-cuando="{{ $cuando }}" aria-label="{{ $cuando }}"></button>
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

<div class="modal fade" id="accionCalendario" tabindex="-1" aria-labelledby="accionCalendarioTitulo" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('calendario.bloqueos.store') }}">
            @csrf
            <div class="modal-header">
                <h2 class="modal-title h5" id="accionCalendarioTitulo">Ese horario</h2>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="fw-semibold" data-cuando>Elegí un horario en el calendario.</p>
                <input type="hidden" name="fecha_actual" value="{{ $fechaActual->toDateString() }}">
                <input type="hidden" name="fecha" value="{{ old('fecha') }}">
                <input type="hidden" name="hora_inicio" value="{{ old('hora_inicio') }}">
                <div class="mb-3">
                    <label class="form-label" for="hora-fin-accion">Hasta</label>
                    <select class="form-select" id="hora-fin-accion" name="hora_fin"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="nota-accion">Título o nota</label>
                    <input class="form-control @error('nota') is-invalid @enderror" type="text" id="nota-accion" name="nota" maxlength="200" value="{{ old('nota') }}" placeholder="Entrevista interna, feriado, trámites…">
                    @error('nota')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="dia-entero" name="dia_entero" value="1" @checked(old('dia_entero') === '1')>
                    <label class="form-check-label" for="dia-entero">Día entero</label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="submit" name="tipo" value="no_disponible">No disponible</button>
                <button class="btn btn-primary" type="submit" name="tipo" value="agenda">Agendar</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        const modal = document.getElementById('accionCalendario');

        const llenarHasta = (inicio, seleccionado) => {
            const campo = modal.querySelector('[name=hora_fin]');
            const partes = inicio.split(':').map(Number);
            let actual = (partes[0] * 60) + partes[1] + 30;
            const limite = 20 * 60;

            campo.innerHTML = '';

            while (actual <= limite) {
                const texto = String(Math.floor(actual / 60)).padStart(2, '0') + ':' + String(actual % 60).padStart(2, '0');
                const opcion = document.createElement('option');
                opcion.value = texto;
                opcion.textContent = texto;
                opcion.selected = texto === seleccionado;
                campo.appendChild(opcion);
                actual += 30;
            }
        };

        modal?.addEventListener('show.bs.modal', (evento) => {
            const boton = evento.relatedTarget;

            if (!boton) {
                return;
            }

            modal.querySelector('[name=fecha]').value = boton.dataset.fecha;
            modal.querySelector('[name=hora_inicio]').value = boton.dataset.hora;
            modal.querySelector('[data-cuando]').textContent = boton.dataset.cuando;
            llenarHasta(boton.dataset.hora, boton.dataset.fin);
        });

        @if ($errors->any() && old('tipo') && old('hora_inicio'))
            llenarHasta(@json(substr((string) old('hora_inicio'), 0, 5)), @json(substr((string) old('hora_fin'), 0, 5)));
            window.bootstrap?.Modal.getOrCreateInstance(modal).show();
        @endif
    </script>
@endpush
