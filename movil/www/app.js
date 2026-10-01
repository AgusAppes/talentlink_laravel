const SERVIDOR = 'https://talentlink-laravel.onrender.com';

const login = document.querySelector('#login');
const registro = document.querySelector('#registro');
const feed = document.querySelector('#feed');
const formLogin = document.querySelector('#form-login');
const avisoLogin = document.querySelector('#login-aviso');
const avisoRegistro = document.querySelector('#registro-aviso');
const avisoFeed = document.querySelector('#feed-aviso');
const listaOfertas = document.querySelector('#ofertas');
const listaPendientes = document.querySelector('#pendientes');
const capaCarga = document.querySelector('#cargando');
let cargas = 0;

if (localStorage.getItem('token')) {
    mostrarFeed();
    cargarOfertas();
}

formLogin.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    avisoLogin.textContent = '';

    try {
        const datos = await pedir('/api/movil/login', {
            method: 'POST',
            espera: 'Iniciando sesión...',
            body: {
                correo: document.querySelector('#correo').value,
                password: document.querySelector('#password').value,
            },
        });
        entrar(datos);
    } catch (error) {
        avisoLogin.textContent = error.message;
    }
});

document.querySelector('#ir-registro').addEventListener('click', () => {
    avisoLogin.textContent = '';
    avisoRegistro.textContent = '';
    login.hidden = true;
    registro.hidden = false;
});

document.querySelector('#ir-login').addEventListener('click', () => {
    avisoRegistro.textContent = '';
    registro.hidden = true;
    login.hidden = false;
});

document.querySelector('#form-registro').addEventListener('submit', async (evento) => {
    evento.preventDefault();
    avisoRegistro.textContent = '';

    try {
        const datos = await pedir('/api/movil/registro', {
            method: 'POST',
            espera: 'Creando cuenta...',
            body: {
                nombre: document.querySelector('#nombre').value,
                apellido: document.querySelector('#apellido').value,
                correo: document.querySelector('#correo-registro').value,
                password: document.querySelector('#password-registro').value,
                password_confirmation: document.querySelector('#password-confirmacion').value,
            },
        });
        entrar(datos);
    } catch (error) {
        avisoRegistro.textContent = error.message;
    }
});

document.querySelector('#salir').addEventListener('click', () => {
    localStorage.removeItem('token');
    localStorage.removeItem('nombre');
    mostrarVista('ofertas');
    feed.hidden = true;
    registro.hidden = true;
    login.hidden = false;
});

document.querySelector('#ir-ofertas').addEventListener('click', () => mostrarVista('ofertas'));
document.querySelector('#ir-postulaciones').addEventListener('click', () => mostrarVista('postulaciones'));

// Esta función pide las ofertas y las guarda en el teléfono
// En terminos tecnicos, cuando se abre el feed, se ejecuta esta función
// y hace un GET asincrónico a /api/movil/ofertas
async function cargarOfertas() {
    avisoFeed.textContent = '';

    try {
        const datos = await pedir('/api/movil/ofertas', { espera: 'Buscando ofertas...' });
        localStorage.setItem('ofertas', JSON.stringify(datos.ofertas));
        datos.ofertas.forEach((oferta) => {
            if (oferta.ya_postulada) {
                marcarPendiente(oferta, 'enviado');
            }
        });
        avisoFeed.textContent = '';
        dibujarOfertas(datos.ofertas);
    } catch (error) {
        const guardadas = JSON.parse(localStorage.getItem('ofertas') || '[]');
        avisoFeed.textContent = guardadas.length
            ? 'Sin conexión. Mostrando las ofertas guardadas en el teléfono.'
            : error.message;
        dibujarOfertas(guardadas);
    }

    dibujarPendientes();
}

// Esta función guarda la postulación en el teléfono y después la envía
// En terminos tecnicos, cuando se toca Postularme, se ejecuta esta función
// y hace un POST asincrónico a /api/movil/ofertas/{id}/postular
async function postular(oferta) {
    marcarPendiente(oferta, 'pendiente');
    dibujarPendientes();
    avisoFeed.textContent = 'Guardado en el teléfono. Enviando...';

    try {
        const datos = await pedir('/api/movil/ofertas/' + oferta.id + '/postular', {
            method: 'POST',
            espera: 'Enviando postulación...',
        });
        marcarPendiente(oferta, 'enviado');
        marcarOfertaEnviada(oferta.id);
        avisoFeed.textContent = datos.mensaje;
    } catch (error) {
        avisoFeed.textContent = error.message + ' Quedó guardada en el teléfono.';
    }

    dibujarOfertas(JSON.parse(localStorage.getItem('ofertas') || '[]'));
    dibujarPendientes();
}

