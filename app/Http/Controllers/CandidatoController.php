<?php

namespace App\Http\Controllers;

use App\Models\Candidato;

class CandidatoController extends Controller
{
    // Esta función muestra el listado de candidatos
    // En terminos tecnicos, cuando el admin abre /candidatos, se ejecuta esta función
    // y trae cada candidato con su usuario, ciudad y cantidad de postulaciones
    public function index()
    {
        $candidatos = Candidato::query()
            ->with(['usuario', 'ciudad.provincia'])
            ->withCount('postulaciones')
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(10)
            // withQueryString() es una función que permite mantener los parámetros de la URL al paginar
            // por ejemplo, si se está en la página 2 de la lista de candidatos, al paginar se mantendrá la página 2 en la URL
            // o si se está filtrando por ciudad, al paginar se mantendrá el filtro de ciudad en la URL
            ->withQueryString();

        return view('candidatos.index', compact('candidatos'));
    }

    // Esta función muestra el detalle de un candidato
    // En terminos tecnicos, cuando el admin abre /candidatos/{id}, se ejecuta esta función
    // y trae sus datos y las postulaciones, de la más reciente a la más vieja
    public function show(Candidato $candidato)
    {
        $candidato->load([
            'usuario',
            'ciudad.provincia',
            'habilidades',
            'experiencias',
            'postulaciones' => function ($postulaciones) {
                $postulaciones
                    ->with([
                        'etapa',
                        'oferta.estado',
                        'oferta.busqueda.empresa',
                    ])
                    ->orderByDesc('postulaciones.id');
            },
        ]);

        return view('candidatos.show', compact('candidato'));
    }
}
