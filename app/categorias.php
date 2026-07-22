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
    <title>Categorias | ADVITIUM</title>
    <link rel="shortcut icon" href="../assets/img/icons/categoriasicono.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/categorias.css">
</head>
<body>
    
    <div id="navbar-container"></div>

    <main class="main-container">
        <div class="card-panel">
            <h1 class="titulo-pagina">Categorias</h1>

            <?php if (!$puedeEscribir): ?>
                <div style="background: #fef9c3; padding: 8px 16px; border-radius: 6px; margin-bottom: 15px; color: #854d0e;">
                    Modo auditor. No puedes agregar, editar o eliminar categorías.
                </div>
            <?php endif; ?>

            <?php if ($puedeEscribir): ?>
            <details class="registrar-producto-details">
                <summary><h2>Registrar categoria</h2></summary>
                <form action="">
                    <div>
                        <label for="nombre">Nombre de la categoria: </label>
                        <input type="text" name="nombre" id="nombre" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,30}$" data-mensaje-error="Debe tener entre 3 y 30 caracteres, solo letras y espacios.">
                    </div>
                    <div>
                        <label for="descripcion">Descripcion: </label>
                        <input type="text" name="descripcion" id="descripcion" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,;:\(\)\-]{10,150}$" data-mensaje-error="Debe tener entre 10 y 150 caracteres.">
                    </div>
                    <button type="submit">Guardar</button>
                </form>
            </details>
            <?php endif; ?>

            <h2>Lista de categorias</h2>

            <div class="search-container">
                <button type="submit" id="search-button"></button>
                <input type="text" name="buscar" id="busqueda" placeholder="Buscar categoria...">
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripcion</th>
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

        function renderFilaCategoria(registro){
            const acciones = puedeEscribir
                ? `<button>Editar</button><button>Eliminar</button>`
                : `<span style="color:#999; font-size:0.9rem;">—</span>`;
            return `
                <tr data-id="${registro.id}">
                    <td>${registro.id}</td>
                    <td>${textoSeguro(registro.nombre)}</td>
                    <td>${textoSeguro(registro.descripcion)}</td>
                    <td class="acciones">${acciones}</td>
                </tr>`;
        }

        inicializarRegistroTablaApi({
            endpoint: '../assets/php/categorias_api.php',
            formSelector: '.registrar-producto-details form',
            tablaSelector: 'main table',
            renderFila: renderFilaCategoria
        });
    </script>

    <script>
        fetch('navbar.php')
        .then(response => response.text())
        .then( data => {
            document.getElementById('navbar-container').innerHTML = data;
            const links = document.querySelectorAll('nav ul li a');
            links.forEach(link => {
                if(link.textContent.trim() === 'Categorias') {
                    link.classList.add('active-link');
                }
            });
            inicializarMenuHamburguesa();
        })
        .catch(error => console.error("Error al cargar el navbar", error))
    </script>

</body>
</html>
