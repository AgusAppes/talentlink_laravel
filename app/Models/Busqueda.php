<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Busqueda extends Model
{
    protected $table = 'busquedas';

    protected $fillable = [
        'nombre_puesto',
        'empresas_id',
        'estado_busqueda_id',
    ];

    // Esta función trae la empresa que cargó la solicitud
    // En terminos tecnicos, cuando el listado muestra la columna Empresa, se ejecuta esta función
    // y busca la fila de empresas con el empresas_id de esta búsqueda
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresas_id');
    }

    // Esta función trae el estado de la solicitud
    // En terminos tecnicos, cuando el listado muestra el badge, se ejecuta esta función
    // y busca la fila de estado_busqueda con el estado_busqueda_id
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoBusqueda::class, 'estado_busqueda_id');
    }

    // Esta función adjunta la ficha de Mongo y la modalidad y ciudad de MySQL
    // En terminos tecnicos, cuando un listado ya tiene las búsquedas de la página, se ejecuta esta función
    // y trae los documentos solicitudes de esos ids en una sola lectura
    public static function hidratarFichas(iterable $busquedas): void
    {
        if ($busquedas instanceof \Illuminate\Pagination\AbstractPaginator) {
            $busquedas = $busquedas->getCollection();
        }

        $lista = collect($busquedas)->filter(fn ($busqueda) => $busqueda instanceof self);

        if ($lista->isEmpty()) {
            return;
        }

        $ids = $lista->pluck('id')->map(fn ($id) => (int) $id)->unique()->values();
        $fichas = SolicitudDocumento::query()
            ->whereIn('busqueda_id', $ids->all())
            ->get()
            ->keyBy(fn (SolicitudDocumento $ficha) => (int) $ficha->busqueda_id);

        $modalidadIds = $fichas->pluck('modalidades_id')->filter()->unique()->values();
        $ciudadIds = $fichas->pluck('ciudades_id')->filter()->unique()->values();

        $modalidades = $modalidadIds->isEmpty()
            ? collect()
            : Modalidad::query()->whereIn('id', $modalidadIds->all())->get()->keyBy('id');

        $ciudades = $ciudadIds->isEmpty()
            ? collect()
            : Ciudad::query()->with('provincia')->whereIn('id', $ciudadIds->all())->get()->keyBy('id');

        foreach ($lista as $busqueda) {
            $ficha = $fichas->get((int) $busqueda->id);

            if ($ficha) {
                $ficha->setRelation('modalidad', $modalidades->get($ficha->modalidades_id));
                $ficha->setRelation('ciudad', $ciudades->get($ficha->ciudades_id));
            }

            $busqueda->setRelation('ficha', $ficha);
        }
    }

    public function oferta(): HasOne
    {
        return $this->hasOne(Oferta::class, 'busquedas_id');
    }
}
