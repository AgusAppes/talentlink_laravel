<?php

namespace App\Http\Controllers;

use App\Models\Busqueda;
use App\Models\Candidato;
use App\Models\Empresa;
use App\Models\Oferta;
use App\Models\Postulacion;

class DashboardController extends Controller
{
    // Esta función muestra el dashboard del admin
    // En terminos tecnicos, cuando se visita /dashboard, se ejecuta esta función
    // y sirve dashboard/index.blade.php con las cinco métricas
    public function index()
    {
        return view('dashboard.index', [
            'metricas' => $this->calcularMetricas(),
        ]);
    }

    // Esta función devuelve las métricas en JSON
    // En terminos tecnicos, cuando el dashboard pide /dashboard/metricas, se ejecuta esta función
    // y responde con los cinco conteos para actualizar las tarjetas
    public function metricas()
    {
        return response()->json($this->calcularMetricas());
    }

    // Esta función cuenta las cinco métricas del dashboard
    // En terminos tecnicos, cuando se abre el dashboard o se piden las métricas, se ejecuta esta función
    // y devuelve los conteos de solicitudes, ofertas, postulaciones, candidatos y empresas
    private function calcularMetricas(): array
    {
        return [
            'solicitudes_pendientes' => Busqueda::query()->where('estado_busqueda_id', 1)->count(),
            'ofertas_publicadas' => Oferta::query()->where('estado_ofertas_id', 1)->count(),
            'postulaciones_pendientes' => Postulacion::query()->where('etapas_id', 1)->count(),
            'candidatos' => Candidato::query()->count(),
            'empresas' => Empresa::query()->count(),
        ];
    }
}
