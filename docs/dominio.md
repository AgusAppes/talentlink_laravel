# TalentLink — Dominio del sistema

Documento de referencia del proyecto. Describe qué es TalentLink, qué problema resuelve, sus módulos, fortalezas y limitaciones del prototipo actual, y la planificación de migración a Laravel.

**Integrantes:** Appes Agustina, Benitez Tiara Guadalupe  
**Institución:** Escuela Normal Superior N.º 10 — Anexo Comercial San Antonio  
**Materia:** Lenguaje Generador de Informes (LGI) — Análisis de Sistemas, 2.º año — 2026

---

## 1. ¿Qué es TalentLink?

**TalentLink** es una plataforma web de **gestión de reclutamiento y selección de personal**. Está pensada para una **consultora de Recursos Humanos** que actúa como intermediaria entre:

- **Empresas cliente** que necesitan cubrir vacantes.
- **Candidatos** que buscan oportunidades laborales.

Centraliza en un solo lugar solicitudes, ofertas, postulaciones y seguimiento por etapas, reemplazando procesos que hoy suelen repartirse entre correos, mensajes, planillas y carpetas con CVs.

**Eslogan:** *"Conectamos talento con oportunidades."*

**Tipo de sistema:** aplicación web (PHP + MySQL en el prototipo; migración planificada a Laravel). No es app móvil nativa.

---

## 2. Problemática que resuelve

### Situación sin sistema

En muchas consultoras de RRHH el circuito de contratación se gestiona de forma **manual y dispersa**:

- Las empresas piden personal por email o WhatsApp.
- Las ofertas se publican en distintos medios sin registro unificado.
- Los CVs llegan sueltos y se archivan en carpetas o Excel.
- El estado de cada candidato depende de la memoria o notas del reclutador.

### Consecuencias

- Desorganización y **pérdida de información**.
- **Demoras** al responder a empresas y candidatos.
- **Sobrecarga** del personal de RRHH.
- Poca visibilidad sobre búsquedas, ofertas y postulaciones activas.
- Comunicación **fragmentada** entre empresa, consultora y candidato.

### Necesidad

Una solución que **centralice y ordene** el ciclo completo: desde que la empresa solicita personal hasta que RRHH avanza (o cierra) cada postulación, con acceso diferenciado según el rol de quien usa el sistema.

---

## 3. Objetivos del proyecto

1. **Automatizar y centralizar** la vinculación entre empresas y candidatos.
2. **Gestionar solicitudes de búsqueda** cargadas por las empresas.
3. **Publicar ofertas laborales** a partir de esas búsquedas (personal RRHH).
4. **Recibir y administrar postulaciones** de candidatos registrados.
5. **Hacer seguimiento por etapas** del proceso de selección.
6. **Administrar perfiles** de candidatos, empresas y personal de RRHH.
7. **Visualizar métricas** en un panel de resumen para apoyar decisiones.

---

## 4. Actores y roles

| Rol | Quién es | Perfil en BD | Al iniciar sesión |
|-----|----------|--------------|-------------------|
| **admin** | Personal RRHH de la consultora | `personal_rrhh` | Dashboard |
| **empresa** | Cliente que pide personal | `empresas` | Nueva solicitud |
| **candidato** | Persona en búsqueda de empleo | `candidatos` | Ofertas (feed) |

Cada usuario (`usuarios`) tiene un rol y **un** perfil vinculado. El login usa `correo` + `password` (hash bcrypt).

---

## 5. Flujo principal del negocio

```
Empresa solicita personal
        ↓
Solicitud / búsqueda registrada (+ detalle + habilidades)
        ↓
RRHH revisa y cambia estado de la búsqueda
        ↓
RRHH publica una oferta laboral (una por búsqueda)
        ↓
Candidato ve ofertas publicadas y se postula
        ↓
Postulación creada (etapa inicial: Pendiente de revisión)
        ↓
RRHH avanza etapas → Rechazada / Aprobada / En revisión
        ↓
Empresa consulta el avance de sus búsquedas y postulaciones
```

### Reglas de negocio importantes

- **Una oferta por búsqueda.**
- Solo **admin/RRHH** publica ofertas y cambia su estado.
- Solo **empresa** crea solicitudes de personal.
- **Empresa** solo ve datos de su organización.
- **Candidato** no gestiona ofertas ni etapas; solo postula y ve sus postulaciones.
- No se permite **duplicar** postulación (mismo candidato + misma oferta).

---

## 6. Módulos del sistema

Estado referido al **prototipo PHP** actual. En Laravel se reimplementará el mismo alcance funcional.

