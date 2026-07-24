<?php
session_start();

function usuarioAutenticado() {
    return isset($_SESSION['usuario_id']);
}

function obtenerRolUsuario() {
    return $_SESSION['usuario_rol'] ?? null;
}

function usuarioTieneRol($rol) {
    return usuarioAutenticado() && $_SESSION['usuario_rol'] === $rol;
}

function usuarioPuedeEscribir() {
    $rol = obtenerRolUsuario();
    return in_array($rol, ['Administrador', 'Almacenista']);
}

// Módulos financieros (edición completa de clientes, altas/bajas): solo Administrador.
function usuarioPuedeGestionarFinanzas() {
    return usuarioTieneRol('Administrador');
}

//El administrador y almacenista si pueden registrar movimientos financieros
function usuarioPuedeRegistrarMovimientos() {
    $rol = obtenerRolUsuario();
    return in_array($rol, ['Administrador', 'Almacenista'], true);
}

function usuarioPuedeGestionarUsuarios() {
    return usuarioTieneRol('Administrador');
}

function redirigir($url) {
    header('Location: ' . $url);
    exit;
}

function verificarAutenticacion() {
    if (!usuarioAutenticado()) {
        redirigir('pruebabloqueo.php');
    }
}

function verificarAcceso($pagina) {
    $permisos = [
        'inicio.php' => ['Administrador', 'Almacenista', 'Auditor'],
        'inventario.php' => ['Administrador', 'Almacenista', 'Auditor'],
        'categorias.php' => ['Administrador', 'Almacenista', 'Auditor'],
        'proveedores.php' => ['Administrador', 'Almacenista', 'Auditor'],
        'movimientos.php' => ['Administrador', 'Almacenista', 'Auditor'],
        'clientes.php' => ['Administrador', 'Auditor'],
        'cuentas.php' => ['Administrador', 'Almacenista', 'Auditor'],
        'reportes.php' => ['Administrador', 'Auditor'],
        'parametros.php' => ['Administrador'],
        'usuarios.php' => ['Administrador'],
    ];

    // Por defecto, si una página nueva no está listada, se niega el acceso salvo al Administrador.
    $rolesPermitidos = $permisos[$pagina] ?? ['Administrador'];

    $rol = obtenerRolUsuario();
    if (!in_array($rol, $rolesPermitidos)) {
      
        header('HTTP/1.0 403 Forbidden');
        echo '<!DOCTYPE html>
        <html>
        <head><title>Acceso denegado</title></head>
        <body style="font-family: sans-serif; text-align: center; padding: 50px;">
            <h1>Acceso denegado</h1>
            <p>No tienes permisos para acceder a esta página.</p>
            <a href="pruebabloqueo.php">Volver al inicio</a>
        </body>
        </html>';
        exit;
    }
}
?>