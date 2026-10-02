<?php

namespace App\Experto;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class LectorCv
{
    // Esta función saca el texto de un CV en PDF
    // En terminos tecnicos, cuando se calcula la compatibilidad de una postulación con CV, se ejecuta esta función
    // y usa el texto del PDF o, si no tiene, las dos primeras páginas con Tesseract
    public function texto(?string $ruta): string
    {
        if (! $ruta) {
            return '';
        }

        $pdf = null;

        try {
            $pdf = $this->copiarPdf($ruta);

            if ($pdf === null) {
                return '';
            }

            $digital = trim(preg_replace('/\s+/', ' ', $this->textoDigital($pdf) ?? '') ?? '');

            if (mb_strlen($digital) >= 20) {
                return $digital;
            }

            $ocr = trim($this->textoOcr($pdf));

            return $ocr !== '' ? $ocr : $digital;
        } catch (\Throwable $e) {
            report($e);

            return '';
        } finally {
            if ($pdf !== null && is_file($pdf)) {
                @unlink($pdf);
            }
        }
    }

    // Esta función copia el CV a un archivo temporal
    // En terminos tecnicos, cuando hay que leer el PDF, se ejecuta esta función
    // y lo baja del disco de CV a un archivo local con extensión .pdf
    private function copiarPdf(string $ruta): ?string
    {
        $disco = Storage::disk(config('filesystems.cv_disk'));

        if (! $disco->exists($ruta)) {
            return null;
        }

        $temporal = tempnam(sys_get_temp_dir(), 'cv');

        if ($temporal === false) {
            return null;
        }

        $pdf = $temporal.'.pdf';
        rename($temporal, $pdf);

        if (file_put_contents($pdf, $disco->get($ruta)) === false) {
            @unlink($pdf);

            return null;
        }

        return $pdf;
    }

    // Esta función lee la capa de texto del PDF
    // En terminos tecnicos, cuando el CV es un PDF digital, se ejecuta esta función
    // y llama a pdftotext sobre las dos primeras páginas
    private function textoDigital(string $pdf): ?string
    {
        return $this->ejecutar([
            config('experto.pdftotext'),
            '-enc', 'UTF-8',
            '-f', '1',
            '-l', '2',
            $pdf,
            '-',
        ], 10);
    }

    // Esta función lee un CV escaneado convirtiéndolo en imágenes
    // En terminos tecnicos, cuando el PDF no trae texto, se ejecuta esta función
    // y pasa hasta dos páginas por pdftoppm y después por Tesseract
    private function textoOcr(string $pdf): string
    {
        $directorio = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tl-ocr-'.bin2hex(random_bytes(4));

        if (! @mkdir($directorio) && ! is_dir($directorio)) {
            return '';
        }

        try {
            $prefijo = $directorio.DIRECTORY_SEPARATOR.'pagina';
            $conversion = $this->ejecutar([
                config('experto.pdftoppm'),
                '-png',
                '-r', '120',
                '-f', '1',
                '-l', '2',
                $pdf,
                $prefijo,
            ], 15);

            if ($conversion === null) {
                return '';
            }

            $paginas = glob($prefijo.'*.png') ?: [];
            sort($paginas);
            $texto = '';

            foreach (array_slice($paginas, 0, 2) as $pagina) {
                $salida = $this->ocrPagina($pagina);

                if ($salida !== '') {
                    $texto .= ' '.$salida;
                }
            }

            return trim($texto);
        } finally {
            foreach (glob($directorio.DIRECTORY_SEPARATOR.'*') ?: [] as $archivo) {
                @unlink($archivo);
            }

            @rmdir($directorio);
        }
    }

    // Esta función pasa una página del CV por Tesseract
    // En terminos tecnicos, cuando ya hay una imagen PNG, se ejecuta esta función
    // y prueba español más inglés, o solo inglés si falta el idioma
    private function ocrPagina(string $pagina): string
    {
        $tesseract = config('experto.tesseract');
        $salida = $this->ejecutar([$tesseract, $pagina, 'stdout', '-l', 'spa+eng', '--psm', '6'], 15);

        if ($salida !== null && trim($salida) !== '') {
            return $salida;
        }

        return trim($this->ejecutar([$tesseract, $pagina, 'stdout', '-l', 'eng', '--psm', '6'], 15) ?? '');
    }

    // Esta función corre un programa externo y devuelve su salida
    // En terminos tecnicos, cuando hace falta pdftotext, pdftoppm o Tesseract, se ejecuta esta función
    // y devuelve null si el programa no está o no termina bien
    private function ejecutar(array $comando, int $segundos): ?string
    {
        $proceso = new Process($comando);
        $proceso->setTimeout($segundos);

        try {
            $proceso->run();
        } catch (\Throwable) {
            return null;
        }

        if (! $proceso->isSuccessful()) {
            return null;
        }

        return $proceso->getOutput();
    }
}