| Módulo | Funcionalidad | Estado prototipo |
|--------|---------------|------------------|
| **Autenticación** | Login, logout, registro de candidato, sesión y permisos | Completo |
| **Dashboard** | Métricas para admin; refresco automático vía AJAX | Completo |
| **Solicitudes** | Alta (empresa), listado (admin / mis solicitudes), cambio de estado (admin) | Completo |
| **Ofertas** | Publicar desde búsqueda, editar estado, listado admin, feed candidato | Completo |
| **Postulaciones** | Postular, mis postulaciones, avanzar etapa (admin) | Completo |
| **Candidatos** | Listado y detalle (admin); perfil propio (candidato) | Completo |
| **Empresa** | Edición del perfil de la empresa | Completo |
| **Usuarios** | Listado, alta, roles y permisos (admin) | Completo |
| **Empresas (ABM admin)** | Gestión centralizada de empresas | Pendiente |
| **Reportes / estadísticas** | Más allá del dashboard básico | Pendiente |
| **Carga de CV** | Archivos adjuntos del candidato | Pendiente |
| **Solicitudes (refinamiento)** | Detalle, editar y eliminar solicitud | Pendiente |

### Menú lateral (visión por rol)

- **Admin:** Dashboard, Solicitudes, Ofertas, Candidatos, Postulaciones, Usuarios.
- **Empresa:** Nueva solicitud, Mis solicitudes, Mi empresa.
- **Candidato:** Ofertas, Mis postulaciones, Mi perfil.

---

## 7. Puntos fuertes del proyecto

| Aspecto | Detalle |
|---------|---------|
| **Dominio claro** | Flujo empresa → RRHH → candidato bien definido y ya implementado en el prototipo. |
| **Roles y permisos** | Tres actores con permisos granulares (`ofertas.ver`, `postulaciones.gestionar`, etc.). |
| **Circuito completo** | Desde la solicitud hasta el avance de etapas, probado con datos demo. |
| **Base de datos estructurada** | Modelo relacional con catálogos, geo (Argentina / Georef) y entidades de negocio. |
| **Interfaz coherente** | Diseño propio (login, panel, sidebar, CSS por módulo). |
| **Datos de prueba** | Seeders con usuarios, empresas, candidatos y búsquedas demo (`12345678`). |
| **Alineación académica** | Propuesta original contempla Laravel; la migración cierra esa brecha. |

---

## 8. Puntos débiles y limitaciones

| Aspecto | Detalle |
|---------|---------|
| **Arquitectura del prototipo** | PHP plano con un solo `index.php?accion=...`; rutas, lógica y vistas poco separadas. |
| **Mezcla PHP / HTML** | Algunas vistas mezclan presentación y lógica; dificulta mantenimiento y ampliaciones. |
| **SQL directo** | Consultas repetidas en controladores; sin capa de modelos reutilizable. |
| **Sin framework** | No hay convenciones estándar (rutas nombradas, validación centralizada, policies). |
| **Funcionalidades pendientes** | CV, reportes avanzados, ABM de empresas, refinamiento de solicitudes. |
| **Escalabilidad** | El diseño actual sirve para prototipo y demo, no para producción sin refactor. |
| **Tests automatizados** | No hay suite de pruebas unitarias o de integración. |

Estas limitaciones motivan la **migración a Laravel**, no un parche sobre el código existente.

---

## 9. Modelo de datos (resumen)

Base de datos MySQL: `talent_link`.

**Seguridad e identidad:** `usuarios`, `roles`, `permisos`, `permisos_por_roles`

**Actores:** `empresas`, `candidatos`, `personal_rrhh`

**Proceso de reclutamiento:** `busquedas`, `detalle_busquedas`, `estado_busqueda`, `ofertas`, `estado_ofertas`, `postulaciones`, `postulaciones_por_candidatos`, `etapas`

**Catálogos y geo:** `habilidades`, `habilidades_por_busqueda`, `modalidades`, `paises`, `provincias`, `ciudades`

Relación simplificada:

```
Empresa → Búsqueda → Detalle + Habilidades
              ↓
            Oferta → Postulación → Etapa
                        ↓
                    Candidato
```

---

## 10. Planificación de migración a Laravel

### Enfoque

Trasladar la **lógica de negocio** ya validada en el prototipo PHP a la **arquitectura de Laravel**, iniciando un nuevo proyecto del framework y distribuyendo cada módulo en rutas, controladores, modelos, vistas Blade y base de datos (migrations/seeders). Se busca **conservar el funcionamiento** desarrollado, incorporándolo a una estructura más ordenada, clara y mantenible.

El prototipo PHP permanece como **referencia funcional**; no se copia archivo por archivo.

### Stack en Laravel

- Laravel + Blade + Eloquent
- Auth de sesión propia (tabla `usuarios`, login con `correo`)
- Form Requests (validación) + Policies (autorización)
- Misma base `talent_link` y mismos nombres de tablas
- CSS del diseño actual en `public/css/`

**No se usará:** Breeze/Jetstream, Spatie, Livewire, Inertia, capa de services/repositorios innecesaria.

