<?php

return [

    // Programas del sistema. En Docker están en el PATH; en local, la ruta completa si hace falta.
    'tesseract' => env('TESSERACT_PATH', 'tesseract'),
    'pdftotext' => env('PDFTOTEXT_PATH', 'pdftotext'),
    'pdftoppm' => env('PDFTOPPM_PATH', 'pdftoppm'),

];
