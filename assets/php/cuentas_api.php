<?php
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!usuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado.']);
    exit;
}

// Movimientos financieros: mismo acceso que cuentas.php en assets/php/roles.php
// -> verificarAcceso() (Almacenista no tiene acceso a módulos financieros).
if (!in_array(obtenerRolUsuario(), ['Administrador', 'Auditor'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para ver movimientos financieros.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

$tipo = $_GET['tipo'] ?? '';
if (!in_array($tipo, ['proveedor', 'cliente'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo no válido.']);
    exit;
}

try {
    $conexion = obtenerConexionBD();

    if ($tipo === 'proveedor') {
        $filas = $conexion->query(
            "SELECT id_cp AS id, id_proveedor, IF(tipo = 1, 'Cargo', 'Pago') AS tipo,
                    monto, fecha, motivo
             FROM movimientos_cuenta_proveedores ORDER BY fecha DESC"
        )->fetch_all(MYSQLI_ASSOC);
    } else {
        $filas = $conexion->query(
            "SELECT id_cc AS id, id_cliente, IF(tipo = 1, 'Cargo', 'Pago') AS tipo,
                    monto, fecha, motivo
             FROM movimientos_cuenta_clientes ORDER BY fecha DESC"
        )->fetch_all(MYSQLI_ASSOC);
    }

    echo json_encode($filas);
} catch (\mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la base de datos.']);
}
