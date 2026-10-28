<?php

namespace App\Models;

use App\Http\Controllers\CalendarioController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class EntrevistaEmpresa extends Model
{
    protected $table = 'entrevistas_empresa';

    protected $fillable = [
        'envios_id',
        'inicio',
        'nota',
        'enlace',
    ];

    protected $casts = [
        'inicio' => 'datetime',
    ];

    // Esta función trae el envío de la entrevista
    // En terminos tecnicos, cuando la empresa propone un horario, se ejecuta esta función
    // y busca la fila de envios con el envios_id
    public function envio(): BelongsTo
    {
        return $this->belongsTo(Envio::class, 'envios_id');
    }

    // Esta función pasa el inicio a la hora de Argentina
    // En terminos tecnicos, cuando la ficha muestra el horario propuesto, se ejecuta esta función
    // y convierte el datetime guardado a America/Argentina/Buenos_Aires
    public function inicioLocal(): ?Carbon
    {
        return $this->inicio?->copy()->timezone(CalendarioController::ZONA);
    }

    // Esta función dice si la entrevista ya empezó
    // En terminos tecnicos, cuando el calendario decide si muestra Confirmar, se ejecuta esta función
    // y compara el inicio local con la hora actual de Argentina
    public function yaEmpezo(): bool
    {
        $inicio = $this->inicioLocal();

        return $inicio !== null && $inicio->lte(Carbon::now(CalendarioController::ZONA));
    }

    // Esta función arma el título que ve el candidato
    // En terminos tecnicos, cuando se pinta el turno en su calendario, se ejecuta esta función
    // y junta la empresa con el puesto de la oferta
    public function tituloCandidato(): string
    {
        $empresa = $this->envio?->postulacion?->oferta?->busqueda?->empresa?->nombre ?? '';
        $puesto = $this->envio?->postulacion?->oferta?->busqueda?->nombre_puesto ?? '';

        return 'entrevista con '.$empresa.'-'.$puesto;
    }

    // Esta función arma el título que ve la empresa
    // En terminos tecnicos, cuando se pinta el turno en su calendario, se ejecuta esta función
    // y junta el nombre del candidato con el puesto de la oferta
    public function tituloEmpresa(): string
    {
        $candidato = $this->envio?->postulacion?->candidatos?->first();
        $nombre = trim(($candidato?->nombre ?? '').' '.($candidato?->apellido ?? ''));
        $puesto = $this->envio?->postulacion?->oferta?->busqueda?->nombre_puesto ?? '';

        return 'entrevista con '.$nombre.'-'.$puesto;
    }
}
