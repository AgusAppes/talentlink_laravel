<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} — TalentLink</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="lg-placeholder">
    <main class="lg-card">
        <h1 class="lg-card-title">{{ $titulo }}</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="lg-btn" type="submit">Cerrar sesión</button>
        </form>
    </main>
</body>
</html>