async function pedir(ruta, opciones = {}) {
    mostrarCarga(opciones.espera || 'Cargando...');
    const token = localStorage.getItem('token');

    try {
        const respuesta = await fetch(SERVIDOR + ruta, {
            method: opciones.method || 'GET',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(token ? { Authorization: 'Bearer ' + token } : {}),
            },
            body: opciones.body ? JSON.stringify(opciones.body) : undefined,
        });
        const datos = await respuesta.json().catch(() => ({}));

        if (!respuesta.ok || datos.ok === false) {
            const mensaje = datos.mensaje
                || (datos.errors && Object.values(datos.errors)[0][0])
                || 'No se pudo completar.';
            throw new Error(mensaje);
        }

        return datos;
    } finally {
        ocultarCarga();
    }
}

function mostrarCarga(texto) {
    cargas += 1;
    capaCarga.querySelector('p').textContent = texto;
    capaCarga.hidden = false;
}

function ocultarCarga() {
    cargas = Math.max(0, cargas - 1);

    if (cargas === 0) {
        capaCarga.hidden = true;
    }
}

function entrar(datos) {
    localStorage.setItem('token', datos.token);
    localStorage.setItem('nombre', (datos.nombre + ' ' + datos.apellido).trim());
    registro.hidden = true;
    mostrarFeed();
    cargarOfertas();
}

function mostrarFeed() {
    login.hidden = true;
    feed.hidden = false;
    mostrarVista('ofertas');
}

function mostrarVista(vista) {
    const esOfertas = vista === 'ofertas';
    document.querySelector('#vista-ofertas').hidden = !esOfertas;
    document.querySelector('#vista-postulaciones').hidden = esOfertas;
    document.querySelector('#ir-ofertas').classList.toggle('activo', esOfertas);
    document.querySelector('#ir-postulaciones').classList.toggle('activo', !esOfertas);
    document.querySelector('#saludo').textContent = esOfertas
        ? (localStorage.getItem('nombre') || 'Ofertas')
        : 'Postulaciones';
}

function marcarPendiente(oferta, estado) {
    const pendientes = JSON.parse(localStorage.getItem('pendientes') || '[]')
        .filter((item) => item.id !== oferta.id);
    pendientes.push({
        id: oferta.id,
        puesto: oferta.puesto,
        empresa: oferta.empresa,
        estado: estado,
    });
    localStorage.setItem('pendientes', JSON.stringify(pendientes));
}

function marcarOfertaEnviada(id) {
    const ofertas = JSON.parse(localStorage.getItem('ofertas') || '[]');
    const oferta = ofertas.find((item) => item.id === id);

    if (oferta) {
        oferta.ya_postulada = true;
        localStorage.setItem('ofertas', JSON.stringify(ofertas));
    }
}

function agregar(padre, etiqueta, clase, texto) {
    const nodo = document.createElement(etiqueta);
    if (clase) {
        nodo.className = clase;
    }
    nodo.textContent = texto;
    padre.append(nodo);
}

function dibujarOfertas(ofertas) {
    listaOfertas.innerHTML = '';

    if (!ofertas.length) {
        listaOfertas.innerHTML = '<p class="vacio">No hay ofertas guardadas.</p>';
        return;
    }

    ofertas.forEach((oferta) => {
        const tarjeta = document.createElement('article');
        agregar(tarjeta, 'p', 'empresa', oferta.empresa || 'Empresa');
        agregar(tarjeta, 'h2', '', oferta.puesto || 'Oferta');

        const meta = document.createElement('div');
        meta.className = 'meta';
        [oferta.modalidad, oferta.ciudad, oferta.vacantes ? oferta.vacantes + ' vacantes' : '']
            .filter(Boolean)
            .forEach((dato) => agregar(meta, 'span', '', dato));

        if (meta.childElementCount) {
            tarjeta.append(meta);
        }

        if (oferta.descripcion) {
            agregar(tarjeta, 'p', 'descripcion', oferta.descripcion.slice(0, 180));
        }

        if (oferta.ya_postulada) {
            agregar(tarjeta, 'p', 'estado', 'Ya te postulaste');
        } else if (oferta.requiere_cv) {
            agregar(tarjeta, 'p', 'nota', 'Esta oferta pide CV. Postulate desde la web.');
        } else {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.textContent = 'Postularme';
            boton.addEventListener('click', () => postular(oferta));
            tarjeta.append(boton);
        }

        listaOfertas.append(tarjeta);
    });
}

function dibujarPendientes() {
    const pendientes = JSON.parse(localStorage.getItem('pendientes') || '[]');
    listaPendientes.innerHTML = '';

    if (!pendientes.length) {
        listaPendientes.innerHTML = '<p class="vacio">Todavía no guardaste postulaciones.</p>';
        return;
    }

    pendientes.forEach((item) => {
        const tarjeta = document.createElement('article');
        agregar(tarjeta, 'h2', '', item.puesto || 'Postulación');
        agregar(tarjeta, 'p', item.estado === 'enviado' ? 'estado' : 'nota', item.estado === 'enviado' ? 'Enviada al servidor' : 'Pendiente de envío');

        if (item.estado === 'pendiente') {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.textContent = 'Reintentar';
            boton.addEventListener('click', () => postular(item));
            tarjeta.append(boton);
        }

        listaPendientes.append(tarjeta);
    });
}
