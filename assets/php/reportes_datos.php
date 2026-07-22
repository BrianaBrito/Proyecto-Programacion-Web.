<?php
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!usuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado.']);
    exit;
}

if (!in_array(obtenerRolUsuario(), ['Administrador', 'Auditor'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para ver reportes.']);
    exit;
}


$consultas = [
    'productos' => [
        'columnas' => ['ID', 'Nombre', 'Categoría', 'Proveedor', 'Precio unitario', 'Stock'],
        'sql' => 'SELECT p.id_pr AS id, p.nombre_pr AS nombre, c.nombre AS categoria,
                         pr.nombre_p AS proveedor, p.precio_unitario_pr AS precio_unitario, p.stock_actual AS stock
                  FROM productos p
                  LEFT JOIN categorias c ON c.id = p.id_categoria
                  LEFT JOIN proveedores pr ON pr.id_p = p.id_proveedor
                  ORDER BY p.id_pr',
    ],
    'categorias' => [
        'columnas' => ['ID', 'Nombre', 'Descripción'],
        'sql' => 'SELECT id, nombre, descripcion FROM categorias ORDER BY id',
    ],
    'proveedores' => [
        'columnas' => ['ID', 'Nombre', 'Contacto', 'Teléfono', 'Email', 'Estado', 'Saldo'],
        'sql' => "SELECT id_p AS id, nombre_p AS nombre, contacto_p AS contacto, telefono_p AS telefono,
                         email_p AS email, IF(estado_p = 1, 'Activo', 'Inactivo') AS estado, saldo_p AS saldo
                  FROM proveedores ORDER BY id_p",
    ],
    'clientes' => [
        'columnas' => ['ID', 'Nombre', 'Contacto', 'Teléfono', 'Email', 'Estado', 'Saldo'],
        'sql' => "SELECT id_c AS id, nombre_c AS nombre, contacto_c AS contacto, telefono_c AS telefono,
                         email_c AS email, IF(estado_c = 1, 'Activo', 'Inactivo') AS estado, saldo_c AS saldo
                  FROM clientes ORDER BY id_c",
    ],
    'movimientos' => [
        'columnas' => ['ID', 'Fecha', 'Producto', 'Tipo', 'Cantidad', 'Motivo', 'Responsable'],
        'sql' => "SELECT m.id_m AS id, m.fecha_m AS fecha, p.nombre_pr AS producto,
                         IF(m.tipo_m = 1, 'Entrada', 'Salida') AS tipo, m.cantidad_m AS cantidad,
                         m.motivo_m AS motivo, u.nombre_u AS responsable
                  FROM movimientos m
                  LEFT JOIN productos p ON p.id_pr = m.id_producto
                  LEFT JOIN usuarios u ON u.id_u = m.id_user
                  ORDER BY m.fecha_m DESC",
    ],
];

$entidad = $_GET['entidad'] ?? '';

if (!isset($consultas[$entidad])) {
    http_response_code(400);
    echo json_encode(['error' => 'Entidad no válida.']);
    exit;
}

try {
    $pdo = obtenerConexionBD();
    $filas = $pdo->query($consultas[$entidad]['sql'])->fetchAll();
    echo json_encode([
        'columnas' => $consultas[$entidad]['columnas'],
        'filas' => $filas,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la base de datos.']);
}
