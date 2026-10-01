{{-- Marco de las pantallas de un usuario ya logueado. Otras vistas lo extienden y solo rellenan título y contenido. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    {{-- Codificación y escala para que se vea bien en el celular. --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Título de la pestaña. Cada vista define la sección "title". --}}
    <title>@yield('title') — TalentLink</title>
    @vite(['resources/css/bootstrap.css'])
    <link rel="stylesheet" href="{{ asset('css/tema.css') }}">
    {{-- Tokens y tarjetas que todavía usan las pantallas. --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- Hueco para CSS extra de una pantalla puntual. --}}
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        {{-- Menú lateral con los accesos según el rol. --}}
        @include('partials.sidebar')
        <div class="tl-main d-flex flex-column flex-grow-1">
            {{-- Barra superior: título de la página y menú del avatar. --}}
            @include('partials.navbar')
            <div class="container-fluid p-4">
                {{-- En Android, el candidato ve el enlace para bajar la aplicación. --}}
                @if (auth()->user()->esCandidato() && preg_match('/Android/i', request()->userAgent() ?? ''))
                    <div class="alert alert-primary alert-dismissible fade show" role="alert">
                        Estás en el celular. Podés usar TalentLink desde la aplicación.
                        <a class="alert-link" href="{{ asset('TalentLink.apk') }}">Descargar</a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
                {{-- Aviso verde cuando un controlador guardó el mensaje en session('ok'). --}}
                @if (session('ok'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('ok') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
                {{-- Aviso rojo cuando un controlador guardó el mensaje en session('error'). --}}
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
                {{-- Contenido propio de cada pantalla. Cada vista lo define en la sección "content". --}}
                @yield('content')
            </div>
        </div>
    </div>
    @vite(['resources/js/app.js'])
    {{-- Espacio para JavaScript de una pantalla puntual. --}}
    @stack('scripts')
</body>
</html>
