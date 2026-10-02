<?php

namespace App\Experto;

use DateTimeImmutable;

class MotorCompatibilidad
{
    // Esta función recibe la base de conocimiento del sistema experto
    // En terminos tecnicos, cuando Laravel arma el evaluador, se ejecuta esta función
    // y deja listas las reglas de sinónimos, implicaciones y pesos
    public function __construct(private BaseConocimiento $base) {}

    // Esta función infiere el porcentaje de compatibilidad y explica qué reglas se cumplieron
    // En terminos tecnicos, cuando se evalúa una postulación, se ejecuta esta función
    // y compara habilidades, años y ciudad sin leer la base de datos
    public function inferir(
        array $habilidadesRequeridas,
        array $habilidadesPerfil,
        array $experiencias,
        ?int $aniosRequeridos,
        ?int $modalidadId,
        ?int $ciudadOferta,
        ?int $ciudadCandidato,
        string $textoCv = '',
        ?string $estadoCv = null,
        ?string $fechaReferencia = null,
    ): ResultadoCompatibilidad {
        $requeridas = $this->requeridas($habilidadesRequeridas);

        if ($requeridas === []) {
            return new ResultadoCompatibilidad(false, null, [
                'La solicitud no tiene habilidades para comparar.',
            ], [], []);
        }

        $declaradas = $this->declaradas($habilidadesPerfil, $experiencias);
        $texto = $this->base->normalizar($textoCv);
        $fechaReferencia ??= (new DateTimeImmutable('today'))->format('Y-m-d');

        $reglas = [];
        $coincidencias = [];
        $faltantes = [];
        $pesoObtenido = 0;

        foreach ($requeridas as $canonico => $label) {
            $evidencia = $this->evidencia($canonico, $label, $declaradas, $texto);

            if ($evidencia === null) {
                $faltantes[] = $label;
                $reglas[] = 'Falta '.$label.'.';

                continue;
            }

            $pesoObtenido += $evidencia['peso'];
            $coincidencias[] = [
                'habilidad' => $label,
                'regla' => $evidencia['regla'],
                'peso' => $evidencia['peso'],
            ];
            $reglas[] = $evidencia['regla'];
        }

        $valorHabilidades = (int) round(
            100 * $pesoObtenido / (count($requeridas) * BaseConocimiento::PESO_PERFIL)
        );

        $partes = [[
            'peso' => BaseConocimiento::PESO_HABILIDADES,
            'valor' => $valorHabilidades,
        ]];

        $this->sumarAnios($partes, $reglas, $aniosRequeridos, $experiencias, $fechaReferencia);
        $this->sumarUbicacion($partes, $reglas, $modalidadId, $ciudadOferta, $ciudadCandidato);
        $this->sumarCv($reglas, $estadoCv);

        return new ResultadoCompatibilidad(
            true,
            $this->porcentaje($partes),
            $reglas,
            $coincidencias,
            $faltantes,
        );
    }

    // Esta función deja una sola vez cada habilidad pedida
    // En terminos tecnicos, cuando empieza la inferencia, se ejecuta esta función
    // y arma la lista canónica conservando el texto original
    private function requeridas(array $habilidades): array
    {
        $lista = [];

        foreach ($habilidades as $habilidad) {
            $label = trim((string) $habilidad);
            $canonico = $this->base->canonizar($label);

            if ($canonico === '' || isset($lista[$canonico])) {
                continue;
            }

            $lista[$canonico] = $label;
        }

        return $lista;
    }

    // Esta función junta las habilidades del perfil y de la experiencia, sin repetir
    // En terminos tecnicos, cuando el motor busca coincidencias, se ejecuta esta función
    // y se queda con el origen de mayor peso para cada habilidad
    private function declaradas(array $habilidadesPerfil, array $experiencias): array
    {
        $lista = [];

        foreach ($habilidadesPerfil as $habilidad) {
            $this->anotar($lista, (string) $habilidad, BaseConocimiento::PESO_PERFIL, 'perfil');
        }

        foreach ($experiencias as $experiencia) {
            foreach ($experiencia['habilidades'] ?? [] as $habilidad) {
                $this->anotar($lista, (string) $habilidad, BaseConocimiento::PESO_EXPERIENCIA, 'experiencia');
            }
        }

        return $lista;
    }

    // Esta función anota una habilidad si aporta más peso que la que ya estaba
    // En terminos tecnicos, cuando se recorre el perfil o una experiencia, se ejecuta esta función
    // y guarda el nombre original, el origen y el peso
    private function anotar(array &$lista, string $habilidad, int $peso, string $origen): void
    {
        $label = trim($habilidad);
        $canonico = $this->base->canonizar($label);

        if ($canonico === '' || $peso <= ($lista[$canonico]['peso'] ?? 0)) {
            return;
        }

        $lista[$canonico] = [
            'nombre' => $label,
            'origen' => $origen,
            'peso' => $peso,
        ];
    }

