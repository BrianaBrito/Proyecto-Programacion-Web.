function inicializarDashboard() {
    const inventario = obtenerColeccion(COLECCIONES.INVENTARIO);
    const proveedores = obtenerColeccion(COLECCIONES.PROVEEDORES);
    const movimientos = obtenerColeccion(COLECCIONES.MOVIMIENTOS);

    const totalProductos = inventario.length;
    actualizarElemento('.dash-card:first-child .dash-num', totalProductos);

    const stockBajo = inventario.filter(item => obtenerStock(item) < 10).length;
    actualizarElemento('.dash-card:nth-child(2) .dash-num', stockBajo);

    const totalProveedores = proveedores.length;
    actualizarElemento('.dash-card:last-child .dash-num', totalProveedores);

    const movimientosOrdenados = movimientos
        .filter(m => m.fecha)
        .sort((a, b) => b.fecha.localeCompare(a.fecha) || (b.id - a.id))
        .slice(0, 3);

    renderizarTablaMovimientos(movimientosOrdenados);
    renderizarActividadReciente(movimientosOrdenados);
}

function obtenerStock(item) {
    return Number(item.stock ?? item.cantidad ?? 0);
}

function renderizarTablaMovimientos(movimientos) {
    const tbody = document.querySelector('.tabla-resumen tbody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (movimientos.length === 0) {
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 4;
        td.textContent = 'Sin movimientos registrados';
        tr.appendChild(td);
        tbody.appendChild(tr);
        return;
    }

    movimientos.forEach(mov => {
        const tipo = (mov.tipo || '').toLowerCase();
        const esEntrada = tipo === 'entrada';

        const tr = document.createElement('tr');

        const tdFecha = document.createElement('td');
        tdFecha.textContent = formatearFecha(mov.fecha);
        tr.appendChild(tdFecha);

        const tdTipo = document.createElement('td');
        const badge = document.createElement('span');
        badge.className = `badge badge-${esEntrada ? 'entrada' : 'salida'}`;
        badge.textContent = esEntrada ? 'Entrada' : 'Salida';
        tdTipo.appendChild(badge);
        tr.appendChild(tdTipo);

        const tdProducto = document.createElement('td');
        tdProducto.textContent = mov.producto || mov.productoNombre || 'N/A';
        tr.appendChild(tdProducto);

        const tdCantidad = document.createElement('td');
        tdCantidad.textContent = `${esEntrada ? '+' : '-'}${mov.cantidad || 0}`;
        tr.appendChild(tdCantidad);

        tbody.appendChild(tr);
    });
}

function renderizarActividadReciente(movimientos) {
    const actividadContainer = document.querySelector('.dash-card.highlight .dash-info');
    if (!actividadContainer) return;

    const titulo = actividadContainer.querySelector('h3');
    actividadContainer.innerHTML = '';
    if (titulo) actividadContainer.appendChild(titulo);

    if (movimientos.length === 0) {
        const p = document.createElement('p');
        p.textContent = 'Sin movimientos recientes';
        actividadContainer.appendChild(p);
        return;
    }

    movimientos.forEach(mov => {
        const tipo = (mov.tipo || '').toLowerCase();
        const esEntrada = tipo === 'entrada';

        const item = document.createElement('div');
        item.className = 'actividad-item';

        const punto = document.createElement('span');
        punto.className = `punto ${esEntrada ? 'azul' : 'rojo'}`;
        item.appendChild(punto);

        const p = document.createElement('p');
        const tipoTexto = esEntrada ? 'Entrada' : 'Salida';
        p.textContent = `${tipoTexto}: ${mov.cantidad || 0} ${mov.producto || mov.productoNombre || ''}`;
        item.appendChild(p);

        actividadContainer.appendChild(item);
    });
}

function actualizarElemento(selector, valor) {
    const elemento = document.querySelector(selector);
    if (elemento) elemento.textContent = valor;
}

function formatearFecha(fechaStr) {
    if (!fechaStr) return '';
    const partes = fechaStr.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return fechaStr;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarDashboard);
} else {
    inicializarDashboard();
}
