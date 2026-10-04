<?php

namespace App\Http\Controllers;

use App\Models\Bloqueo;
use App\Models\Entrevista;
use App\Models\PersonalRrhh;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalendarioController extends Controller
{
    public const ZONA = 'America/Argentina/Buenos_Aires';

    public const DURACION = 30;

    public const DIAS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    // Esta función muestra el calendario según el rol a partir de la fecha actual
    // En terminos tecnicos, cuando se visita /calendario, se ejecuta esta función
    // y sirve la semana del reclutador o del candidato
    public function index(Request $request)
    {
        $usuario = $request->user();
        $fechaActual = $this->fechaActual($request);

        if ($usuario->esCandidato()) {
            $candidato = $usuario->candidato;

            return view('calendario.index', array_merge(
                $this->armar($fechaActual, null, $candidato->id),
                ['editar' => false]
            ));
        }

        $reclutador = $usuario->personalRrhh;
        abort_unless($reclutador && $usuario->puede('postulaciones.gestionar'), 403);

        return view('calendario.index', array_merge(
            $this->armar($fechaActual, $reclutador, null),
            [
                'editar' => true,
                'sinHorario' => $reclutador->disponibilidades()->doesntExist(),
            ]
        ));
    }

    // Esta función muestra el horario semanal en el perfil del reclutador
    // En terminos tecnicos, cuando se visita /mi-perfil/horario, se ejecuta esta función
    // y sirve el formulario de franjas semanales
    public function horario(Request $request)
    {
        $reclutador = $this->reclutador($request);

        return view('perfiles.horario', [
            'reclutador' => $reclutador,
            'franjas' => $this->franjasParaFormulario($reclutador),
        ]);
    }

    // Esta función guarda el horario semanal
    // En terminos tecnicos, cuando el reclutador envía el formulario de su perfil, se ejecuta esta función
    // y reemplaza las filas de disponibilidades
    public function guardarHorario(Request $request)
    {
        $reclutador = $this->reclutador($request);

        $datos = $request->validate([
            'dias' => ['nullable', 'array'],
            'franjas' => ['nullable', 'array'],
        ]);

        $dias = array_map('intval', $datos['dias'] ?? []);
        $filas = $this->filasDeHorario($datos['franjas'] ?? [], $dias);

        DB::transaction(function () use ($reclutador, $filas) {
            $reclutador->disponibilidades()->delete();
            if ($filas !== []) {
                $reclutador->disponibilidades()->createMany($filas);
            }
        });

        return redirect()
            ->route('perfil.horario')
            ->with('ok', 'Guardaste tu horario de entrevistas.');
    }

    // Esta función agenda un horario o lo marca como no disponible
    // En terminos tecnicos, cuando el reclutador confirma el cuadro de un turno, se ejecuta esta función
    // y crea una fila de bloqueos con el tipo elegido
    public function guardarBloqueo(Request $request)
    {
        $reclutador = $this->reclutador($request);

        $datos = $request->validate([
            'fecha' => ['required'],
            'hora_inicio' => ['nullable'],
            'hora_fin' => ['nullable'],
            'nota' => ['nullable'],
            'tipo' => ['required'],
            'dia_entero' => ['nullable'],
        ], [
            'fecha.required' => 'La fecha es obligatoria.',
            'tipo.required' => 'Elegí si vas a agendar o marcar no disponible.',
        ]);

        $diaEntero = ($datos['dia_entero'] ?? null) === '1';
        $inicio = $diaEntero ? null : $this->normalizarHora($datos['hora_inicio'] ?? null);
        $fin = $diaEntero ? null : $this->normalizarHora($datos['hora_fin'] ?? null);

        if (($inicio === null) !== ($fin === null)) {
            throw ValidationException::withMessages([
                'hora_fin' => 'Completá la hora de inicio y la de fin, o dejá las dos vacías para bloquear el día.',
            ]);
        }

        if ($inicio !== null && $this->minutos($fin) <= $this->minutos($inicio)) {
            throw ValidationException::withMessages([
                'hora_fin' => 'La hora de fin tiene que ser posterior a la de inicio.',
            ]);
        }

        $nota = isset($datos['nota']) ? trim($datos['nota']) : null;
        $nota = $nota === '' ? null : $nota;

        if ($datos['tipo'] === 'agenda' && $nota === null) {
            throw ValidationException::withMessages([
                'nota' => 'Escribí un título para lo que vas a agendar.',
            ]);
        }

        if ($inicio === null) {
            $reclutador->bloqueos()->whereDate('fecha', $datos['fecha'])->delete();
            $reclutador->bloqueos()->create([
                'fecha' => $datos['fecha'],
                'hora_inicio' => null,
                'hora_fin' => null,
                'nota' => $nota,
                'tipo' => $datos['tipo'],
            ]);
        } else {
            $hayDia = $reclutador->bloqueos()
                ->whereDate('fecha', $datos['fecha'])
                ->whereNull('hora_inicio')
                ->exists();

            if ($hayDia) {
                return back()->with('error', 'Ese día ya está marcado como no disponible.');
            }

            $reclutador->bloqueos()->create([
                'fecha' => $datos['fecha'],
                'hora_inicio' => $inicio,
                'hora_fin' => $fin,
                'nota' => $nota,
                'tipo' => $datos['tipo'],
            ]);
        }

        $mensaje = $datos['tipo'] === 'agenda'
            ? 'Agendaste ese horario.'
            : 'Marcaste el horario como no disponible.';

        return redirect()
            ->route('calendario', $this->volver($request))
            ->with('ok', $mensaje);
    }

    // Esta función quita un bloqueo del calendario
    // En terminos tecnicos, cuando el reclutador envía Quitar, se ejecuta esta función
    // y borra esa fila de bloqueos si es suya
    public function eliminarBloqueo(Request $request, Bloqueo $bloqueo)
    {
        $reclutador = $this->reclutador($request);
        abort_unless((int) $bloqueo->personal_rrhh_id === (int) $reclutador->id, 403);

        $bloqueo->delete();

        return redirect()
            ->route('calendario', $this->volver($request))
            ->with('ok', 'Quitaste el horario no disponible.');
    }

    // Esta función exige que el usuario sea el reclutador logueado
    // En terminos tecnicos, cuando guarda horario o bloqueos, se ejecuta esta función
    // y corta con 403 si no tiene perfil de personal de RRHH
    private function reclutador(Request $request): PersonalRrhh
    {
        $usuario = $request->user();
        $reclutador = $usuario->personalRrhh;

        abort_unless($reclutador && $usuario->puede('postulaciones.gestionar'), 403);

        return $reclutador;
    }

    // Esta función lee la fecha que se está mirando
    // En terminos tecnicos, cuando /calendario trae el parámetro fecha, se ejecuta esta función
    // y la interpreta en America/Argentina/Buenos_Aires
    private function fechaActual(Request $request): Carbon
    {
        $hoy = Carbon::now(self::ZONA)->startOfDay();
        $fecha = $request->query('fecha');

        if (! is_string($fecha) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $hoy;
        }

        try {
            $fechaActual = Carbon::createFromFormat('Y-m-d', $fecha, self::ZONA)->startOfDay();
        } catch (\Throwable) {
            return $hoy;
        }

        return $fechaActual->format('Y-m-d') === $fecha ? $fechaActual : $hoy;
    }

    // Esta función arma los datos de la semana
    // En terminos tecnicos, cuando se dibuja el calendario, se ejecuta esta función
    // y junta entrevistas, bloqueos y horas de esa semana
    private function armar(Carbon $fechaActual, ?PersonalRrhh $reclutador, ?int $candidatoId): array
    {
        return array_merge(
            [
                'fechaActual' => $fechaActual,
                'hoy' => Carbon::now(self::ZONA)->toDateString(),
            ],
            $this->armarSemana($fechaActual, $reclutador, $candidatoId)
        );
    }

    // Esta función arma la grilla de una semana
    // En terminos tecnicos, cuando la vista es semana, se ejecuta esta función
    // y reparte entrevistas y bloqueos en día y hora
    private function armarSemana(Carbon $fechaActual, ?PersonalRrhh $reclutador, ?int $candidatoId): array
    {
        $inicio = $fechaActual->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $fin = $inicio->copy()->addDays(7);
        $dias = [];

        for ($i = 0; $i < 7; $i++) {
            $dias[] = $inicio->copy()->addDays($i);
        }

        $entrevistas = $this->entrevistasEntre($inicio, $fin, $reclutador?->id, $candidatoId);
        $porHora = [];

        foreach ($entrevistas as $entrevista) {
            $local = $entrevista->inicioLocal();
            $porHora[$local->toDateString()][$local->hour][] = $entrevista;
        }

        $horaMin = null;
        $horaMax = null;

        foreach ($entrevistas as $entrevista) {
            $local = $entrevista->inicioLocal();
            $finEntrevista = $entrevista->finLocal();
            $horaMin = min($horaMin ?? 23, $local->hour);
            $horaMax = max($horaMax ?? 0, $finEntrevista ? $this->horaTope($finEntrevista->format('H:i')) : $local->hour + 1);
        }

        $horas = $horaMin === null ? [] : range($horaMin, max($horaMin, $horaMax - 1));
        $grilla = ['slots' => [], 'celdas' => [], 'cubiertos' => []];

        if ($reclutador) {
            $bloqueos = $reclutador->bloqueos()
                ->whereDate('fecha', '>=', $inicio->toDateString())
                ->whereDate('fecha', '<', $fin->toDateString())
                ->get();
            $disponibles = $reclutador->disponibilidades()->get()->groupBy('dia_semana');
            $grilla = $this->grilla($dias, $entrevistas, $bloqueos, $disponibles);
        }

        return [
            'inicioSemana' => $inicio,
            'dias' => $dias,
            'horas' => $horas,
            'entrevistasPorHora' => $porHora,
            'slots' => $grilla['slots'],
            'celdas' => $grilla['celdas'],
            'cubiertos' => $grilla['cubiertos'],
        ];
    }

    // Esta función arma la semana en turnos de media hora
    // En terminos tecnicos, cuando el reclutador abre el calendario, se ejecuta esta función
    // y ubica entrevistas y bloqueos en la grilla, con el alto según su duración
    private function grilla(array $dias, $entrevistas, $bloqueos, $disponibles): array
    {
        $desde = 8 * 60;
        $hasta = 20 * 60;
        $slots = [];

        for ($minuto = $desde; $minuto < $hasta; $minuto += 30) {
            $slots[] = sprintf('%02d:%02d', intdiv($minuto, 60), $minuto % 60);
        }

        $celdas = [];

        foreach ($dias as $dia) {
            $franjas = $disponibles->get($dia->dayOfWeekIso, collect());

            foreach ($slots as $slot) {
                $celdas[$dia->toDateString()][$slot] = [
                    'disponible' => $this->slotEnFranja($franjas, $this->minutos($slot)),
                ];
            }
        }

        $eventos = [];

        foreach ($bloqueos as $bloqueo) {
            if ($bloqueo->esDiaEntero()) {
                $eventos[] = [
                    'fecha' => $bloqueo->fecha->toDateString(),
                    'inicio' => $desde,
                    'fin' => $hasta,
                    'tipo' => $bloqueo->tipo === 'agenda' ? 'agenda' : 'no_disponible',
                    'bloqueo' => $bloqueo,
                ];

                continue;
            }

            $eventos[] = [
                'fecha' => $bloqueo->fecha->toDateString(),
                'inicio' => $this->minutos($bloqueo->hora_inicio),
                'fin' => $this->minutos($bloqueo->hora_fin),
                'tipo' => $bloqueo->tipo === 'agenda' ? 'agenda' : 'no_disponible',
                'bloqueo' => $bloqueo,
            ];
        }

        foreach ($entrevistas as $entrevista) {
            $inicio = $entrevista->inicioLocal();
            $fin = $entrevista->finLocal();

            if (! $inicio || ! $fin) {
                continue;
            }

            $eventos[] = [
                'fecha' => $inicio->toDateString(),
                'inicio' => ($inicio->hour * 60) + $inicio->minute,
                'fin' => ($fin->hour * 60) + $fin->minute,
                'tipo' => 'entrevista',
                'entrevista' => $entrevista,
            ];
        }

        usort($eventos, function (array $a, array $b) {
            if ($a['inicio'] !== $b['inicio']) {
                return $a['inicio'] <=> $b['inicio'];
            }

            return ($a['tipo'] === 'entrevista' ? 0 : 1) <=> ($b['tipo'] === 'entrevista' ? 0 : 1);
        });

        $cubiertos = [];

        foreach ($eventos as $evento) {
            $inicio = max($desde, $this->bajarMedia($evento['inicio']));
            $fin = min($hasta, $this->subirMedia(max($evento['fin'], $evento['inicio'] + 1)));

            if ($fin <= $inicio) {
                $fin = min($hasta, $inicio + 30);
            }

            $fecha = $evento['fecha'];
            $slot = sprintf('%02d:%02d', intdiv($inicio, 60), $inicio % 60);

            if (! isset($celdas[$fecha][$slot]) || isset($celdas[$fecha][$slot]['evento']) || isset($cubiertos[$fecha][$slot])) {
                continue;
            }

            $libreHasta = $inicio;

            for ($minuto = $inicio; $minuto < $fin; $minuto += 30) {
                $clave = sprintf('%02d:%02d', intdiv($minuto, 60), $minuto % 60);

                if ($minuto !== $inicio && (isset($celdas[$fecha][$clave]['evento']) || isset($cubiertos[$fecha][$clave]))) {
                    break;
                }

                $libreHasta = $minuto + 30;
            }

            $span = max(1, (int) (($libreHasta - $inicio) / 30));
            $celdas[$fecha][$slot]['evento'] = $evento;
            $celdas[$fecha][$slot]['span'] = $span;

            for ($paso = 1; $paso < $span; $paso++) {
                $siguiente = $inicio + ($paso * 30);
                $cubiertos[$fecha][sprintf('%02d:%02d', intdiv($siguiente, 60), $siguiente % 60)] = true;
            }
        }

        return [
            'slots' => $slots,
            'celdas' => $celdas,
            'cubiertos' => $cubiertos,
        ];
    }

    // Esta función dice si una media hora cae en el horario habitual
    // En terminos tecnicos, cuando se pinta una celda vacía, se ejecuta esta función
    // y marca las medias horas que el reclutador ofrece para entrevistas
    private function slotEnFranja($franjas, int $inicio): bool
    {
        $fin = $inicio + 30;

        foreach ($franjas as $franja) {
            if ($inicio < $this->minutos($franja->hora_fin) && $this->minutos($franja->hora_inicio) < $fin) {
                return true;
            }
        }

        return false;
    }

    // Esta función baja una hora al turno de media hora anterior
    // En terminos tecnicos, cuando un turno no empieza en punto o y media, se ejecuta esta función
    // y lo apoya en la fila de la grilla
    private function bajarMedia(int $minutos): int
    {
        return $minutos - ($minutos % 30);
    }

    // Esta función sube una hora al turno de media hora siguiente
    // En terminos tecnicos, cuando un turno no termina en punto o y media, se ejecuta esta función
    // y estira la fila hasta cubrirlo
    private function subirMedia(int $minutos): int
    {
        $resto = $minutos % 30;

        return $resto === 0 ? $minutos : $minutos + (30 - $resto);
    }

    // Esta función trae las entrevistas visibles de un período
    // En terminos tecnicos, cuando se arma la semana, se ejecuta esta función
    // y filtra por el reclutador o por el candidato, en hora de Argentina
    private function entrevistasEntre(Carbon $desde, Carbon $hasta, ?int $personalId, ?int $candidatoId)
    {
        $consulta = Entrevista::query()
            ->with(['candidato', 'personalRrhh', 'postulacion.oferta.busqueda'])
            ->whereIn('estado', [Entrevista::CONFIRMADA, Entrevista::CANCELADA])
            ->whereNotNull('inicio')
            ->where('inicio', '>=', $desde->copy()->subDay()->utc())
            ->where('inicio', '<', $hasta->copy()->addDay()->utc());

        if ($candidatoId) {
            $consulta->where('candidatos_id', $candidatoId);
        } else {
            $consulta->where('personal_rrhh_id', $personalId);
        }

        return $consulta->get()->filter(function (Entrevista $entrevista) use ($desde, $hasta) {
            $inicio = $entrevista->inicioLocal();

            return $inicio && $inicio >= $desde && $inicio < $hasta;
        })->values();
    }

    // Esta función prepara las dos franjas de cada día para el formulario
    // En terminos tecnicos, cuando se muestra el horario habitual, se ejecuta esta función
    // y deja las horas como HH:MM
    private function franjasParaFormulario(PersonalRrhh $reclutador): array
    {
        $franjas = [];

        foreach ($reclutador->disponibilidades()->orderBy('hora_inicio')->get() as $fila) {
            $franjas[$fila->dia_semana][] = [
                'inicio' => substr($fila->hora_inicio, 0, 5),
                'fin' => substr($fila->hora_fin, 0, 5),
            ];
        }

        return $franjas;
    }

    // Esta función valida y arma las filas del horario semanal
    // En terminos tecnicos, cuando el horario pasa la validación básica, se ejecuta esta función
    // y arma una franja por cada día marcado
    private function filasDeHorario(array $franjas, array $dias): array
    {
        $filas = [];

        foreach (self::DIAS as $dia => $nombre) {
            if (! in_array($dia, $dias, true)) {
                continue;
            }

            $inicio = $this->normalizarHora($franjas[$dia][0]['inicio'] ?? null);
            $fin = $this->normalizarHora($franjas[$dia][0]['fin'] ?? null);

            if ($inicio === null || $fin === null) {
                throw ValidationException::withMessages([
                    "franjas.$dia.0.fin" => 'Completá la hora de inicio y la de fin del '.$nombre.'.',
                ]);
            }

            if ($this->minutos($fin) <= $this->minutos($inicio)) {
                throw ValidationException::withMessages([
                    "franjas.$dia.0.fin" => 'En el '.$nombre.' la hora de fin tiene que ser posterior a la de inicio.',
                ]);
            }

            if ($this->minutos($fin) - $this->minutos($inicio) < self::DURACION) {
                throw ValidationException::withMessages([
                    "franjas.$dia.0.fin" => 'La franja del '.$nombre.' tiene que cubrir al menos '.self::DURACION.' minutos.',
                ]);
            }

            $filas[] = [
                'dia_semana' => $dia,
                'hora_inicio' => $inicio,
                'hora_fin' => $fin,
            ];
        }

        return $filas;
    }

    // Esta función deja una hora como HH:MM:SS
    // En terminos tecnicos, cuando llega un input time, se ejecuta esta función
    // y descarta los segundos si vinieron de más
    private function normalizarHora(?string $hora): ?string
    {
        if ($hora === null || $hora === '') {
            return null;
        }

        return substr($hora, 0, 5).':00';
    }

    // Esta función pasa una hora a minutos desde la medianoche
    // En terminos tecnicos, cuando se comparan dos horas, se ejecuta esta función
    // y devuelve la cantidad de minutos
    private function minutos(string $hora): int
    {
        [$horas, $minutos] = array_map('intval', explode(':', substr($hora, 0, 5)));

        return ($horas * 60) + $minutos;
    }

    // Esta función dice hasta qué hora hay que dibujar la grilla
    // En terminos tecnicos, cuando una franja no termina en punto, se ejecuta esta función
    // y sube una hora para que el último tramo tenga fila
    private function horaTope(string $hora): int
    {
        [$horas, $minutos] = array_map('intval', explode(':', substr($hora, 0, 5)));

        return $minutos > 0 ? $horas + 1 : $horas;
    }

    // Esta función arma la vuelta al mismo lugar del calendario
    // En terminos tecnicos, cuando un formulario del calendario se guarda, se ejecuta esta función
    // y conserva la vista y la fecha que se estaban mirando
    private function volver(Request $request): array
    {
        return [
            'fecha' => $request->input('fecha_actual'),
        ];
    }

    // Esta función lista los turnos libres de los próximos días
    // En terminos tecnicos, cuando el candidato abre elegir horario, se ejecuta esta función
    // y corta las franjas del reclutador en bloques que todavía no empezaron
    public function listar(PersonalRrhh $reclutador, int $dias = 14): array
    {
        $ahora = Carbon::now(self::ZONA);
        $desde = $ahora->copy()->startOfDay();
        $hasta = $desde->copy()->addDays($dias);
        $duracion = self::DURACION;

        $disponibilidades = $reclutador->disponibilidades()->get()->groupBy('dia_semana');
        $bloqueos = $reclutador->bloqueos()
            ->whereDate('fecha', '>=', $desde->toDateString())
            ->whereDate('fecha', '<', $hasta->toDateString())
            ->get()
            ->groupBy(fn ($bloqueo) => $bloqueo->fecha->toDateString());

        $entrevistas = Entrevista::query()
            ->where('personal_rrhh_id', $reclutador->id)
            ->where('estado', Entrevista::CONFIRMADA)
            ->whereNotNull('inicio')
            ->where('inicio', '>=', $desde->copy()->subDay()->utc())
            ->where('inicio', '<', $hasta->copy()->addDay()->utc())
            ->get();

        $huecos = [];

        for ($dia = $desde->copy(); $dia < $hasta; $dia->addDay()) {
            $franjas = $this->franjasDelDia($dia, $disponibilidades, $bloqueos, $entrevistas);

            foreach ($this->cortar($franjas, $duracion, $ahora) as $hueco) {
                $huecos[] = $hueco;
            }
        }

        return $huecos;
    }

    // Esta función dice si un horario sigue libre
    // En terminos tecnicos, cuando el candidato confirma un turno, se ejecuta esta función
    // y vuelve a armar los huecos para ver si ese inicio sigue adentro
    public function incluye(PersonalRrhh $reclutador, Carbon $inicio): bool
    {
        $clave = $inicio->copy()->timezone(self::ZONA)->format('Y-m-d H:i');

        foreach ($this->listar($reclutador) as $hueco) {
            if ($hueco->format('Y-m-d H:i') === $clave) {
                return true;
            }
        }

        return false;
    }

    // Esta función deja las franjas de un día después de sacar bloqueos y entrevistas
    // En terminos tecnicos, cuando se recorre cada día de los 14, se ejecuta esta función
    // y devuelve pares de inicio y fin todavía libres
    private function franjasDelDia(Carbon $dia, Collection $disponibilidades, Collection $bloqueos, Collection $entrevistas): array
    {
        $intervalos = [];

        foreach ($disponibilidades->get($dia->dayOfWeekIso, collect()) as $fila) {
            $inicio = $this->combinar($dia, $fila->hora_inicio);
            $fin = $this->combinar($dia, $fila->hora_fin);

            if ($fin > $inicio) {
                $intervalos[] = [$inicio, $fin];
            }
        }

        foreach ($bloqueos->get($dia->toDateString(), collect()) as $bloqueo) {
            if ($bloqueo->esDiaEntero()) {
                return [];
            }

            $intervalos = $this->restar(
                $intervalos,
                $this->combinar($dia, $bloqueo->hora_inicio),
                $this->combinar($dia, $bloqueo->hora_fin)
            );
        }

        foreach ($entrevistas as $entrevista) {
            $inicio = $entrevista->inicioLocal();
            $fin = $entrevista->finLocal();

            if ($inicio && $fin) {
                $intervalos = $this->restar($intervalos, $inicio, $fin);
            }
        }

        return $intervalos;
    }

    // Esta función parte una franja en turnos de la duración elegida
    // En terminos tecnicos, cuando ya quedó el tramo libre de un día, se ejecuta esta función
    // y se queda con los bloques enteros que empiezan después de ahora
    private function cortar(array $intervalos, int $duracion, Carbon $ahora): array
    {
        $huecos = [];

        foreach ($intervalos as [$inicio, $fin]) {
            $cursor = $inicio->copy();

            while ($cursor->copy()->addMinutes($duracion)->lessThanOrEqualTo($fin)) {
                if ($cursor->greaterThan($ahora)) {
                    $huecos[] = $cursor->copy();
                }

                $cursor->addMinutes($duracion);
            }
        }

        return $huecos;
    }

    // Esta función saca un tramo ocupado de las franjas libres
    // En terminos tecnicos, cuando un bloqueo o una entrevista pisa el horario, se ejecuta esta función
    // y parte o descarta los intervalos que se superponen
    private function restar(array $intervalos, Carbon $corteInicio, Carbon $corteFin): array
    {
        $resultado = [];

        foreach ($intervalos as [$inicio, $fin]) {
            if ($corteFin <= $inicio || $corteInicio >= $fin) {
                $resultado[] = [$inicio, $fin];

                continue;
            }

            if ($corteInicio > $inicio) {
                $resultado[] = [$inicio, $corteInicio->copy()];
            }

            if ($corteFin < $fin) {
                $resultado[] = [$corteFin->copy(), $fin];
            }
        }

        return $resultado;
    }

    // Esta función junta un día con una hora
    // En terminos tecnicos, cuando se compara una franja con un bloqueo, se ejecuta esta función
    // y arma un Carbon en America/Argentina/Buenos_Aires
    private function combinar(Carbon $dia, string $hora): Carbon
    {
        return Carbon::createFromFormat(
            'Y-m-d H:i',
            $dia->format('Y-m-d').' '.substr($hora, 0, 5),
            self::ZONA
        )->startOfMinute();
    }
}
