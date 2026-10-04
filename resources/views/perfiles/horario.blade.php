@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
    @php
        $editando = $errors->any();
        $horas = [];

        // Este bucle crea un array de horas desde las 6:00 hasta las 22:00, cada 30 minutos
        for ($minuto = 6 * 60; $minuto <= 22 * 60; $minuto += 30) {
            // Este sprintf forma el formato de la hora en formato HH:MM
            // intdiv es una función que divide el minuto por 60 y devuelve el cociente
            // $minuto % 60 es el resto de la división del minuto por 60
            $horas[] = sprintf('%02d:%02d', intdiv($minuto, 60), $minuto % 60);
        }
    @endphp

    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-3">
                <h2 class="h6 mb-0">Horario de disponibilidad</h2>
                <button class="btn btn-sm btn-outline-secondary {{ $editando ? 'd-none' : '' }}" type="button" id="editar-horario" aria-label="Editar horario">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                    </svg>
                </button>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $mensaje)
                            <li>{{ $mensaje }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('perfil.horario.update') }}" id="form-horario">
                @csrf
                @method('PUT')

                <div class="table-responsive">
                    <table class="table align-middle mb-3">
                        <thead>
                            <tr>
                                <th>Días</th>
                                <th>Horario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (\App\Http\Controllers\CalendarioController::DIAS as $dia => $nombre)
                                @php
                                    $inicio = $editando ? old("franjas.$dia.inicio", '') : ($franjas[$dia]['inicio'] ?? '');
                                    $fin = $editando ? old("franjas.$dia.fin", '') : ($franjas[$dia]['fin'] ?? '');
                                    $marcado = $editando ? in_array($dia, array_map('intval', old('dias', [])), true) : $inicio !== '';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input dia-horario" type="checkbox" id="dia-{{ $dia }}" name="dias[]" value="{{ $dia }}" @checked($marcado) @disabled(! $editando)>
                                            <label class="form-check-label" for="dia-{{ $dia }}">{{ $nombre }}</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <span>de</span>
                                            <select class="form-select w-auto hora-horario" name="franjas[{{ $dia }}][inicio]" aria-label="Inicio {{ $nombre }}" @disabled(! $editando || ! $marcado) @required($editando && $marcado)>
                                                <option value="">—</option>
                                                @foreach ($horas as $hora)
                                                    <option value="{{ $hora }}" @selected($inicio === $hora)>{{ $hora }}</option>
                                                @endforeach
                                            </select>
                                            <span>a</span>
                                            <select class="form-select w-auto hora-horario" name="franjas[{{ $dia }}][fin]" aria-label="Fin {{ $nombre }}" @disabled(! $editando || ! $marcado) @required($editando && $marcado)>
                                                <option value="">—</option>
                                                @foreach ($horas as $hora)
                                                    <option value="{{ $hora }}" @selected($fin === $hora)>{{ $hora }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="{{ $editando ? '' : 'd-none' }}" id="acciones-horario">
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Guardar horario</button>
                        <a class="btn btn-outline-secondary" href="{{ route('perfil.horario') }}">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const formHorario = document.getElementById('form-horario');

        const sincronizarDia = (casilla) => {
            const fila = casilla.closest('tr');

            fila.querySelectorAll('select.hora-horario').forEach((campo) => {
                campo.disabled = !casilla.checked;
                campo.required = casilla.checked;
            });
        };

        document.getElementById('editar-horario')?.addEventListener('click', () => {
            formHorario.querySelectorAll('.dia-horario').forEach((casilla) => {
                casilla.disabled = false;
                sincronizarDia(casilla);
            });
            document.getElementById('editar-horario').classList.add('d-none');
            document.getElementById('acciones-horario').classList.remove('d-none');
        });

        formHorario?.querySelectorAll('.dia-horario').forEach((casilla) => {
            casilla.addEventListener('change', () => sincronizarDia(casilla));
        });
    </script>
@endpush
