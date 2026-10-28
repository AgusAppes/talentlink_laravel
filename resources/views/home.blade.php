{{--
    Home pública. No usa layouts/guest.php: ese layout es el marco angosto del login y el registro.

    TEXTOS: están en esta vista, sección por sección.
    CARRUSEL: reemplazá los archivos de public/images/home/ o cambiá el arreglo $diapositivas.
    TESTIMONIOS: el bloque marcado como demostración no son opiniones reales.
    CONTACTO: el proyecto no tiene correo, teléfono ni redes. Completalos en el footer.
--}}
{{-- Acá empieza la lista de imágenes del carrusel. Cada archivo está en public/images/home/. --}}
@php
    $diapositivas = [
        ['src' => 'images/home/carrusel-1.svg', 'alt' => 'Imagen de muestra: panel de selección de personal'],
        ['src' => 'images/home/carrusel-2.svg', 'alt' => 'Imagen de muestra: etapas de una postulación'],
        ['src' => 'images/home/carrusel-3.svg', 'alt' => 'Imagen de muestra: agenda de entrevistas'],
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio — TalentLink</title>
    @include('partials.iconos')
    @vite(['resources/css/bootstrap.css'])
    <link rel="stylesheet" href="{{ asset('css/tema.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>
<body class="tl-home">
    <a class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-primary" href="#contenido">Saltar al contenido</a>

    {{-- Acá empieza la barra de arriba: logo, enlaces a las secciones y los botones de entrar o registrarse. --}}
    <header class="tl-home-nav">
        <nav class="navbar navbar-expand-lg py-3" aria-label="Principal">
            <div class="container">
                <a class="navbar-brand tl-home-marca d-flex align-items-center p-0" href="{{ route('home') }}">
                    <img src="{{ asset('logo_TL.png') }}" alt="TalentLink" width="60" height="64">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuHome" aria-controls="menuHome" aria-expanded="false" aria-label="Abrir menú">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="menuHome">
                    <ul class="navbar-nav mx-auto gap-lg-2">
                        <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="#funcionalidades">Funcionalidades</a></li>
                        <li class="nav-item"><a class="nav-link" href="#opiniones">Opiniones</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
                    </ul>
                    <div class="d-flex flex-column flex-lg-row gap-2">
                        <a class="btn btn-outline-primary" href="{{ route('login') }}">Iniciar sesión</a>
                        <a class="btn btn-primary" href="{{ route('registro') }}">Registrarse</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main id="contenido">
        @if (session('error'))
            <div class="container pt-3">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            </div>
        @endif

        {{-- Acá empieza el primer bloque: el título, el texto de presentación y los botones. --}}
        <section class="tl-home-hero" id="inicio">
            <div class="container">
                <div class="row align-items-center gy-5">
                    <div class="col-lg-6">
                        <p class="tl-home-kicker">Plataforma de recursos humanos</p>
                        <h1>Conectamos talento con <span>oportunidades</span></h1>
                        <p class="lead mt-3">TalentLink ordena la búsqueda de personal: la empresa pide un perfil, el equipo de RRHH publica la oferta y el candidato se postula y sigue su proceso.</p>
                        <p class="text-secondary">Candidatos, empresas y personal de selección trabajan sobre la misma información, cada uno con su acceso.</p>
                        <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                            <a class="btn btn-primary btn-lg" href="{{ route('registro') }}">Crear cuenta</a>
                            <a class="btn btn-outline-primary btn-lg" href="#funcionalidades">Ver funcionalidades</a>
                        </div>
                    </div>
                    {{-- Acá empieza el dibujo de la derecha: tres tarjetas de ejemplo (oferta, perfil y etapa). --}}
                    <div class="col-lg-6">
                        <div class="tl-home-escena" aria-hidden="true">
                            <article class="tl-home-mini tl-home-mini-oferta">
                                <small>Oferta laboral</small>
                                <strong>Analista de selección</strong>
                                <p class="mb-2">Publicada para los candidatos.</p>
                                <span class="tag">Abierta</span>
                            </article>
                            <article class="tl-home-mini tl-home-mini-candidato">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="tl-home-avatar">LP</span>
                                    <div>
                                        <strong>Perfil del candidato</strong>
                                        <p class="mb-0">Datos, habilidades y experiencia.</p>
                                    </div>
                                </div>
                            </article>
                            <article class="tl-home-mini tl-home-mini-etapa">
                                <small>Postulación</small>
                                <strong>Etapa: entrevista</strong>
                                <p class="mb-0">El recorrido queda visible para quien gestiona el proceso.</p>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Acá empieza el texto que explica para qué sirve la plataforma y quién la usa. --}}
        <section class="tl-home-seccion pt-0" aria-labelledby="titulo-sistema">
            <div class="container">
                <div class="row align-items-end g-4 mb-4">
                    <div class="col-lg-7">
                        <h2 id="titulo-sistema">Un solo lugar para buscar, postularse y hacer el seguimiento</h2>
                    </div>
                    <div class="col-lg-5">
                        <p class="mb-0 text-secondary">La plataforma sirve para organizar solicitudes de personal, ofertas, perfiles y el avance de cada postulación. No reemplaza el criterio de selección: ordena la información con la que trabaja el equipo.</p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <article class="tl-home-rol">
                            <span>01</span>
                            <h3 class="h5">Candidatos</h3>
                            <p class="mb-0 text-secondary">Completan su perfil, ven las ofertas disponibles y consultan el estado de sus postulaciones.</p>
                        </article>
                    </div>
                    <div class="col-md-4">
                        <article class="tl-home-rol">
                            <span>02</span>
                            <h3 class="h5">Empresas</h3>
                            <p class="mb-0 text-secondary">Cargan solicitudes de personal y mantienen los datos de la empresa desde su propio acceso.</p>
                        </article>
                    </div>
                    <div class="col-md-4">
                        <article class="tl-home-rol">
                            <span>03</span>
                            <h3 class="h5">Personal de RRHH</h3>
                            <p class="mb-0 text-secondary">Publica ofertas, revisa candidatos, cambia la etapa de cada postulación y agenda entrevistas.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        {{-- Acá empiezan las tarjetas de funcionalidades: ofertas, solicitudes, candidatos, etapas, entrevistas, perfiles y avisos. --}}
        <section class="tl-home-seccion pt-0" id="funcionalidades" aria-labelledby="titulo-funciones">
            <div class="container">
                <h2 id="titulo-funciones" class="mb-2">Lo que ya podés hacer en TalentLink</h2>
                <p class="text-secondary mb-4">Estas tarjetas describen funciones que están en el sistema. Cada rol ve solo lo que le corresponde.</p>
                <div class="tl-home-bento">
                    <article class="tl-home-func tl-home-func-ancha">
                        <span class="tl-home-icono" aria-hidden="true">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25v-4.25m16.5 0a2.25 2.25 0 0 0 .75-1.636V6.75A2.25 2.25 0 0 0 18.75 4.5H5.25A2.25 2.25 0 0 0 3 6.75v5.764c0 .62.27 1.207.75 1.636m16.5 0v.11A2.25 2.25 0 0 1 18.75 16.5h-13.5A2.25 2.25 0 0 1 3 14.26v-.11"/></svg>
                        </span>
                        <h3>Ofertas laborales</h3>
                        <p>Las ofertas se publican, editan y listan. El candidato ve las disponibles y puede postularse desde ahí.</p>
                    </article>
                    <article class="tl-home-func tl-home-func-ancha">
                        <span class="tl-home-icono" aria-hidden="true">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </span>
                        <h3>Solicitudes de personal</h3>
                        <p>La empresa registra qué perfil necesita. RRHH consulta esas solicitudes y su estado antes de abrir la búsqueda.</p>
                    </article>
                    <article class="tl-home-func">
                        <span class="tl-home-icono" aria-hidden="true">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                        </span>
                        <h3>Candidatos</h3>
                        <p>El equipo consulta el listado y el detalle de cada persona: ubicación, habilidades y experiencia.</p>
                    </article>
                    <article class="tl-home-func">
                        <span class="tl-home-icono" aria-hidden="true">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>
                        </span>
                        <h3>Seguimiento del proceso</h3>
                        <p>Cada postulación tiene una etapa. RRHH la actualiza y puede calcular la compatibilidad del perfil con la oferta.</p>
                    </article>
                    <article class="tl-home-func">
                        <span class="tl-home-icono" aria-hidden="true">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        </span>
                        <h3>Entrevistas</h3>
                        <p>Hay un calendario para agendar, confirmar o cancelar entrevistas y para marcar horarios no disponibles.</p>
                    </article>
                    <article class="tl-home-func tl-home-func-ancha">
                        <span class="tl-home-icono" aria-hidden="true">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        </span>
                        <h3>Perfiles y currículums</h3>
                        <p>El candidato carga foto, descripción, habilidades y experiencia. Al postularse puede adjuntar el CV en PDF.</p>
                    </article>
                    <article class="tl-home-func tl-home-func-ancha">
                        <span class="tl-home-icono" aria-hidden="true">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                        </span>
                        <h3>Avisos dentro de la plataforma</h3>
                        <p>Quien tiene sesión recibe notificaciones en el panel y las puede marcar como leídas.</p>
                    </article>
                </div>
            </div>
        </section>

        {{-- Acá empieza el carrusel. Muestra las imágenes de $diapositivas, que están en public/images/home/. --}}
        <section class="tl-home-seccion pt-0" aria-labelledby="titulo-carrusel">
            <div class="container">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
                    <div>
                        <h2 id="titulo-carrusel" class="mb-2">Así se ve el trabajo de selección</h2>
                        <p class="text-secondary mb-0">Las imágenes de abajo son de muestra. Se reemplazan sin tocar el resto de la página.</p>
                    </div>
                </div>
                <div id="carruselHome" class="carousel slide tl-home-carrusel" data-bs-ride="carousel" data-bs-interval="5000">
                    <div class="carousel-indicators">
                        @foreach ($diapositivas as $indice => $diapositiva)
                            <button type="button" data-bs-target="#carruselHome" data-bs-slide-to="{{ $indice }}" @class(['active' => $indice === 0]) @if ($indice === 0) aria-current="true" @endif aria-label="Imagen {{ $indice + 1 }}"></button>
                        @endforeach
                    </div>
                    <div class="carousel-inner">
                        @foreach ($diapositivas as $indice => $diapositiva)
                            <div class="carousel-item {{ $indice === 0 ? 'active' : '' }}">
                                <img class="tl-home-slide" src="{{ asset($diapositiva['src']) }}" alt="{{ $diapositiva['alt'] }}">
                            </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carruselHome" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carruselHome" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                </div>
            </div>
        </section>

        {{-- Acá empiezan las opiniones. Son textos de demostración, no comentarios reales. --}}
        <section class="tl-home-seccion tl-home-opiniones" id="opiniones" aria-labelledby="titulo-opiniones">
            <div class="container">
                <h2 id="titulo-opiniones" class="mb-2">Opiniones</h2>
                <p class="text-secondary mb-4">Gracias a TalentLink pude encontrar oportunidades laborales y presentar mi perfil de manera sencilla. ¡Una plataforma muy práctica!</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <article class="tl-home-testimonio">
                            <div class="tl-home-estrellas mb-3" aria-label="Valoración de ejemplo: 5 de 5">
                                @for ($estrella = 0; $estrella < 5; $estrella++)
                                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.75 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.79.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29Z"/></svg>
                                @endfor
                            </div>
                            <p>Gracias a TalentLink pude encontrar oportunidades laborales y presentar mi perfil de manera sencilla. ¡Una plataforma muy práctica!</p>
                            <strong>Candidato de TalentLink</strong>
                            <small class="d-block text-secondary">Demostración</small>
                        </article>
                    </div>
                    <div class="col-md-4">
                        <article class="tl-home-testimonio">
                            <div class="tl-home-estrellas mb-3" aria-label="Valoración de ejemplo: 5 de 5">
                                @for ($estrella = 0; $estrella < 5; $estrella++)
                                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.75 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.79.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29Z"/></svg>
                                @endfor
                            </div>
                            <p>Una solución útil para gestionar nuestras solicitudes de personal y conectar con un equipo de selección de forma más organizada y eficiente</p>
                            <strong>Empresa reclutadora de TalentLink</strong>
                            <small class="d-block text-secondary">Demostración</small>
                        </article>
                    </div>
                    <div class="col-md-4">
                        <article class="tl-home-testimonio">
                            <div class="tl-home-estrellas mb-3" aria-label="Valoración de ejemplo: 5 de 5">
                                @for ($estrella = 0; $estrella < 5; $estrella++)
                                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.75 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.79.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29Z"/></svg>
                                @endfor
                            </div>TalentLink nos ayuda a mantener la información organizada y facilita el seguimiento de los candidatos durante los procesos de selección.</p>
                            <strong>RRHH de TalentLink</strong>
                            <small class="d-block text-secondary">Demostración</small>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        {{-- Acá empieza el recuadro final, que invita a crear una cuenta. --}}
        <section class="tl-home-cta" aria-labelledby="titulo-cta">
            <div class="container">
                <div class="tl-home-cta-panel">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <h2 id="titulo-cta" class="text-white mb-2">Empezá con tu cuenta</h2>
                            <p class="mb-0">Registrate como candidato o entrá si ya formás parte de una empresa o del equipo de selección.</p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <a class="btn btn-light btn-lg" href="{{ route('registro') }}">Registrarse</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- Acá empieza el pie: descripción, enlaces, acceso y el espacio de contacto. --}}
    <footer class="tl-home-footer" id="contacto">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a class="d-inline-flex mb-3" href="{{ route('home') }}">
                        <img src="{{ asset('logo_TL.png') }}" alt="TalentLink" width="68" height="72">
                    </a>
                    <p>Plataforma para organizar ofertas, solicitudes de personal, perfiles de candidatos y el avance de las postulaciones.</p>
                </div>
                <div class="col-6 col-lg-2">
                    <strong class="d-block mb-2">Secciones</strong>
                    <ul class="list-unstyled d-grid gap-2 mb-0">
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#funcionalidades">Funcionalidades</a></li>
                        <li><a href="#opiniones">Opiniones</a></li>
                        <li><a href="#contacto">Contacto</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-3">
                    <strong class="d-block mb-2">Acceso</strong>
                    <ul class="list-unstyled d-grid gap-2 mb-0">
                        <li><a href="{{ route('login') }}">Iniciar sesión</a></li>
                        <li><a href="{{ route('registro') }}">Registrarse</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    
                    <strong class="d-block mb-2">Contacto</strong>
                    <p class="mb-0"> TalentLink@gmail.com <br> 3765-121314</p>
                </div>
            </div>
            <p class="mt-4 mb-0 pt-3 border-top border-white border-opacity-10 small">© {{ date('Y') }} TalentLink</p>
        </div>
    </footer>

    @vite(['resources/js/app.js'])
    <script type="module">
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches && window.bootstrap) {
            document.querySelectorAll('.carousel').forEach((elemento) => {
                window.bootstrap.Carousel.getOrCreateInstance(elemento).pause();
            });
        }
    </script>
</body>
</html>
