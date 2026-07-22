<?php
require_once __DIR__ . '/../assets/php/roles.php';
verificarAutenticacion();
verificarAcceso(basename(__FILE__));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parámetros de la empresa | ADVITIUM</title>
    <link rel="shortcut icon" href="../assets/img/icons/categoriasicono.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/parametros.css">
</head>
<body>
    
    <div id="navbar-container"></div>

    <main class="main-container">
        <div class="card-panel">
            <h1 class="titulo-pagina">Parámetros de la empresa</h1>

            <div class="seccion-datos">
                <form action="" type="POST">
                        <div>
                            <label for="razon-social">Razón social: </label>
                            <input type="text" name="razon-social" id="razons" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.&-]{5,40}$"/>
                        </div>
                        <div>
                            <label for="rfc">RFC: </label>
                            <input type="text" name="rfc" id="rfc" required pattern="[A-ZÃ‘&]{3,4}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])(?:[A-Z\d]{3})"/>
                        </div>
                         <div>
                            <label for="regimen-fiscal">Régimen fiscal: </label>
                            <input type="text" name="regimen-fiscal" id="regimen-fiscal" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.&-]{5,40}$"/>
                        </div>
                        <div>
                            <label for="domicilio">Domicilio: </label>
                            <input type="text" name="domicilio" id="domicilio" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.&-]{5,40}$"/>
                        </div>
                </form>
            </div>

            <div class="logo">
                <form name="logo" type="POST">
                        <input type="file" name="logo" id="logo"/>
                        <button type="submit" name="subir-imagen">Guardar</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        fetch('navbar.php')
        .then(response => response.text())
        .then( data => {
            document.getElementById('navbar-container').innerHTML = data;
            const links = document.querySelectorAll('nav ul li a');
            links.forEach(link => {
                if(link.textContent.trim() === 'parametros.php') {
                    link.classList.add('active-link');
                }
            });
        })    
        .catch(error => console.error("Error al cargar el navbar", error))
    </script>

</body>
</html>
