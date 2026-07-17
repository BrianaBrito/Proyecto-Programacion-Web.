const COLECCIONES = {
    INVENTARIO: 'inventario',
    CLIENTES: 'clientes',
    PROVEEDORES: 'proveedores',
    CATEGORIAS: 'categorias',
    MOVIMIENTOS: 'movimientos'
};

const ALMACENAMIENTO_PREFIJO = 'advitium_';

function obtenerClaveAlmacenamiento(nombreColeccion) {
    return `${ALMACENAMIENTO_PREFIJO}${nombreColeccion}`;
}

function obtenerColeccion(nombreColeccion) {
    const datos = localStorage.getItem(obtenerClaveAlmacenamiento(nombreColeccion));
    if (!datos) return [];

    try {
        const registros = JSON.parse(datos);
        return Array.isArray(registros) ? registros : [];
    } catch (error) {
        console.error(`Error al leer la colección "${nombreColeccion}" de localStorage`, error);
        return [];
    }
}

function guardarColeccion(nombreColeccion, registros) {
    localStorage.setItem(obtenerClaveAlmacenamiento(nombreColeccion), JSON.stringify(registros));
}

function generarIdRegistro(registros) {
    const idsNumericos = registros
        .map(registro => Number(registro.id))
        .filter(id => !isNaN(id));
    return idsNumericos.length ? Math.max(...idsNumericos) + 1 : 1;
}

function agregarRegistro(nombreColeccion, registro) {
    const registros = obtenerColeccion(nombreColeccion);
    const nuevoRegistro = { ...registro, id: registro.id ?? generarIdRegistro(registros) };

    registros.push(nuevoRegistro);
    guardarColeccion(nombreColeccion, registros);

    return nuevoRegistro;
}

function actualizarRegistro(nombreColeccion, id, cambios) {
    const registros = obtenerColeccion(nombreColeccion);
    const indice = registros.findIndex(registro => String(registro.id) === String(id));
    if (indice === -1) return null;

    const registroActualizado = { ...registros[indice], ...cambios, id: registros[indice].id };
    registros[indice] = registroActualizado;
    guardarColeccion(nombreColeccion, registros);

    return registroActualizado;
}

function eliminarRegistro(nombreColeccion, id) {
    const registros = obtenerColeccion(nombreColeccion);
    const registrosFiltrados = registros.filter(registro => String(registro.id) !== String(id));
    const seElimino = registrosFiltrados.length !== registros.length;

    if (seElimino) {
        guardarColeccion(nombreColeccion, registrosFiltrados);
    }

    return seElimino;
}
