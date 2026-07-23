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
    <script src="../assets/scripts/almacenamiento.js"></script>
    <script src="../assets/scripts/validacion.js"></script>
    <script src="../assets/scripts/actualizacion-tablas.js"></script>

    <script>
        document.getElementById('Por-cuenta').addEventListener('change', evento => {
            const esProveedor = evento.target.value === 'proveedor';
            document.getElementById('tabla-proveedores').classList.toggle('oculto', !esProveedor);
            document.getElementById('tabla-clientes').classList.toggle('oculto', esProveedor);
        });
    </script>
    
    <script>
        const CUENTAS_PROVEEDORES_INICIALES = [
            { id: 1, id_proveedor: 1, tipo: 'Cargo', monto: 1300, fecha: '2026-07-20', motivo: 'Compra de 100 utiles' },
            { id: 2, id_proveedor: 2, tipo: 'Pago', monto: 1500, fecha: '2026-07-21', motivo: 'Pago de mercancia' }
        ];

        const CUENTAS_CLIENTES_INICIALES = [
            { id: 1, id_cliente: 301, tipo: 'Cargo', monto: 1300, fecha: '2026-07-20', motivo: 'Compra de 100 utiles' },
            { id: 2, id_cliente: 302, tipo: 'Pago', monto: 1500, fecha: '2026-07-21', motivo: 'Pago de mercancia' },
            { id: 3, id_cliente: 303, tipo: 'Cargo', monto: 1700, fecha: '2026-07-22', motivo: 'Compra de 120 utiles' }
        ];

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

        inicializarRegistroTabla({
            coleccion: COLECCIONES.MOVIMIENTOS_PROVEEDOR,
            tablaSelector: '#tabla-proveedores table',
            datosDefecto: CUENTAS_PROVEEDORES_INICIALES,
            renderFila: renderFilaProveedor
        });

        inicializarRegistroTabla({
            coleccion: COLECCIONES.MOVIMIENTOS_CLIENTE,
            tablaSelector: '#tabla-clientes table',
            datosDefecto: CUENTAS_CLIENTES_INICIALES,
            renderFila: renderFilaCliente
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