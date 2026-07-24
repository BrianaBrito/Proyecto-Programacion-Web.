<?php
require_once __DIR__ . '/../assets/php/roles.php';
$rolActual = obtenerRolUsuario();
?>
<head>
    <link rel="stylesheet" href="../assets/css/nav.css">
</head>

<nav>
    <h2>ADVITIUM</h2>
    <ul>
        <li><a href="inicio.php">Inicio</a></li>

        <?php if (in_array($rolActual, ['Administrador', 'Almacenista', 'Auditor'], true)): ?>
        <li><a href="inventario.php">Inventario</a></li>
        <li><a href="categorias.php">Categorias</a></li>
        <li><a href="proveedores.php">Proveedores</a></li>
        <li><a href="movimientos.php">Movimientos</a></li>
        <?php endif; ?>

        <?php if (in_array($rolActual, ['Administrador', 'Almacenista', 'Auditor'], true)): ?>
        <li><a href="cuentas.php">Movimientos financieros</a></li>
        <?php endif; ?>

        <?php if (in_array($rolActual, ['Administrador', 'Almacenista', 'Auditor'], true)): ?>
        <li><a href="clientes.php">Clientes</a></li>
        <li><a href="reportes.php">Reportes</a></li>
        <?php endif; ?>

        <?php if ($rolActual === 'Administrador'): ?>
        <li class="menu-item">
            <a href="#">Configuración
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                    viewBox="0 0 24 24">
                    <path d="m12 15.59-4.29-4.3-1.42 1.42 5.71 5.7 5.71-5.7-1.42-1.42z"></path>
                    <path d="m12 10.59-4.29-4.3-1.42 1.42 5.71 5.7 5.71-5.7-1.42-1.42z"></path>
                    </svg>
            </a>
            <ul class="opciones-menu-config">
                <li class="menu-inside">
                    <a href="parametros.php" class="menu-link menu-link--inside">Parámetros de la Empresa</a>
                </li>
                <li class="menu-inside">
                    <a href="pruebabloqueo.php" class="menu-link menu-link--inside">Gestión de usuarios</a>
                </li>
            </ul>
        </li>
        <?php endif; ?>
    </ul>
</nav>