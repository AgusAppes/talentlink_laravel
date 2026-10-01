<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TalentLink')</title>
    @vite(['resources/css/bootstrap.css'])
    <link rel="stylesheet" href="{{ asset('css/tema.css') }}">
</head>
<body>
    <main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
        <div class="w-100" style="max-width: 28rem;">
            <img class="d-block mx-auto mb-3 rounded" src="{{ asset('images/logo.jpg') }}" alt="TalentLink" width="96">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
    @vite(['resources/js/app.js'])
</body>
</html>
