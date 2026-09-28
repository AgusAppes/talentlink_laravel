{{-- Marco de las pantallas de un usuario ya logueado. Otras vistas lo extienden y solo rellenan título y contenido. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    {{-- Codificación y escala para que se vea bien en el celular. --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Título de la pestaña. Cada vista define la sección "title". --}}
    <title>@yield('title') — TalentLink</title>
    {{-- Estilos comunes del panel. --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- Hueco para CSS extra de una pantalla puntual. --}}
    @stack('styles')
</head>
<body>
    <div class="app">
        {{-- Menú lateral con los accesos según el rol. --}}
        @include('partials.sidebar')
        <div class="tl-main">
            {{-- Barra superior: título de la página, usuario y botón Salir. --}}
            @include('partials.navbar')
            <div class="tl-content">
                {{-- Aviso verde cuando un controlador guardó el mensaje en session('ok'). --}}
                @if (session('ok'))
                    <p class="tl-alert-ok">{{ session('ok') }}</p>
                @endif
                {{-- Aviso rojo cuando un controlador guardó el mensaje en session('error'). --}}
                @if (session('error'))
                    <p class="tl-alert-error">{{ session('error') }}</p>
                @endif
                {{-- Contenido propio de cada pantalla. Cada vista lo define en la sección "content". --}}
                {{-- yield es un método que permite insertar el contenido de otra vista en la vista actual --}}
                {{-- Es decir, en esta sección se renderiza el contenido de la vista que extiende/incluye este archivo --}}	
                @yield('content')
            </div>
        </div>
    </div>
    {{-- Espacio para JavaScript de una pantalla puntual. --}}
    @stack('scripts')
</body>
</html>