    // Esta función elige la mejor evidencia para una habilidad pedida
    // En terminos tecnicos, cuando se recorre cada habilidad de la solicitud, se ejecuta esta función
    // y prioriza perfil, después experiencia, después una implicación y al final el CV
    private function evidencia(string $canonico, string $label, array $declaradas, string $texto): ?array
    {
        if (isset($declaradas[$canonico])) {
            $dato = $declaradas[$canonico];

            return [
                'peso' => $dato['peso'],
                'regla' => $dato['origen'] === 'perfil'
                    ? 'Coincide '.$label.' en las habilidades del perfil.'
                    : 'Coincide '.$label.' en la experiencia laboral.',
            ];
        }

        $mejor = null;

        foreach ($declaradas as $declarada => $dato) {
            if (! in_array($canonico, $this->base->implicadas($declarada), true)) {
                continue;
            }

            $peso = max(1, $dato['peso'] - 1);

            if ($mejor !== null && $peso <= $mejor['peso']) {
                continue;
            }

            $lugar = $dato['origen'] === 'perfil' ? 'en el perfil' : 'en la experiencia';
            $mejor = [
                'peso' => $peso,
                'regla' => $dato['nombre'].' '.$lugar.' aporta '.$label.'.',
            ];
        }

        $mencion = $this->mencionEnCv($canonico, $label, $texto);

        if ($mencion !== null && ($mejor === null || $mencion['peso'] > $mejor['peso'])) {
            return $mencion;
        }

        return $mejor;
    }

    // Esta función busca la habilidad, o una que la implique, en el texto del CV
    // En terminos tecnicos, cuando el perfil no cubre la habilidad pedida, se ejecuta esta función
    // y la cuenta con el peso más bajo si aparece como palabra entera
    private function mencionEnCv(string $canonico, string $label, string $texto): ?array
    {
        if ($texto === '') {
            return null;
        }

        if ($this->aparece($canonico, $texto)) {
            return [
                'peso' => BaseConocimiento::PESO_CV,
                'regla' => 'El CV menciona '.$label.'.',
            ];
        }

        foreach ($this->base->queImplican($canonico) as $origen) {
            if ($this->aparece($origen, $texto)) {
                return [
                    'peso' => BaseConocimiento::PESO_CV,
                    'regla' => 'El CV menciona '.$origen.', que aporta '.$label.'.',
                ];
            }
        }

        return null;
    }

    // Esta función revisa si una habilidad está escrita en el CV
    // En terminos tecnicos, cuando se analiza el texto ya normalizado, se ejecuta esta función
    // y exige la palabra o la frase completa, no un pedazo de otra palabra
    private function aparece(string $canonico, string $texto): bool
    {
        $plano = ' '.$texto.' ';

        foreach ($this->base->formas($canonico) as $forma) {
            if (str_contains($plano, ' '.$forma.' ')) {
                return true;
            }
        }

        return false;
    }

    // Esta función suma el factor de años si hay fechas para comparar
    // En terminos tecnicos, cuando la solicitud pide años de experiencia, se ejecuta esta función
    // y calcula los períodos del candidato sin contar dos veces el mismo tiempo
    private function sumarAnios(array &$partes, array &$reglas, ?int $aniosRequeridos, array $experiencias, string $fechaReferencia): void
    {
        if ($aniosRequeridos === null || $aniosRequeridos <= 0) {
            $reglas[] = 'La solicitud no exige años de experiencia.';

            return;
        }

        $anios = $this->anios($experiencias, $fechaReferencia);

        if ($anios === null) {
            $reglas[] = 'No se evaluaron los años porque el perfil no tiene fechas de experiencia.';

            return;
        }

        $valor = (int) min(100, round(($anios / $aniosRequeridos) * 100));
        $partes[] = [
            'peso' => BaseConocimiento::PESO_ANIOS,
            'valor' => $valor,
        ];
        $reglas[] = 'Tiene '.$this->textoAnios($anios).' de experiencia y la solicitud pide '.$aniosRequeridos.'.';
    }

