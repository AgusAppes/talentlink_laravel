import './bootstrap';
import bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';

window.bootstrap = bootstrap;

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((elemento) => new bootstrap.Tooltip(elemento));
