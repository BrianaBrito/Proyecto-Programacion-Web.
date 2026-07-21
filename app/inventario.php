<?php
require_once '../assets/php/roles.php';
verificarAutenticacion();
verificarAcceso(basename(__FILE__));
$puedeEscribir = usuarioPuedeEscribir();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario | ADVITIUM</title>
    <link rel="shortcut icon" href="../assets/img/icons/inventarioicono.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/inventario.css">
</head>
<body>
        
    <div id="navbar-container"></div>

    <main class="main-container">
        <div class="card-panel">
            <h1 class="titulo-pagina">Inventario</h1>
            
            <?php if (!$puedeEscribir): ?>
                <div style="background: #fef9c3; padding: 8px 16px; border-radius: 6px; margin-bottom: 15px; color: #854d0e;">
                   <svg xmlns="http://w3.org" viewBox="0 0 24 24" width="16" height="16" style="margin-right: 8px; vertical-align: middle;">
  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#E65100"/>
</svg>
  Modo auditor. No puedes agregar, editar o eliminar productos.
                </div>
            <?php endif; ?>

            <?php if ($puedeEscribir): ?>
            <details class="registrar-producto-details">
                <summary><h2>Registrar producto</h2></summary>
                <form action="">
                    <div>
                        <label for="nombre" >Nombre del producto: </label>
                        <input type="text" name="nombre" id="nombre" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9      \s\-\(\)]{3,30}$" data-mensaje-error="Debe tener entre 3 y 30 caracteres (letras, números, espacios, guiones o paréntesis).">
                    </div>
                    <div>
                        <label for="categoria">Categoria: </label>
                        <input type="text" name="categoria" id="categoria" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,30}$" data-mensaje-error="Debe tener entre 3 y 30 caracteres, solo letras y espacios.">
                    </div>
                    <div>
                        <label for="descripcion">Descripcion: </label>
                        <input type="text" name="descripcion" id="descripcion" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,;:\(\)\+\-]{3,60}$" data-mensaje-error="Debe tener entre 3 y 60 caracteres.">
                    </div>
                    <div>
                        <label for="proveedor">Proveedor: </label>
                        <select name="proveedor" id="proveedor" required>

                        </select>
                    </div>
                    <div>
                        <label for="stock">Stock actual: </label>
                        <input type="number" name="stock" id="stock" required pattern="^[0-9]{1,5}$" data-mensaje-error="Ingresa una cantidad válida (hasta 5 dígitos).">
                    </div>
                    <div>
                        <label for="precio">Precio: </label>
                        <input type="number" name="precio" id="precio" required step="0.01" min="0" pattern="^\d+(\.\d{1,2})?$" data-mensaje-error="Ingresa un precio válido (hasta 2 decimales).">
                    </div>
                    <button type="submit">Guardar</button>
                </form>
            </details>
            <?php endif; ?>

            <h2>Lista de productos</h2>

            <div class="search-container">  
                <button type="submit" id="search-button">  </button>
                <input type="text" name="buscar" id="busqueda" placeholder="Buscar producto...">
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Categoria</th>
                        <th>Descripcion</th>
                        <th>Proveedor</th>
                        <th>Stock</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </main>

    <dialog id="salida-dialog" class="salida-dialog">
        <h3>Registrar salida</h3>
        <p>Producto: <strong id="salida-producto"></strong> (Stock actual: <span id="salida-stock-actual"></span>)</p>
        <label for="salida-cantidad">Cantidad que sale</label>
        <input type="number" id="salida-cantidad" min="1">
        <p id="salida-resultado" class="salida-resultado"></p>
        <div class="salida-dialog-actions">
            <button type="button" id="salida-confirmar">Confirmar</button>
            <button type="button" id="salida-cancelar">Cerrar</button>
        </div>
    </dialog>

    <script src="../assets/scripts/navbar-menu.js"></script>
    <script src="../assets/scripts/tabla-ordenable.js"></script>
    <script src="../assets/scripts/busqueda.js"></script>
    <script src="../assets/scripts/almacenamiento.js"></script>
    <script src="../assets/scripts/validacion.js"></script>
    <script src="../assets/scripts/actualizacion-tablas.js"></script>

    <script>
        const puedeEscribir = <?= json_encode($puedeEscribir) ?>;

        const INVENTARIO_INICIAL = [
            { id: 1, nombre: 'Laptop', categoria: 'Electronica', descripcion: 'Gamer', proveedor: 'DDTech', stock: 15, precio: 10500 },
            { id: 2, nombre: 'Multimetro', categoria: 'Electronica', descripcion: 'Escolar', proveedor: 'Telmedia', stock: 23, precio: 299 },
            { id: 3, nombre: 'Playera Basica', categoria: 'Ropa', descripcion: 'Algodon talla M', proveedor: 'Textiles Monarca', stock: 80, precio: 120 },
            { id: 4, nombre: 'Arroz 1kg', categoria: 'Alimentos', descripcion: 'No perecedero', proveedor: 'Distribuidora Alimenticia del Norte', stock: 150, precio: 28 },
            { id: 5, nombre: 'Cuaderno Profesional', categoria: 'Papeleria', descripcion: '100 hojas, cuadricula', proveedor: 'Papelera del Centro', stock: 200, precio: 35 },
            { id: 6, nombre: 'Muñeca Articulada', categoria: 'Juguetes', descripcion: 'Edad recomendada 5+', proveedor: 'Juguetera Estrella', stock: 40, precio: 199 }
        ];

        const PROVEEDORES_INICIALES = [
            { id: 1, nombre: 'DDTech', contacto: 'Juan Perez', telefono: '3334445555', email: 'ventas@ddtech.com', direccion: 'Zapopan, Jalisco', saldo: 0 },
            { id: 2, nombre: 'Telmedia', contacto: 'Maria Lopez', telefono: '5556667777', email: 'contacto@telmedia.com', direccion: 'CDMX', saldo: 150 }
        ];

        function renderFilaInventario(registro) {
            let acciones = '';
            if (puedeEscribir) {
                acciones = `
                    <button>Editar</button>
                    <button>Eliminar</button>
                    <button class="btn-salida">Salida</button>
                `;
            } else {
                acciones = `<span style="color:#999; font-size:0.9rem;">—</span>`;
            }
            return `
                <tr data-id="${registro.id}">
                    <td data-label="ID">${registro.id}</td>
                    <td data-label="Nombre">${textoSeguro(registro.nombre)}</td>
                    <td data-label="Categoria">${textoSeguro(registro.categoria)}</td>
                    <td data-label="Descripcion">${textoSeguro(registro.descripcion)}</td>
                    <td data-label="Proveedor">${textoSeguro(registro.proveedor)}</td>
                    <td data-label="Stock">${registro.stock}</td>
                    <td data-label="Precio">${formatoDinero(registro.precio)}</td>
                    <td data-label="Acciones" class="acciones">${acciones}</td>
                </tr>`;
        }

        function poblarSelectProveedores() {
            if (obtenerColeccion(COLECCIONES.PROVEEDORES).length === 0) {
                guardarColeccion(COLECCIONES.PROVEEDORES, PROVEEDORES_INICIALES);
            }

            const select = document.getElementById('proveedor');
            const proveedores = obtenerColeccion(COLECCIONES.PROVEEDORES);
            const opcionesProveedores = proveedores
                .map(proveedor => `<option value="${textoSeguro(proveedor.nombre)}">${textoSeguro(proveedor.nombre)}</option>`)
                .join('');

            select.innerHTML = `<option value="" disabled selected hidden>Selecciona un proveedor</option>${opcionesProveedores}`;
        }

        poblarSelectProveedores();

        inicializarRegistroTabla({
            coleccion: COLECCIONES.INVENTARIO,
            formSelector: '.registrar-producto-details form',
            tablaSelector: 'main table',
            datosDefecto: INVENTARIO_INICIAL,
            renderFila: renderFilaInventario
        });
    </script>
  
    <script>
        fetch('../app/navbar.php')
        .then(response => response.text())
        .then( data => {
            document.getElementById('navbar-container').innerHTML = data;
            const links = document.querySelectorAll('nav ul li a');
            links.forEach(link => {
                if(link.textContent.trim() === 'Inventario') {
                    link.classList.add('active-link');
                }
            });
            inicializarMenuHamburguesa();
        })
        .catch(error => console.error("Error al cargar el navbar", error))

    </script>

    <script>
        if (puedeEscribir) {
            const salidaDialog = document.getElementById('salida-dialog');
            const salidaProducto = document.getElementById('salida-producto');
            const salidaStockActual = document.getElementById('salida-stock-actual');
            const salidaCantidad = document.getElementById('salida-cantidad');
            const salidaResultado = document.getElementById('salida-resultado');
            let filaActual = null;

            document.querySelector('main table').addEventListener('click', evento => {
                const boton = evento.target.closest('.btn-salida');
                if (!boton) return;

                filaActual = boton.closest('tr');
                const celdas = filaActual.querySelectorAll('td');
                salidaProducto.textContent = celdas[1].textContent.trim();
                salidaStockActual.textContent = celdas[5].textContent.trim();
                salidaCantidad.value = '';
                salidaCantidad.max = celdas[5].textContent.trim();
                salidaResultado.textContent = '';
                salidaResultado.classList.remove('error');
                salidaDialog.showModal();
            });

            document.getElementById('salida-confirmar').addEventListener('click', () => {
                const cantidad = parseInt(salidaCantidad.value, 10);
                const stock = parseInt(salidaStockActual.textContent, 10);

                if (!cantidad || cantidad <= 0){
                    salidaResultado.textContent = 'Ingresa una cantidad válida.';
                    salidaResultado.classList.add('error');
                    return;
                }

                if (cantidad > stock){
                    salidaResultado.textContent = `No hay suficiente stock (disponible: ${stock}).`;
                    salidaResultado.classList.add('error');
                    return;
                }

                salidaResultado.classList.remove('error');
                salidaResultado.textContent = `Salida registrada: ${cantidad} unidad(es) de ${salidaProducto.textContent}.`;
            });

            document.getElementById('salida-cancelar').addEventListener('click', () => {
                salidaDialog.close();
            });
        }
    </script>

</body>
</html>