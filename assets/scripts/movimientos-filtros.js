
function inicializarFiltrosMovimientos() {

    // primero buscamos la tabla, si no existe (por ejemplo en otra pagina) simplemente no hacemos nada, para no tirar error en consola

    const tabla = document.querySelector('.tabla-movimientos table');
    if (!tabla) return;

    const tbody = tabla.tBodies[0];
    if (!tbody) return;

    // los 3 selects del filtro, tal cual estan en el html

    const selectFecha = document.querySelector('select[name="por-fecha"]');
    const selectTipo = document.querySelector('select[name="tipo"]');
    const selectResponsable = document.querySelector('select[name="responsable"]');

    
    console.log('[movimientos-filtros] inicializando filtros...');
    console.log('[movimientos-filtros] select fecha encontrado:', !!selectFecha);
    console.log('[movimientos-filtros] select tipo encontrado:', !!selectTipo);
    console.log('[movimientos-filtros] select responsable encontrado:', !!selectResponsable);

    
    // esta funcion oculta o muestra las filas dependiendo de lo que el
    // usuario haya elegido en tipo y responsable (los dos filtros se
    // aplican juntos, no uno u otro)
    function aplicarFiltros() {
        const tipoSeleccionado = selectTipo ? selectTipo.value.toLowerCase() : '';
        const responsableSeleccionado = selectResponsable ? selectResponsable.value : '';

        // debugeo rapido para revisar en consola que se esta leyendo bien lo que
        // el usuario selecciono en los combos
        console.log('[movimientos-filtros] aplicando filtros -> tipo:', tipoSeleccionado || '(sin filtro)', '| responsable:', responsableSeleccionado || '(sin filtro)');

        let contadorVisibles = 0;

        tbody.querySelectorAll('tr').forEach(fila => {

            // aguas aqui, la columna 3 es Tipo y la 6 es Responsable

            const tipoFila = fila.children[3] ? fila.children[3].textContent.trim().toLowerCase() : '';
            const responsableFila = fila.children[6] ? fila.children[6].textContent.trim() : '';

            const coincideTipo = !tipoSeleccionado || tipoFila === tipoSeleccionado;
            const coincideResponsable = !responsableSeleccionado || responsableFila === responsableSeleccionado;

            fila.hidden = !(coincideTipo && coincideResponsable);

            if (!fila.hidden) contadorVisibles++;
        });

        console.log('[movimientos-filtros] filas visibles despues de filtrar:', contadorVisibles);
    }

    // esta otra funcion no filtra nada, solo reordena las filas que ya
    // estan en la tabla segun la fecha (mas antiguos o mas recientes)

    function aplicarOrden() {
        const orden = selectFecha ? selectFecha.value : '';

        // si todavia no eligen nada de fecha, dejamos la tabla como esta

        if (!orden) {
        
            console.log('[movimientos-filtros] sin orden seleccionado, se deja el orden original');
            return;
        
        }

        const filas = Array.from(tbody.querySelectorAll('tr'));

        filas.sort((filaA, filaB) => {

            const fechaA = filaA.children[1] ? filaA.children[1].textContent.trim() : '';
            const fechaB = filaB.children[1] ? filaB.children[1].textContent.trim() : '';
            // las fechas vienen en formato AAAA-MM-DD, por eso se pueden
            // comparar como texto y el orden sale correcto igual
            return orden === 'recent' ? fechaB.localeCompare(fechaA) : fechaA.localeCompare(fechaB);
      
        });

        // reinsertamos las filas ya ordenadas, apendice por apendice
        // (appendChild mueve el nodo si ya existe, no lo duplica)
        filas.forEach(fila => tbody.appendChild(fila));

        console.log('[movimientos-filtros] tabla ordenada por fecha, modo:', orden);
    }

    // funcion central que se llama cada vez que cambia cualquier select
    function actualizarTabla() {
        console.log('[movimientos-filtros] cambio detectado en algun filtro, actualizando tabla...');
        aplicarOrden();
        aplicarFiltros();
    }

    // le ponemos el listener a los 3 selects, si alguno no existe en el
    // html por algun motivo, se ignora (por eso el if adentro del forEach)
    [selectFecha, selectTipo, selectResponsable].forEach(select => {
        if (select) select.addEventListener('change', actualizarTabla);
    });
}

// esto es para que el script funcione sin importar si se carga antes o
// despues de que el html ya termino de cargar (por si acaso)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarFiltrosMovimientos);
} else {
    inicializarFiltrosMovimientos();
}
