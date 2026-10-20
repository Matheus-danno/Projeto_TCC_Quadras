// JS do Bootstrap para o site público, empacotado pelo Vite (sem CDN).
// Exposto em window.bootstrap porque scripts inline das views usam
// `new bootstrap.Modal(...)`; módulos rodam antes do DOMContentLoaded.
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;
