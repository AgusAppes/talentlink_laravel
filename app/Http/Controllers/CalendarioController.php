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
                $this->armarSemana($fechaActual, null, $candidato->id),
                [
                    'fechaActual' => $fechaActual,
                    'hoy' => Carbon::now(self::ZONA)->toDateString(),
                    'editar' => false,
                ]
            ));
        }

        $reclutador = $usuario->personalRrhh;
        abort_unless($reclutador && $usuario->puede('postulaciones.gestionar'), 403);

        return view('calendario.index', array_merge(
            $this->armarSemana($fechaActual, $reclutador, null),
            [
                'fechaActual' => $fechaActual,
                'hoy' => Carbon::now(self::ZONA)->toDateString(),
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

    // Esta función guarda el horario semanal (disponible/habitual) del reclutador
    // En terminos tecnicos, cuando el reclutador envía el formulario de /mi-perfil/horario,
    // se ejecuta esta función, guarda las filas en disponibilidades y redirige al mismo perfil
    public function guardarHorario(Request $request)
    {
        $reclutador = $this->reclutador($request);

        // array_map aplica una función a cada elemento de un array
        // En este caso, la función es intval, que convierte el valor a un entero
        // Y se usa para convertir a enteros los valores que vienen del formulario como texto
        $dias = array_map('intval', $request->input('dias', []));
        // franjas se refiere al intervalo de disponibilidad de cada dia
        // Es decir hora de inicio y hora de fin
        // filasDeHorario arma las filas de disponibilidad para cada dia, devuelve un array con dia_semana, hora_inicio y hora_fin
        $filas = $this->filasDeHorario($request->input('franjas', []), $dias);

        DB::transaction(function () use ($reclutador, $filas) {
            // delete() borra todas las filas de la tabla disponibilidades para el reclutador
            // Esto se hace para simplificar en caso de que el reclutador ya tenia disponibilidad cargada
            // Se borra todo lo anterior y se reemplaza por la nueva disponibilidad
            $reclutador->disponibilidades()->delete();
            // createMany() crea multiples filas en la tabla disponibilidades
            // recibe un array de arrays, cada array es una fila de la tabla
            // cada fila tiene los campos dia_semana, hora_inicio y hora_fin
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

        $diaEntero = $request->input('dia_entero') === '1';
        $inicio = $diaEntero ? null : $request->input('hora_inicio');
        $fin = $diaEntero ? null : $request->input('hora_fin');
        $nota = trim((string) $request->input('nota'));
        // Si la nota está vacía, se le asigna el valor 'No disponible'
        $nota = $nota === '' ? 'No disponible' : $nota;
        $tipo = $request->input('tipo');

        // Si el inicio es null, significa que el reclutador marcó el dia entero como no disponible
        // Se borra todo lo anterior y se crea una fila de bloqueos con el tipo elegido
        if ($inicio === null) {
            $reclutador->bloqueos()->whereDate('fecha', $request->input('fecha'))->delete();
            $reclutador->bloqueos()->create([
                'fecha' => $request->input('fecha'),
                'hora_inicio' => null,
                'hora_fin' => null,
                'nota' => $nota,
                'tipo' => $tipo,
            ]);
        } else {
            $reclutador->bloqueos()->create([
                'fecha' => $request->input('fecha'),
                'hora_inicio' => $inicio,
                'hora_fin' => $fin,
                'nota' => $nota,
                'tipo' => $tipo,
            ]);
        }

        $mensaje = $tipo === 'agenda'
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

    // Esta función arma la grilla de una semana
    // En terminos tecnicos, cuando se dibuja el calendario, se ejecuta esta función
    // y reparte entrevistas y bloqueos en día y media hora
    private function armarSemana(Carbon $fechaActual, ?PersonalRrhh $reclutador, ?int $candidatoId): array
    {
        $inicio = $fechaActual->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $fin = $inicio->copy()->addDays(7);
        $dias = [];

        // arma un array con los 7 dias de la semana y los agrega a la variable $dias
        for ($i = 0; $i < 7; $i++) {
            $dias[] = $inicio->copy()->addDays($i);
        }

        $entrevistas = $this->entrevistasEntre($inicio, $fin, $reclutador?->id, $candidatoId);
        // slots son las filas de media hora del calendario
        // celdas son los espacios (fila, columna) de la grilla
        // cubiertos son las celdas que ya están cubiertas por una entrevista o un bloqueo
        $grilla = ['slots' => [], 'celdas' => [], 'cubiertos' => []];
        $entrevistasPorSlot = [];

        // arma los bloqueos y las disponibilidades del reclutador
        if ($reclutador) {
            $bloqueos = $reclutador->bloqueos()
                ->whereDate('fecha', '>=', $inicio->toDateString())
                ->whereDate('fecha', '<', $fin->toDateString())
                ->get();
            $disponibles = $reclutador->disponibilidades()->get()->groupBy('dia_semana');
            $grilla = $this->grilla($dias, $entrevistas, $bloqueos, $disponibles);
        } else {
            [$grilla['slots'], $entrevistasPorSlot] = $this->slotsCandidato($entrevistas);
        }

        return [
            'inicioSemana' => $inicio,
            'dias' => $dias,
            'slots' => $grilla['slots'],
            'entrevistasPorSlot' => $entrevistasPorSlot,
            'celdas' => $grilla['celdas'],
            'cubiertos' => $grilla['cubiertos'],
        ];
    }

    // Esta función arma las filas de media hora del calendario del candidato
    // En terminos tecnicos, cuando el candidato abre /calendario, se ejecuta esta función
    // y ubica cada entrevista en el horario en que empieza
    private function slotsCandidato($entrevistas): array
    {
        $porSlot = [];
        $minutoMin = null;
        $minutoMax = null;

        foreach ($entrevistas as $entrevista) {
            $local = $entrevista->inicioLocal();

            if (! $local) {
                continue;
            }

            $minuto = ($local->hour * 60) + $local->minute;
            $slot = sprintf('%02d:%02d', intdiv($minuto, 60), $minuto % 60);
            $porSlot[$local->toDateString()][$slot][] = $entrevista;
            $minutoMin = min($minutoMin ?? $minuto, $minuto);
            $minutoMax = max($minutoMax ?? $minuto, $minuto);
        }

        $slots = [];

        if ($minutoMin !== null) {
            for ($minuto = $minutoMin; $minuto <= $minutoMax; $minuto += self::DURACION) {
                $slots[] = sprintf('%02d:%02d', intdiv($minuto, 60), $minuto % 60);
            }
        }

        return [$slots, $porSlot];
    }

    // Esta función arma la semana en turnos de media hora
    // En terminos tecnicos, cuando el reclutador abre el calendario, se ejecuta esta función
    // y ubica entrevistas y bloqueos en la grilla, con el alto según su duración
    // Una collection es un objeto de laravel que contiene una colección de elementos
    // Se diferencia de un array porque tiene métodos para manipular la colección, como filter, map, etc.
    private function grilla(array $dias, Collection $entrevistas, Collection $bloqueos, Collection $disponibles): array
    {
        $desde = 6 * 60;
        $hasta = 22 * 60;
        $slots = [];

        // arma un array con las filas de media hora del calendario
        for ($minuto = $desde; $minuto < $hasta; $minuto += 30) {
            // intdiv($minuto, 60): divide los minutos por 60 y devuelve la parte entera. Obtiene la hora
            // $minuto % 60: obtiene el resto de la división de los minutos por 60. Obtiene los minutos
            // sprintf('%02d:%02d', intdiv($minuto, 60), $minuto % 60): formatea la hora y los minutos en el formato HH:MM
            // %02d: indica que el número debe tener 2 dígitos. Si el número tiene menos de 2 dígitos, se agrega un 0 al inicio
            $slots[] = sprintf('%02d:%02d', intdiv($minuto, 60), $minuto % 60);
        }

        $celdas = [];

        // recorre los dias de la semana
        foreach ($dias as $dia) {
            // get(): obtiene la franja de disponibilidad para el dia
            $franjas = $disponibles->get($dia->dayOfWeekIso, collect());

            // recorre las filas de media hora del calendario para ese dia
            foreach ($slots as $slot) {
                // slotEnFranja verifica si la fila de media hora está en la franja de disponibilidad
                $celdas[$dia->toDateString()][$slot] = [
                    'disponible' => $this->slotEnFranja($franjas, $this->minutos($slot)),
                ];
            }
        } 

        // arma un array con las entrevistas y los bloqueos
        $eventos = [];

        // recorre las entrevistas y los bloqueos
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
            $inicio = max($desde, $evento['inicio']);
            $fin = min($hasta, $evento['fin']);

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

    // Esta función dice si una celda de media hora cae en el horario habitual
    // Se usa para marcar las celdas disponibles para entrevistas
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

    // Esta función prepara la franja de cada día para el formulario
    // En terminos tecnicos, cuando se muestra el horario habitual, se ejecuta esta función
    // y deja las horas como HH:MM
    private function franjasParaFormulario(PersonalRrhh $reclutador): array
    {
        $franjas = [];

        foreach ($reclutador->disponibilidades()->orderBy('hora_inicio')->get() as $fila) {
            $franjas[$fila->dia_semana] = [
                'inicio' => substr($fila->hora_inicio, 0, 5),
                'fin' => substr($fila->hora_fin, 0, 5),
            ];
        }

        return $franjas;
    }

    // Esta función devuelve una array con dia_semana, hora_inicio y hora_fin, es decir, arma la fila que se va a guardar en la base de datos
    // En terminos tecnicos, cuando el horario pasa la validación básica, se ejecuta esta función
    // y arma una franja por cada día marcado
    // :array significa que la función devuelve un array
    private function filasDeHorario(array $franjas, array $dias): array
    {
        $filas = [];

        // self::DIAS es un array que contiene los dias de la semana
        // Este foreach se ejecuta para cada dia de la semana
        foreach (self::DIAS as $dia => $nombre) {

            // in_array verifica si el dia esta en el array $dias
            // Si el dia que se está recorriendo no esta en el array $dias que recibe por parametro
            // se omite el resto del codigo y continua al siguiente dia, es decir, vuelve al inicio del foreach
            if (! in_array($dia, $dias, true)) {
                continue;
            }

            $inicio = $franjas[$dia]['inicio'];
            $fin = $franjas[$dia]['fin'];

            if ($this->minutos($fin) <= $this->minutos($inicio)) {
                throw ValidationException::withMessages([
                    "franjas.$dia.fin" => 'En el '.$nombre.' la hora de fin tiene que ser posterior a la de inicio.',
                ]);
            }

            if ($this->minutos($fin) - $this->minutos($inicio) < self::DURACION) {
                throw ValidationException::withMessages([
                    "franjas.$dia.fin" => 'La franja del '.$nombre.' tiene que cubrir al menos '.self::DURACION.' minutos.',
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

    // Esta función pasa una hora a minutos desde la medianoche, se usa para comparar horas y decidir si una hora es anterior o posterior a otra
    // En terminos tecnicos, cuando se comparan dos horas, se ejecuta esta función
    // y devuelve la cantidad de minutos
    // Las horas se comparan en minutos porque es más fácil de manejar y comparar
    private function minutos(string $hora): int
    {
        return ((int) substr($hora, 0, 2) * 60) + (int) substr($hora, 3, 2);
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
