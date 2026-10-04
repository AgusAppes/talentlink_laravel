<?php

namespace App\Http\Controllers;

use App\Models\Entrevista;
use App\Models\Etapa;
use App\Models\Postulacion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EntrevistaController extends Controller
{
    // Esta función pide una entrevista para una postulación
    // En terminos tecnicos, cuando el reclutador envía Solicitar entrevista, se ejecuta esta función
    // y crea la fila solicitada y pasa la etapa a En entrevista
    public function store(Request $request, Postulacion $postulacion)
    {
        $reclutador = $request->user()->personalRrhh;

        if ($reclutador->disponibilidades()->doesntExist()) {
            return back()->with('error', 'Primero cargá tus horarios en el calendario.');
        }

        $candidato = $postulacion->candidatos()->first();

        $creada = DB::transaction(function () use ($postulacion, $reclutador, $candidato) {
            $postulacion = Postulacion::query()->whereKey($postulacion->id)->lockForUpdate()->first();

            $activa = $postulacion->entrevistas()
                ->whereIn('estado', [Entrevista::SOLICITADA, Entrevista::CONFIRMADA])
                ->exists();

            if ($activa) {
                return false;
            }

            $postulacion->entrevistas()->create([
                'personal_rrhh_id' => $reclutador->id,
                'candidatos_id' => $candidato->id,
                'estado' => Entrevista::SOLICITADA,
            ]);

            $postulacion->update(['etapas_id' => Etapa::EN_ENTREVISTA]);

            return true;
        });

        if (! $creada) {
            return back()->with('error', 'Esta postulación ya tiene una entrevista en curso.');
        }

        return back()->with('ok', 'Enviaste la solicitud de entrevista.');
    }

    // Esta función muestra los horarios que el candidato puede elegir
    // En terminos tecnicos, cuando abre la solicitud pendiente, se ejecuta esta función
    // y arma la semana con los huecos libres de los próximos 14 días
    public function elegir(Request $request, Postulacion $postulacion, CalendarioController $huecos)
    {
        $entrevista = $postulacion->entrevistas()
            ->where('estado', Entrevista::SOLICITADA)
            ->first();

        if (! $entrevista) {
            return redirect()
                ->route('postulaciones.index')
                ->with('error', 'No hay una solicitud de entrevista pendiente.');
        }

        $reclutador = $entrevista->personalRrhh;
        $lista = $huecos->listar($reclutador);
        $libres = [];

        foreach ($lista as $hueco) {
            $libres[$hueco->format('Y-m-d H:i')] = true;
        }

        $hoy = Carbon::now(CalendarioController::ZONA)->startOfDay();
        $ultima = $hoy->copy()->addDays(13);
        $inicioSemana = $this->semanaElegir($request, $hoy, $ultima);
        $dias = [];

        for ($i = 0; $i < 7; $i++) {
            $dias[] = $inicioSemana->copy()->addDays($i);
        }

        $slots = [];

        for ($minuto = 6 * 60; $minuto < 22 * 60; $minuto += 30) {
            $slots[] = sprintf('%02d:%02d', intdiv($minuto, 60), $minuto % 60);
        }

        $primera = $hoy->copy()->startOfWeek(Carbon::MONDAY);
        $tope = $ultima->copy()->startOfWeek(Carbon::MONDAY);
        $postulacion->load('oferta.busqueda');

        return view('entrevistas.elegir', [
            'postulacion' => $postulacion,
            'dias' => $dias,
            'slots' => $slots,
            'libres' => $libres,
            'inicioSemana' => $inicioSemana,
            'puedeAnterior' => $inicioSemana->gt($primera),
            'puedeSiguiente' => $inicioSemana->lt($tope),
            'sinHuecos' => $lista === [],
        ]);
    }

    // Esta función elige la semana que se muestra para elegir horario
    // En terminos tecnicos, cuando la ruta trae el parámetro fecha, se ejecuta esta función
    // y la deja dentro de las semanas de los próximos 14 días
    private function semanaElegir(Request $request, Carbon $hoy, Carbon $ultima): Carbon
    {
        $primera = $hoy->copy()->startOfWeek(Carbon::MONDAY);
        $tope = $ultima->copy()->startOfWeek(Carbon::MONDAY);
        $fecha = $request->query('fecha');

        if (! is_string($fecha) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $primera;
        }

        try {
            $fechaActual = Carbon::createFromFormat('Y-m-d', $fecha, CalendarioController::ZONA)->startOfDay();
        } catch (\Throwable) {
            return $primera;
        }

        if ($fechaActual->format('Y-m-d') !== $fecha) {
            return $primera;
        }

        $inicio = $fechaActual->copy()->startOfWeek(Carbon::MONDAY);

        if ($inicio->lt($primera)) {
            return $primera;
        }

        if ($inicio->gt($tope)) {
            return $tope;
        }

        return $inicio;
    }

    // Esta función confirma el turno que eligió el candidato
    // En terminos tecnicos, cuando envía un horario de la lista, se ejecuta esta función
    // y vuelve a comprobar que siga libre antes de guardarlo
    public function confirmar(Request $request, Postulacion $postulacion, CalendarioController $huecos)
    {
        $candidato = $request->user()->candidato;
        abort_unless($candidato, 403);
        abort_unless(
            $postulacion->candidatos()->where('candidatos.id', $candidato->id)->exists(),
            403
        );

        $datos = $request->validate([
            'inicio' => ['required', 'date_format:Y-m-d H:i'],
        ], [
            'inicio.required' => 'Elegí un horario.',
            'inicio.date_format' => 'El horario elegido no es válido.',
        ]);

        $inicio = Carbon::createFromFormat('Y-m-d H:i', $datos['inicio'], CalendarioController::ZONA)->startOfMinute();

        $resultado = DB::transaction(function () use ($postulacion, $candidato, $huecos, $inicio) {
            $entrevista = $postulacion->entrevistas()
                ->where('estado', Entrevista::SOLICITADA)
                ->where('candidatos_id', $candidato->id)
                ->lockForUpdate()
                ->first();

            if (! $entrevista) {
                return 'estado';
            }

            Entrevista::query()
                ->where('personal_rrhh_id', $entrevista->personal_rrhh_id)
                ->where('estado', Entrevista::CONFIRMADA)
                ->lockForUpdate()
                ->get();

            $reclutador = $entrevista->personalRrhh()->first();

            if (! $reclutador || ! $huecos->incluye($reclutador, $inicio)) {
                return 'libre';
            }

            $entrevista->update([
                'estado' => Entrevista::CONFIRMADA,
                'inicio' => $inicio->copy()->utc(),
                'duracion_minutos' => CalendarioController::DURACION,
            ]);

            return 'ok';
        });

        if ($resultado === 'estado') {
            return redirect()
                ->route('postulaciones.index')
                ->with('error', 'Esta solicitud ya no está pendiente.');
        }

        if ($resultado === 'libre') {
            return back()->with('error', 'Ese horario ya no está disponible. Elegí otro.');
        }

        return redirect()
            ->route('calendario')
            ->with('ok', 'Quedó agendada la entrevista.');
    }

    // Esta función cancela una solicitud o una entrevista confirmada
    // En terminos tecnicos, cuando el reclutador o el candidato envían el motivo, se ejecuta esta función
    // y deja la entrevista cancelada y la etapa en Entrevista cancelada
    public function cancelar(Request $request, Entrevista $entrevista)
    {
        $usuario = $request->user();
        $esCandidato = $usuario->esCandidato()
            && (int) $entrevista->candidatos_id === (int) $usuario->candidato?->id;
        $esReclutador = (int) $entrevista->personal_rrhh_id === (int) $usuario->personalRrhh?->id;

        abort_unless($esCandidato || $esReclutador, 403);

        $datos = $request->validate([
            'motivo' => ['required', 'string', 'max:500'],
        ], [
            'motivo.required' => 'El motivo de la cancelación es obligatorio.',
            'motivo.max' => 'El motivo no puede superar los 500 caracteres.',
        ]);

        $cancelada = DB::transaction(function () use ($entrevista, $esCandidato, $datos) {
            $entrevista = Entrevista::query()->whereKey($entrevista->id)->lockForUpdate()->first();

            if (! $entrevista || ! $entrevista->estaActiva()) {
                return false;
            }

            $entrevista->update([
                'estado' => Entrevista::CANCELADA,
                'cancelada_por' => $esCandidato ? 'candidato' : 'reclutador',
                'motivo' => trim($datos['motivo']),
            ]);

            $entrevista->postulacion()->update(['etapas_id' => Etapa::ENTREVISTA_CANCELADA]);

            return true;
        });

        if (! $cancelada) {
            return back()->with('error', 'Esta entrevista ya estaba cancelada.');
        }

        return back()->with('ok', 'Cancelaste la entrevista.');
    }
}
