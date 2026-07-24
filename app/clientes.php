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
        <title>Clientes | ADVITIUM </title>
        <link rel="shortcut icon" href="../assets/img/icons/clientesicono.png" type="image/x-icon">
        <link rel="stylesheet" href="../assets/css/clientes.css">
    </head>

    <body>
        <div id="navbar-container"></div>
        
        <main class="main-container">
            <div class="card-panel">
                <h1 class="titulo-pagina"> Clientes</h1>

                <?php if (!$puedeEscribir): ?>
                    <div style="background: #fef9c3; padding: 8px 16px; border-radius: 6px; margin-bottom: 15px; color: #854d0e;">
                        Modo auditor. No puedes agregar, editar o eliminar clientes.
                    </div>
                <?php endif; ?>

                <?php if ($puedeEscribir): ?>
                <details class="registrar-cliente-details">
                    <summary><h2>Registrar clientes</h2></summary>
                    <form action="">
                        <div>
                            <label for="nombre">Nombre</label>
                            <input type="text" name="nombre" id="nombre" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,30}$" data-mensaje-error="Debe tener entre 3 y 30 caracteres, solo letras y espacios.">
                        </div>

                        <div>
                            <label for="contacto">Contacto</label>
                            <input type="text" name="contacto" id="contacto" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,30}$" data-mensaje-error="Debe tener entre 3 y 30 caracteres, solo letras y espacios.">
                        </div>

                        <div>
                            <label for="telefono">Teléfono</label>
                            <input type="tel" name="telefono" id="telefono" pattern="^\d{10}$" required data-mensaje-error="Debe contener exactamente 10 dígitos numéricos.">
                        </div>

                        <div>
                            <label for="correo">Correo Electronico</label>
                            <input type="email" name="correo" id="correo" pattern="^[a-zA-Z0-9._%\+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" required data-mensaje-error="Ingresa un correo electrónico válido (ejemplo@dominio.com).">
                        </div>

                        <div>
                            <label for="direccion">Dirección</label>
                            <input type="text" name="direccion" id="direccion" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,#\/\-]{10,120}$" data-mensaje-error="Debe tener entre 10 y 120 caracteres.">
                        </div>

                        <button type="submit">Guardar</button>
                    </form>
                </details>
                <?php endif; ?>

                <h2>Lista de clientes</h2>

                <div class="search-container">
                    <button type="submit" id="search-button"></button>
                    <input type="text" name="buscar" id="busqueda" placeholder="Buscar producto...">
                </div>

                <div class="tabla-clientes">
                     <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Dirección</th>
                            <th>Estado</th>
                            <th>Saldo</th>
                            <th>Acciones</th>
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
            const puedeEscribir = <?= json_encode($puedeEscribir) ?>;

            function renderFilaCliente(registro){
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
                        <td>${textoSeguro(registro.correo)}</td>
                        <td>${textoSeguro(registro.direccion)}</td>
                        <td><span class="badge ${claseBadge}">${registro.estado}</span></td>
                        <td>${formatoDinero(registro.saldo)}</td>
                        <td class="acciones">${acciones}</td>
                    </tr>`;
            }

            inicializarRegistroTablaApi({
                endpoint: '../assets/php/clientes_api.php',
                formSelector: '.registrar-cliente-details form',
                tablaSelector: '.tabla-clientes table',
                renderFila: renderFilaCliente
            });
        </script>

        <script>
            fetch('navbar.php')
            .then(response => response.text())
            .then( data => {
                document.getElementById('navbar-container').innerHTML = data;
                const links = document.querySelectorAll('nav ul li a');
                links.forEach(link => {
                    if(link.textContent.trim() === 'Clientes') {
                        link.classList.add('active-link');
                }
                });
                inicializarMenuHamburguesa();
            })
            .catch(error => console.error("Error al cargar el navbar", error))
        </script>
    </body>
</html>