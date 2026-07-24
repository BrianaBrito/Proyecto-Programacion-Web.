<?php
require_once '../assets/php/roles.php';
verificarAutenticacion();
verificarAcceso(basename(__FILE__));
$puedeRegistrarMovimiento = usuarioPuedeRegistrarMovimientos();; //gestionamos permisos
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Movimientos financieros | ADVITIUM</title>
    <link rel="shortcut icon" href="../assets/img/icons/movimientosicono.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/movimientos.css">
</head>
<body>

    <div id="navbar-container"></div>

    <main class="main-container">
        <div class="card-panel">
            <h1 class="titulo-pagina">Movimientos financieros</h1>
            
            <h2>Filtrar movimientos</h2>

            <div class="filter-container">
                <select name="Por-cuenta" id="Por-cuenta">
                    <option value="" disabled selected hidden> Tipo de entidad </option>
                    <option value="proveedor">Proveedores</option>
                    <option value="cliente">Clientes</option>
                </select>
            </div>

            <?php if (!$puedeRegistrarMovimiento): ?>
                <div>
                    <h2>Modo auditor. No puedes registrar movimientos financieros, solo consultarlos. <h2>
                </div>
            <?php endif; ?>

            <div id = "tabla-proveedores" class = "tabla-movimientos-cuenta">
                <?php if ($puedeRegistrarMovimiento): ?>
                <details class="registrar-producto-details">
                    <summary><h2>Registrar movimiento</h2></summary>
                    <form id="form-movimiento-proveedor" action="">
                        <div>
                            <label for="id-proveedor-movimiento">Proveedor: </label>
                            <select name="id_entidad" id="id-proveedor-movimiento" required>
                                <option value="" disabled selected hidden>Elige un proveedor</option>
                            </select>
                        </div>
                        <div>
                            <label for="tipo-movimiento-proveedor">Tipo: </label>
                            <select name="tipo_movimiento" id="tipo-movimiento-proveedor" required>
                                <option value="" disabled selected hidden>Elige el tipo</option>
                                <option value="Cargo">Cargo</option>
                                <option value="Pago">Pago</option>
                            </select>
                        </div>
                        <div>
                            <label for="monto-proveedor">Monto: </label>
                            <input type="number" name="monto" id="monto-proveedor" required min="0.01" max="99999999.99" step="0.01">
                        </div>
                        <div>
                            <label for="motivo-proveedor">Motivo: </label>
                            <input type="text" name="motivo" id="motivo-proveedor" required minlength="3" maxlength="200">
                        </div>
                        <button type="submit">Guardar</button>
                    </form>
                </details>
                <?php endif; ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ID Proveedor</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>

            <div id = "tabla-clientes" class="tabla-movimientos-cuenta oculto">
                <?php if ($puedeRegistrarMovimiento): ?>
                <details class="registrar-producto-details">
                    <summary><h2>Registrar movimiento</h2></summary>
                    <form id="form-movimiento-cliente" action="">
                        <div>
                            <label for="id-cliente-movimiento">Cliente: </label>
                            <select name="id_entidad" id="id-cliente-movimiento" required>
                                <option value="" disabled selected hidden>Elige un cliente</option>
                            </select>
                        </div>
                        <div>
                            <label for="tipo-movimiento-cliente">Tipo: </label>
                            <select name="tipo_movimiento" id="tipo-movimiento-cliente" required>
                                <option value="" disabled selected hidden>Elige el tipo</option>
                                <option value="Cargo">Cargo</option>
                                <option value="Pago">Pago</option>
                            </select>
                        </div>
                        <div>
                            <label for="monto-cliente">Monto: </label>
                            <input type="number" name="monto" id="monto-cliente" required min="0.01" max="99999999.99" step="0.01">
                        </div>
                        <div>
                            <label for="motivo-cliente">Motivo: </label>
                            <input type="text" name="motivo" id="motivo-cliente" required minlength="3" maxlength="200">
                        </div>
                        <button type="submit">Guardar</button>
                    </form>
                </details>
                <?php endif; ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ID Cliente</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>   
        </div>
    </main>

    <script src="../assets/scripts/navbar-menu.js"></script>
    <script src="../assets/scripts/tabla-ordenable.js"></script>
    <script src="../assets/scripts/busqueda.js"></script>
    <script src="../assets/scripts/api-crud.js"></script>
    <script src="../assets/scripts/validacion.js"></script>

    <script>
        document.getElementById('Por-cuenta').addEventListener('change', evento => {
            const esProveedor = evento.target.value === 'proveedor';
            document.getElementById('tabla-proveedores').classList.toggle('oculto', !esProveedor);
            document.getElementById('tabla-clientes').classList.toggle('oculto', esProveedor);
        });
    </script>

    <script>
        function renderFilaProveedor(registro) {
            const claseBadge = registro.tipo === 'Cargo' ? 'cargo' : 'pago';
            return `
                <tr data-id="${registro.id}">
                    <td>${registro.id}</td>
                    <td>${registro.id_proveedor}</td>
                    <td><span class="badge ${claseBadge}">${registro.tipo}</span></td>
                    <td>${formatoDinero(registro.monto)}</td>
                    <td>${textoSeguro(registro.fecha)}</td>
                    <td>${textoSeguro(registro.motivo)}</td>
                </tr>`;
        }

        function renderFilaCliente(registro) {
            const claseBadge = registro.tipo === 'Cargo' ? 'cargo' : 'pago';
            return `
                <tr data-id="${registro.id}">
                    <td>${registro.id}</td>
                    <td>${registro.id_cliente}</td>
                    <td><span class="badge ${claseBadge}">${registro.tipo}</span></td>
                    <td>${formatoDinero(registro.monto)}</td>
                    <td>${textoSeguro(registro.fecha)}</td>
                    <td>${textoSeguro(registro.motivo)}</td>
                </tr>`;
        }

        async function cargarCuentas(tipo, tablaSelector, renderFila) {
            const tbody = document.querySelector(`${tablaSelector} tbody`);
            try {
                const datos = await apiFetchJson(`../assets/php/cuentas_api.php?tipo=${tipo}`);
                tbody.innerHTML = datos.length
                    ? datos.map(renderFila).join('')
                    : '<tr><td colspan="6">No hay movimientos registrados.</td></tr>';
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="6">Error al cargar datos: ${error.message}</td></tr>`;
            }
        }

        cargarCuentas('proveedor', '#tabla-proveedores table', renderFilaProveedor);
        cargarCuentas('cliente', '#tabla-clientes table', renderFilaCliente);

        poblarSelectEntidades(document.getElementById('id-proveedor-movimiento'), '../assets/php/proveedores_api.php', r => r.estado === 'Activo');
        poblarSelectEntidades(document.getElementById('id-cliente-movimiento'), '../assets/php/clientes_api.php', r => r.estado === 'Activo');

        function inicializarRegistroMovimiento({ tipo, formSelector, onGuardado }) {
            const form = document.querySelector(formSelector);
            if (!form) return;
            const botonGuardar = form.querySelector('button[type="submit"]');
            const textoBotonOriginal = botonGuardar ? botonGuardar.textContent : '';

            form.addEventListener('submit', async evento => {
                evento.preventDefault();

                const datosFormulario = new FormData(form);
                datosFormulario.append('accion', 'crear');
                datosFormulario.append('tipo', tipo);

                if (botonGuardar) { botonGuardar.disabled = true; botonGuardar.textContent = 'Guardando...'; }
                try {
                    await apiFetchJson('../assets/php/cuentas_api.php', { method: 'POST', body: datosFormulario });
                    form.reset();
                    if (onGuardado) await onGuardado();
                } catch (error) {
                    alert(error.message);
                } finally {
                    if (botonGuardar) { botonGuardar.disabled = false; botonGuardar.textContent = textoBotonOriginal; }
                }
            });
        }

        inicializarRegistroMovimiento({
            tipo: 'proveedor',
            formSelector: '#form-movimiento-proveedor',
            onGuardado: () => cargarCuentas('proveedor', '#tabla-proveedores table', renderFilaProveedor)
        });
        inicializarRegistroMovimiento({
            tipo: 'cliente',
            formSelector: '#form-movimiento-cliente',
            onGuardado: () => cargarCuentas('cliente', '#tabla-clientes table', renderFilaCliente)
        });
    </script>

    <script>
        fetch('navbar.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('navbar-container').innerHTML= data;
            const links = document.querySelectorAll('nav ul li a');
            links.forEach(link => {
                if(link.textContent.trim() === 'Movimientos financieros') {
                    link.classList.add('active-link');
                }
            });
            inicializarMenuHamburguesa();
        })
        .catch(error => console.error("Error al cargar el nav", error));
    </script>
    
</body>
</html>