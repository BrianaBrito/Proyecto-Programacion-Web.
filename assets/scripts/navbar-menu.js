// Menu hamburguesa del navbar de la app (pantallas < 480px)
function inicializarMenuHamburguesa() {
    const nav = document.querySelector('nav');
    if (!nav || nav.querySelector('.nav-toggle')) return;

    const lista = nav.querySelector('ul');
    if (!lista) return;

    const boton = document.createElement('button');
    boton.type = 'button';
    boton.className = 'nav-toggle';
    boton.setAttribute('aria-label', 'Abrir menú');
    boton.setAttribute('aria-expanded', 'false');
    boton.setAttribute('aria-controls', 'nav-lista');
    boton.innerHTML = '<span></span><span></span><span></span>';

    lista.id = lista.id || 'nav-lista';

    const alternarMenu = (mostrar) => {
        const abierto = typeof mostrar === 'boolean' ? mostrar : !nav.classList.contains('nav-abierto');
        nav.classList.toggle('nav-abierto', abierto);
        boton.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        boton.setAttribute('aria-label', abierto ? 'Cerrar menú' : 'Abrir menú');
    };

    boton.addEventListener('click', () => alternarMenu());

    lista.querySelectorAll('a').forEach(enlace => {
        enlace.addEventListener('click', () => alternarMenu(false));
    });

    nav.insertBefore(boton, lista);
}
