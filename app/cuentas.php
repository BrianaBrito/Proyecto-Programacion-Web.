<?php
require_once '../assets/php/roles.php';
verificarAutenticacion();
verificarAcceso(basename(__FILE__));
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


            <div id = "tabla-proveedores" class = "tabla-movimientos-cuenta">
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