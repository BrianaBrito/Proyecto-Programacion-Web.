function textoSeguro(valor) {
    const contenedor = document.createElement('div');
    contenedor.textContent = valor === undefined || valor === null ? '' : String(valor);
    return contenedor.innerHTML;
}
 
function formatoDinero(valor) {
    const numDinero = Number(valor);
    return '$' + (isNaN(numDinero) ? '0.00' : numDinero.toLocaleString('es-MX', {
        minimumFractionDigits: 2, maximumFractionDigits: 2
    }));
}

// Igual que inicializarRegistroTabla (almacenamiento.js) pero contra un endpoint PHP
// real en vez de localStorage. Se usa en las páginas ya migradas a backend.
async function apiFetchJson(url, opciones) {
    const respuesta = await fetch(url, opciones);
    let datos = null;
    try {
        datos = await respuesta.json();
    } catch (error) {
        datos = null;
    }

    if (!respuesta.ok) {
        const mensaje = (datos && datos.error) || `Error ${respuesta.status}`;
        throw new Error(mensaje);
    }

    return datos;
}

function inicializarRegistroTablaApi(opciones) {
    const { endpoint, formSelector, tablaSelector, renderFila } = opciones;

    const tabla = document.querySelector(tablaSelector);
    if (!tabla || !tabla.tBodies[0]) return;
    const tbody = tabla.tBodies[0];
    const form = formSelector ? document.querySelector(formSelector) : null;
    const botonGuardar = form ? form.querySelector('button[type="submit"]') : null;
    const textoBotonOriginal = botonGuardar ? botonGuardar.textContent : '';

    let registros = [];
    let enEdicion = null;

    function mostrarErrorForm(mensaje) {
        if (!form) return;
        let error = form.querySelector('.form-error');
        if (!error) {
            error = document.createElement('p');
            error.className = 'form-error';
            error.style.color = '#b91c1c';
            form.appendChild(error);
        }
        error.textContent = mensaje;
    }

    function limpiarErrorForm() {
        if (!form) return;
        const error = form.querySelector('.form-error');
        if (error) error.textContent = '';
    }

    function pintarTabla() {
        tbody.innerHTML = registros.map(renderFila).join('');
    }

    async function cargarDatos() {
        try {
            registros = await apiFetchJson(endpoint);
            pintarTabla();
        } catch (error) {
            console.error('Error al cargar datos:', error);
            tbody.innerHTML = `<tr><td colspan="99">Error al cargar los datos: ${error.message}</td></tr>`;
        }
    }

    function llenarForm(registro) {
        if (!form) return;
        Object.keys(registro).forEach(campo => {
            const input = form.elements.namedItem(campo);
            if (input) input.value = registro[campo];
        });
    }

    function entrarEdicion(registro) {
        enEdicion = registro.id;
        llenarForm(registro);
        if (botonGuardar) botonGuardar.textContent = 'Actualizar';

        const details = form ? form.closest('details') : null;
        if (details) {
            details.open = true;
            details.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function salirEdicion() {
        enEdicion = null;
        if (form) form.reset();
        if (botonGuardar) botonGuardar.textContent = textoBotonOriginal;
        limpiarErrorForm();
    }

    if (form) {
        form.addEventListener('submit', async evento => {
            evento.preventDefault();
            limpiarErrorForm();

            const datosFormulario = new FormData(form);
            datosFormulario.append('accion', enEdicion !== null ? 'actualizar' : 'crear');
            if (enEdicion !== null) datosFormulario.append('id', enEdicion);

            if (botonGuardar) botonGuardar.disabled = true;
            try {
                await apiFetchJson(endpoint, { method: 'POST', body: datosFormulario });
                salirEdicion();
                await cargarDatos();
            } catch (error) {
                mostrarErrorForm(error.message);
            } finally {
                if (botonGuardar) botonGuardar.disabled = false;
            }
        });
    }

    tbody.addEventListener('click', async evento => {
        const boton = evento.target.closest('button');
        if (!boton) return;

        const fila = boton.closest('tr');
        const id = fila ? fila.dataset.id : null;
        if (id === null || id === undefined) return;

        const accion = boton.textContent.trim().toLowerCase();

        if (accion === 'editar') {
            const registro = registros.find(r => String(r.id) === String(id));
            if (registro) entrarEdicion(registro);
            return;
        }

        if (accion === 'eliminar') {
            const confirmacion = confirm('¿Estás seguro que quieres eliminar este registro?');
            if (!confirmacion) return;

            boton.disabled = true;
            try {
                await apiFetchJson(endpoint, {
                    method: 'POST',
                    body: new URLSearchParams({ accion: 'eliminar', id })
                });
                if (enEdicion !== null && String(enEdicion) === String(id)) salirEdicion();
                await cargarDatos();
            } catch (error) {
                alert(error.message);
                boton.disabled = false;
            }
        }
    });

    cargarDatos();

    return { cargarDatos };
}
