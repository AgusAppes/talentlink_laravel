<?php

namespace App\Experto;

class BaseConocimiento
{
    public const PESO_PERFIL = 3;

    public const PESO_EXPERIENCIA = 2;

    public const PESO_CV = 1;

    public const PESO_HABILIDADES = 80;

    public const PESO_ANIOS = 15;

    public const PESO_UBICACION = 5;

    // Variante ya normalizada => habilidad canónica.
    private const SINONIMOS = [
        'js' => 'javascript',
        'java script' => 'javascript',
        'react js' => 'react',
        'reactjs' => 'react',
        'vue js' => 'vue',
        'vuejs' => 'vue',
        'angular js' => 'angular',
        'angularjs' => 'angular',
        'node js' => 'node',
        'nodejs' => 'node',
        'postgres' => 'postgresql',
        'my sql' => 'mysql',
        'html5' => 'html',
        'css3' => 'css',
        'microsoft excel' => 'excel',
        'ms excel' => 'excel',
        'cicd' => 'ci cd',
    ];

    // Habilidad declarada => habilidades que cubre solo en parte.
    private const IMPLICA = [
        'laravel' => ['php'],
        'symfony' => ['php'],
        'codeigniter' => ['php'],
        'react' => ['javascript'],
        'vue' => ['javascript'],
        'angular' => ['javascript'],
        'node' => ['javascript'],
        'mysql' => ['sql'],
        'mariadb' => ['sql'],
        'postgresql' => ['sql'],
    ];

    // Esta función deja una habilidad en su forma comparable
    // En terminos tecnicos, cuando el motor compara perfil, experiencia o CV, se ejecuta esta función
    // y devuelve el texto en minúsculas, sin acentos, o su sinónimo canónico
    public function canonizar(string $habilidad): string
    {
        $texto = $this->normalizar($habilidad);

        return self::SINONIMOS[$texto] ?? $texto;
    }

    // Esta función aplana un texto para poder buscar habilidades adentro
    // En terminos tecnicos, cuando se lee una habilidad o el texto del CV, se ejecuta esta función
    // y saca mayúsculas, acentos y signos
    public function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);
        $texto = str_replace(['.', '/', '-', '_'], ' ', $texto);
        $texto = preg_replace('/[^a-z0-9 ]+/', ' ', $texto) ?? $texto;
        $texto = preg_replace('/\s+/', ' ', $texto) ?? $texto;

        return trim($texto);
    }

    // Esta función lista las formas en que puede aparecer una habilidad
    // En terminos tecnicos, cuando se busca una habilidad en el texto del CV, se ejecuta esta función
    // y devuelve el nombre canónico junto con sus sinónimos
    public function formas(string $canonico): array
    {
        $formas = [$canonico];

        foreach (self::SINONIMOS as $variante => $canon) {
            if ($canon === $canonico) {
                $formas[] = $variante;
            }
        }

        return array_values(array_unique($formas));
    }

    // Esta función dice qué otras habilidades cubre una habilidad declarada
    // En terminos tecnicos, cuando no hay coincidencia exacta, se ejecuta esta función
    // y devuelve la lista de implicaciones de la base de conocimiento
    public function implicadas(string $canonico): array
    {
        return self::IMPLICA[$canonico] ?? [];
    }

    // Esta función busca qué habilidades declaradas alcanzan para cubrir otra
    // En terminos tecnicos, cuando el CV no nombra la habilidad pedida, se ejecuta esta función
    // y devuelve las que la implican
    public function queImplican(string $canonico): array
    {
        $origenes = [];

        foreach (self::IMPLICA as $origen => $destinos) {
            if (in_array($canonico, $destinos, true)) {
                $origenes[] = $origen;
            }
        }

        return $origenes;
    }
}
