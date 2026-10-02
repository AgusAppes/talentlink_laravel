import './bootstrap';
import bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((elemento) => new bootstrap.Tooltip(elemento));