### Orden de implementación

| Fase | Tarea | Responsable (equipo) | Resultado esperado |
|------|-------|----------------------|--------------------|
| 1 | Creación del proyecto Laravel (`.env`, Laragon, `public/`) | Agustina | Proyecto base operativo |
| 2 | Migrations del esquema | Tiara | Tablas en MySQL |
| 3 | Seeders (roles, permisos, geo, catálogos, demo) | Agustina | Datos para probar |
| 4 | Módulo autenticación | Tiara | Login/logout/registro y permisos |
| 5 | Layouts (panel + guest, sidebar) | Equipo | Estructura visual común |
| 6 | Módulo solicitudes | Agustina | Alta y gestión por rol |
| 7 | Módulo usuarios | Tiara | ABM y permisos admin |
| 8 | Módulo ofertas | Agustina | Publicar + feed candidato |
| 9 | Módulo postulaciones | Tiara | Postular + avanzar etapas |
| 10 | Perfiles, candidatos admin, dashboard | Equipo | Cierre del circuito |
| 11 | Pruebas integradas (3 roles) | Ambas | Paridad con prototipo |

Cada fase se **cierra y prueba** antes de pasar a la siguiente.

### Patrón por módulo (Laravel)

1. Modelo Eloquent (`$table` cuando el nombre no sea el default)
2. Policy (quién puede)
3. Form Request (validación POST/PUT)
4. Controlador delgado
5. Vistas Blade que extienden el layout
6. Rutas nombradas en `routes/web.php`

### Resultado esperado de la migración

Sistema TalentLink en Laravel capaz de recorrer el flujo completo con admin, empresa y candidato; mismas reglas de permiso y pantallas equivalentes al prototipo; código organizado según convenciones del framework y base preparada para funcionalidades pendientes (CV, reportes, ABM empresas).

### Posibles dificultades

- Adaptar autenticación a `usuarios` / `correo` (no el esquema default `users` / `email` de Laravel).
- Respetar permisos y visibilidad por rol en cada módulo.
- Mantener reglas de negocio (una oferta por búsqueda, no duplicar postulaciones).
- Coordinar trabajo en paralelo sin conflictos en archivos compartidos (layouts, rutas).
- Curva de aprendizaje del framework si el equipo es nuevo en Laravel.

---

## 11. Especificación técnica (referencia para desarrollo)

Esta sección concentra reglas que el código Laravel debe respetar.

### Permisos por rol

| Permiso | admin | empresa | candidato |
|---------|-------|---------|-----------|
| dashboard.ver | sí | | |
| solicitudes.ver | sí | | |
| solicitudes.crear | | sí | |
| solicitudes.editar | sí | | |
| solicitudes.eliminar | sí | | |
| candidatos.ver | sí | | |
| candidatos.editar | sí | | sí (solo perfil propio) |
| empresas.ver | sí | | |
| empresas.editar | sí | sí (solo la suya) | |
| ofertas.ver | sí | | sí |
| ofertas.crear | sí | | |
| postulaciones.ver | sí | | sí (las suyas) |
| postulaciones.crear | | | sí |
| postulaciones.gestionar | sí | | |
| reportes.ver | sí | | |
| usuarios.ver | sí | | |
| usuarios.administrar | sí | | |

Admin tiene todos **excepto** `solicitudes.crear`.

### Catálogos (ids fijos en seeders)

- **estado_busqueda:** 1 Pendiente, 2 En oferta, 3 Cerrada
- **estado_ofertas:** 1 Publicada, 2 En pausa, 3 Finalizada
- **modalidades:** 1 Presencial, 2 Remoto, 3 Híbrida
- **etapas:** 1 Pendiente de revisión, 2 En revisión, 3 Rechazada, 4 Aprobada

### Usuarios demo (password: `12345678`)

**Admins:** `rrhh.ana.gomez@talentlink.com`, `rrhh.bruno.lopez@talentlink.com`, `rrhh.carla.martinez@talentlink.com`

**Empresas:** `talento@auroratech.com` (Aurora Tech), `contacto@mateycode.com`, `rrhh@pampafoods.com`, `jobs@andescargo.com`, `empleos@rioplata.com`

**Candidatos:** `luna.perez@mail.com`, `tomas.rojas@mail.com`, `maria.garcia@mail.com`, `juan.sosa@mail.com`, `sofia.diaz@mail.com`

### Reglas de implementación Laravel

- Validar en Form Request; autorizar en Policy.
- Mensajes de éxito: `redirect()->with('ok', ...)`.
- Formularios: `@csrf`, errores con `@error` y `old()`.
- Sidebar: mostrar ítem solo si el usuario tiene permiso.
- No renombrar tablas ni columnas del esquema actual.

---

*Última actualización: agosto 2026*