    // Esta función suma los años de experiencia sin superponer trabajos
    // En terminos tecnicos, cuando el factor de años entra en el porcentaje, se ejecuta esta función
    // y une los intervalos de fechas antes de dividirlos por 365,25
    private function anios(array $experiencias, string $fechaReferencia): ?float
    {
        $intervalos = [];

        foreach ($experiencias as $experiencia) {
            $desde = $experiencia['desde'] ?? null;

            if (! $desde) {
                continue;
            }

            $hasta = $experiencia['hasta'] ?: $fechaReferencia;

            if ($hasta > $fechaReferencia) {
                $hasta = $fechaReferencia;
            }

            if ($desde > $hasta) {
                continue;
            }

            $intervalos[] = [$desde, $hasta];
        }

        if ($intervalos === []) {
            return null;
        }

        usort($intervalos, fn (array $a, array $b) => strcmp($a[0], $b[0]));

        $fusion = [$intervalos[0]];

        foreach (array_slice($intervalos, 1) as $intervalo) {
            $ultimo = $fusion[array_key_last($fusion)];

            if ($intervalo[0] <= $ultimo[1]) {
                if ($intervalo[1] > $ultimo[1]) {
                    $fusion[array_key_last($fusion)][1] = $intervalo[1];
                }

                continue;
            }

            $fusion[] = $intervalo;
        }

        $dias = 0;

        foreach ($fusion as [$desde, $hasta]) {
            $dias += (new DateTimeImmutable($desde))->diff(new DateTimeImmutable($hasta))->days;
        }

        return $dias / 365.25;
    }

    // Esta función escribe los años con una coma decimal
    // En terminos tecnicos, cuando se arma la explicación de experiencia, se ejecuta esta función
    // y elige "año" o "años" según el número
    private function textoAnios(float $anios): string
    {
        $texto = number_format($anios, 1, ',', '');
        $unidad = (int) round($anios) === 1 ? 'año' : 'años';

        return $texto.' '.$unidad;
    }

    // Esta función suma el factor de ciudad según la modalidad
    // En terminos tecnicos, cuando la solicitud tiene modalidad y ciudad, se ejecuta esta función
    // y no resta puntos si la modalidad es remota
    private function sumarUbicacion(array &$partes, array &$reglas, ?int $modalidadId, ?int $ciudadOferta, ?int $ciudadCandidato): void
    {
        if ($modalidadId === 2) {
            $partes[] = [
                'peso' => BaseConocimiento::PESO_UBICACION,
                'valor' => 100,
            ];
            $reglas[] = 'La modalidad es remota, así que la ciudad no resta compatibilidad.';

            return;
        }

        if (! $ciudadOferta || ! $ciudadCandidato || ! in_array($modalidadId, [1, 3], true)) {
            $reglas[] = 'No se evaluó la ciudad porque falta el dato en el perfil o en la solicitud.';

            return;
        }

        if ($ciudadOferta === $ciudadCandidato) {
            $partes[] = [
                'peso' => BaseConocimiento::PESO_UBICACION,
                'valor' => 100,
            ];
            $reglas[] = 'La ciudad del candidato coincide con la de la solicitud.';

            return;
        }

        if ($modalidadId === 3) {
            $partes[] = [
                'peso' => BaseConocimiento::PESO_UBICACION,
                'valor' => 50,
            ];
            $reglas[] = 'La modalidad es híbrida y la ciudad no coincide.';

            return;
        }

        $partes[] = [
            'peso' => BaseConocimiento::PESO_UBICACION,
            'valor' => 0,
        ];
        $reglas[] = 'La modalidad es presencial y la ciudad no coincide.';
    }

    // Esta función deja asentado si el CV se pudo leer
    // En terminos tecnicos, cuando la postulación tiene un PDF, se ejecuta esta función
    // y agrega una regla según haya texto o no
    private function sumarCv(array &$reglas, ?string $estadoCv): void
    {
        if ($estadoCv === 'leido') {
            $reglas[] = 'Se leyó el CV y se usó como evidencia adicional.';
        }

        if ($estadoCv === 'no_leido') {
            $reglas[] = 'No se pudo leer el CV. La compatibilidad usa el perfil y la experiencia.';
        }
    }

    // Esta función cierra el porcentaje con los factores que sí tenían datos
    // En terminos tecnicos, cuando ya se aplicaron las reglas, se ejecuta esta función
    // y pondera habilidades, años y ciudad sin contar el factor que se omitió
    private function porcentaje(array $partes): int
    {
        $acumulado = 0;
        $pesoTotal = 0;

        foreach ($partes as $parte) {
            $acumulado += $parte['valor'] * $parte['peso'];
            $pesoTotal += $parte['peso'];
        }

        if ($pesoTotal === 0) {
            return 0;
        }

        return max(0, min(100, (int) round($acumulado / $pesoTotal)));
    }
}
