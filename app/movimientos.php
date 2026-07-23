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
    <title>Movimientos | ADVITIUM</title>
    <link rel="shortcut icon" href="../assets/img/icons/movimientosicono.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/movimientos.css">
</head>
<body>

    <div id="navbar-container"></div>

    <main class="main-container">
        <div class="card-panel">
            <h1 class="titulo-pagina">Historial de movimientos</h1>
            
            <h2>Filtrar movimientos</h2>

            <div class="filter-container">
                <select name="por-fecha">
                    <option value="" disabled selected hidden> Por fecha </option>
                    <option value="antique">Mas antiguos</option>
                    <option value="recent">Mas recientes</option>
                </select>

                <select name="tipo" id="type">
                    <option value="" disabled selected hidden> Tipo </option>
                    <option value="entrada">Entrada</option>
                    <option value="salida">Salida</option>
                </select>

                <select name="responsable" id="responsable">
                    <option value="" disabled selected hidden> Responsable </option>
                </select>
            </div>


            <div class = "tabla-movimientos">
                <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>
                        <th>Responsable</th>
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
    <script src="../assets/scripts/validacion.js"></script>
    <script src="../assets/scripts/api-crud.js"></script>
    <script src="../assets/scripts/movimientos-filtros.js"></script>

    <script>
        function renderFilaMovimiento(registro) {
            const claseBadge = registro.tipo === 'Entrada' ? 'entrada' : 'salida';
            return `
                <tr>
                    <td>${registro.id}</td>
                    <td>${textoSeguro(registro.fecha)}</td>
                    <td>${textoSeguro(registro.producto)}</td>
                    <td><span class="badge ${claseBadge}">${textoSeguro(registro.tipo)}</span></td>
                    <td>${registro.cantidad}</td>
                    <td>${textoSeguro(registro.motivo)}</td>
                    <td>${textoSeguro(registro.responsable)}</td>
                </tr>`;
        }

        async function cargarMovimientos() {
            const tbody = document.querySelector('.tabla-movimientos table tbody');
            try {
                const datos = await apiFetchJson('../assets/php/movimientos_api.php');
                tbody.innerHTML = datos.length
                    ? datos.map(renderFilaMovimiento).join('')
                    : '<tr><td colspan="7">No hay movimientos registrados.</td></tr>';

                const selectResponsable = document.getElementById('responsable');
                const responsables = [...new Set(datos.map(d => d.responsable).filter(Boolean))];
                const opciones = responsables
                    .map(r => `<option value="${textoSeguro(r)}">${textoSeguro(r)}</option>`)
                    .join('');
                selectResponsable.innerHTML = `<option value="" disabled selected hidden>Responsable</option>${opciones}`;
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="7">Error al cargar movimientos: ${error.message}</td></tr>`;
            }
        }

        cargarMovimientos();
    </script>

    <script>
        fetch('navbar.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('navbar-container').innerHTML= data;
            const links = document.querySelectorAll('nav ul li a');
            links.forEach(link => {
                if(link.textContent.trim() === 'Movimientos') {
                    link.classList.add('active-link');
                }
            });
            inicializarMenuHamburguesa();
        })
        .catch(error => console.error("Error al cargar el nav", error));
    </script>
    
</body>
</html>