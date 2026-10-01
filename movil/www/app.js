const SERVIDOR = 'https://talentlink-laravel.onrender.com';

const login = document.querySelector('#login');
const feed = document.querySelector('#feed');
const formLogin = document.querySelector('#form-login');
const avisoLogin = document.querySelector('#login-aviso');
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
        localStorage.setItem('token', datos.token);
        localStorage.setItem('nombre', (datos.nombre + ' ' + datos.apellido).trim());
        mostrarFeed();
        cargarOfertas();
    } catch (error) {
        avisoLogin.textContent = error.message;
    }
});

document.querySelector('#salir').addEventListener('click', () => {
    localStorage.removeItem('token');
    localStorage.removeItem('nombre');
    feed.hidden = true;
    login.hidden = false;
});

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

function mostrarFeed() {
    login.hidden = true;
    feed.hidden = false;
    document.querySelector('#saludo').textContent = localStorage.getItem('nombre') || 'Ofertas';
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

function dibujarOfertas(ofertas) {
    listaOfertas.innerHTML = '';

    if (!ofertas.length) {
        listaOfertas.innerHTML = '<p>No hay ofertas guardadas.</p>';
        return;
    }

    ofertas.forEach((oferta) => {
        const tarjeta = document.createElement('article');
        const detalle = [oferta.empresa, oferta.modalidad, oferta.ciudad].filter(Boolean).join(' · ');
        tarjeta.innerHTML = '<h2></h2><p></p>';
        tarjeta.querySelector('h2').textContent = oferta.puesto;
        tarjeta.querySelector('p').textContent = detalle;

        if (oferta.ya_postulada) {
            tarjeta.insertAdjacentHTML('beforeend', '<p>Ya te postulaste</p>');
        } else if (oferta.requiere_cv) {
            tarjeta.insertAdjacentHTML('beforeend', '<p>Esta oferta pide CV. Postulate desde la web.</p>');
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
        listaPendientes.innerHTML = '<p>Todavía no guardaste postulaciones.</p>';
        return;
    }

    pendientes.forEach((item) => {
        const tarjeta = document.createElement('article');
        tarjeta.innerHTML = '<h2></h2><p></p>';
        tarjeta.querySelector('h2').textContent = item.puesto;
        tarjeta.querySelector('p').textContent = item.estado === 'enviado' ? 'Enviada al servidor' : 'Pendiente de envío';

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
