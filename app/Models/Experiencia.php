<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Experiencia extends Model
{
    protected $table = 'experiencias';

    public $timestamps = false;

    protected $fillable = [
        'empresa',
        'puesto',
        'fecha_desde',
        'fecha_hasta',
        'descripcion',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
    ];

    // Esta función trae el candidato de la experiencia
    // En terminos tecnicos, cuando se guarda o se borra una experiencia, se ejecuta esta función
    // y busca la fila de candidatos con el candidatos_id
    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class, 'candidatos_id');
    }

    // Esta función arma el texto del período
    // En terminos tecnicos, cuando el perfil o el detalle muestran una experiencia, se ejecuta esta función
    // y devuelve las fechas en formato mes/año, o vacío si no hay fechas
    public function periodo(): string
    {
        $desde = $this->fecha_desde?->format('m/Y');
        $hasta = $this->fecha_hasta?->format('m/Y');

        if ($desde && $hasta) {
            return $desde.' – '.$hasta;
        }

        if ($desde) {
            return $desde.' – Actualidad';
        }

        if ($hasta) {
            return 'Hasta '.$hasta;
        }

        return '';
    }
}
