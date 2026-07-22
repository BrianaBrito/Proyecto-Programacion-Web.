<?php
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!usuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado.']);
    exit;
}

// Historial de movimientos: solo lectura para los 3 roles (mismo acceso que
// movimientos.php en assets/php/roles.php -> verificarAcceso()).
if (!in_array(obtenerRolUsuario(), ['Administrador', 'Almacenista', 'Auditor'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para ver el historial de movimientos.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

try {
    $pdo = obtenerConexionBD();
    $filas = $pdo->query(
        "SELECT m.id_m AS id, m.fecha_m AS fecha, p.nombre_pr AS producto,
                IF(m.tipo_m = 1, 'Entrada', 'Salida') AS tipo, m.cantidad_m AS cantidad,
                m.motivo_m AS motivo, u.nombre_u AS responsable
         FROM movimientos m
         LEFT JOIN productos p ON p.id_pr = m.id_producto
         LEFT JOIN usuarios u ON u.id_u = m.id_user
         ORDER BY m.fecha_m DESC"
    )->fetchAll();
    echo json_encode($filas);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la base de datos.']);
}
