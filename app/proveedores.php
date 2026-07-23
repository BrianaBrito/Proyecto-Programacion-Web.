<?php
require_once '../assets/php/roles.php';
verificarAutenticacion();
verificarAcceso(basename(__FILE__));
$puedeEscribir = usuarioPuedeEscribir();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores | ADVITIUM</title>
    <link rel="shortcut icon" href="../assets/img/icons/proovedoresicono.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/inventario.css">
</head>
<body>
    
    <div id="navbar-container"></div>

    <main class="main-container">
        <div class="card-panel">
            <h1 class="titulo-pagina">Proveedores</h1>

            <?php if (!$puedeEscribir): ?>
                <div style="background: #fef9c3; padding: 8px 16px; border-radius: 6px; margin-bottom: 15px; color: #854d0e;">
                    Modo auditor. No puedes agregar, editar o eliminar proveedores.
                </div>
            <?php endif; ?>

            <?php if ($puedeEscribir): ?>
            <details class="registrar-producto-details">
                <summary><h2>Registrar proveedor</h2></summary>
                <form action="">
                    <div>
                        <label for="nombre">Nombre: </label>
                        <input type="text" name="nombre" id="nombre" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.\&\-]{3,30}$" data-mensaje-error="Debe tener entre 3 y 30 caracteres.">
                    </div>
                    <div>
                        <label for="contacto">Contacto: </label>
                        <input type="text" name="contacto" id="contacto" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,30}$" data-mensaje-error="Debe tener entre 3 y 30 caracteres, solo letras y espacios.">
                    </div>
                    <div>
                        <label for="telefono">Telefono: </label>
                        <input type="text" name="telefono" id="telefono" required pattern="^\d{10}$" data-mensaje-error="Debe contener exactamente 10 dígitos numéricos.">
                    </div>
                    <div>
                        <label for="email">Email: </label>
                        <input type="text" name="email" id="email" required pattern="^[A-Za-z0-9._%\+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$" data-mensaje-error="Ingresa un correo electrónico válido (ejemplo@dominio.com).">
                    </div>
                    <div>
                        <label for="direccion">Direccion: </label>
                        <input type="text" name="direccion" id="direccion" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,#\/\-]{10,120}$" data-mensaje-error="Debe tener entre 10 y 120 caracteres.">
                    </div>
                    <button type="submit">Guardar</button>
                </form>
            </details>
            <?php endif; ?>

            <h2>Lista de proveedores</h2>

            <div class="search-container">
                <button type="submit" id="search-button"></button>
                <input type="text" name="buscar" id="busqueda" placeholder="Buscar proveedor...">
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Telefono</th>
                        <th>Email</th>
                        <th>Direccion</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>
    </main>

    <script src="../assets/scripts/navbar-menu.js"></script>
    <script src="../assets/scripts/tabla-ordenable.js"></script>
    <script src="../assets/scripts/busqueda.js"></script>
    <script src="../assets/scripts/api-crud.js"></script>
    <script src="../assets/scripts/validacion.js"></script>

        <script>
        const puedeEscribir = <?= json_encode($puedeEscribir) ?>;

        function renderFilaProveedor(registro) {
            const claseBadge = registro.estado === 'Activo' ? 'activo' : 'inactivo';
            const acciones = puedeEscribir
                ? `<button>Editar</button><button>Eliminar</button>`
                : `<span style="color:#999; font-size:0.9rem;">—</span>`;
            return `
                <tr data-id="${registro.id}">
                    <td>${registro.id}</td>
                    <td>${textoSeguro(registro.nombre)}</td>
                    <td>${textoSeguro(registro.contacto)}</td>
                    <td>${textoSeguro(registro.telefono)}</td>
                    <td>${textoSeguro(registro.email)}</td>
                    <td>${textoSeguro(registro.direccion)}</td>
                    <td>${formatoDinero(registro.saldo)}</td>
                    <td><span class="badge ${claseBadge}">${registro.estado}</span></td>
                    <td class="acciones">${acciones}</td>
                </tr>`;
        }

        inicializarRegistroTablaApi({
            endpoint: '../assets/php/proveedores_api.php',
            formSelector: '.registrar-producto-details form',
            tablaSelector: 'main table',
            renderFila: renderFilaProveedor
        });
    </script>

    <script>
        fetch('navbar.php')
        .then(response => response.text())
        .then( data => {
            document.getElementById('navbar-container').innerHTML = data;
            const links = document.querySelectorAll('nav ul li a');
            links.forEach(link => {
                if(link.textContent.trim() === 'Proveedores') {
                    link.classList.add('active-link');
                }
            });
            inicializarMenuHamburguesa();
        })
        .catch(error => console.error("Error al cargar el navbar", error))
    </script>

</body>
</html>
