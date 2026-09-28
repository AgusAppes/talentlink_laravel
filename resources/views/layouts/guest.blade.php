<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TalentLink')</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <div class="lg-shell">
        <aside class="lg-brand">
            <div class="lg-brand-body">
                <img class="lg-logo" src="{{ asset('images/logo.jpg') }}" alt="TalentLink">
                <h1 class="lg-brand-title">Conectamos talento con <span>oportunidades</span></h1>
                <p class="lg-brand-text">La consultora de RRHH que reúne a empresas y candidatos en un solo lugar.</p>
            </div>
            <p class="lg-brand-footer">© {{ date('Y') }} TalentLink</p>
        </aside>
        <main class="lg-panel">
            @yield('content')
        </main>
    </div>
</body>
</html>
