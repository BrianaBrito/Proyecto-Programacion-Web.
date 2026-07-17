// Busqueda instantanea de filas en las tablas de gestion
// (Inventario, Clientes, Proveedores y Categorias).

function normalizarTexto(texto) {
    return texto
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLowerCase()
        .trim();
}

function obtenerIndiceColumnaAcciones(tabla) {
    const filaEncabezado = tabla.tHead && tabla.tHead.rows[0];
    if (!filaEncabezado) return -1;

    return Array.from(filaEncabezado.cells).findIndex(
        th => th.textContent.trim().toLowerCase() === 'acciones'
    );
}

function obtenerTextoBuscable(fila, indiceColumnaAcciones) {
    return Array.from(fila.children)
        .filter((_celda, indice) => indice !== indiceColumnaAcciones)
        .map(celda => celda.textContent)
        .join(' ');
}

function filtrarFilasTabla(tabla, termino, indiceColumnaAcciones) {
    const tbody = tabla.tBodies[0];
    if (!tbody) return;

    const terminoNormalizado = normalizarTexto(termino);

    tbody.querySelectorAll('tr').forEach(fila => {
        const textoFila = normalizarTexto(obtenerTextoBuscable(fila, indiceColumnaAcciones));
        const coincide = terminoNormalizado === '' || textoFila.includes(terminoNormalizado);
        fila.hidden = !coincide;
    });
}

function inicializarBusqueda() {
    const input = document.getElementById('busqueda');
    const tabla = document.querySelector('main table');
    if (!input || !tabla) return;

    const indiceColumnaAcciones = obtenerIndiceColumnaAcciones(tabla);

    input.addEventListener('input', () => {
        filtrarFilasTabla(tabla, input.value, indiceColumnaAcciones);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarBusqueda);
} else {
    inicializarBusqueda();
}
