<?php
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!usuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado.']);
    exit;
}

if (!in_array(obtenerRolUsuario(), ['Administrador', 'Almacenista', 'Auditor'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para ver categorías.']);
    exit;
}

try {
    $conexion = obtenerConexionBD();
} catch (\mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos.']);
    exit;
}

// Mismas reglas que validan el formulario en categorias.php (assets/scripts/validacion.js
// solo valida en el navegador; aquí se repiten para no confiar únicamente en el cliente).
function validarCategoria($nombre, $descripcion) {
    $errores = [];
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,30}$/u', $nombre)) {
        $errores[] = 'El nombre debe tener entre 3 y 30 caracteres, solo letras y espacios.';
    }
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,;:()\-]{10,150}$/u', $descripcion)) {
        $errores[] = 'La descripción debe tener entre 10 y 150 caracteres.';
    }
    return $errores;
}

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $filas = $conexion->query('SELECT id, nombre, descripcion FROM categorias ORDER BY id')->fetch_all(MYSQLI_ASSOC);
    echo json_encode($filas);
    exit;
}

if ($metodo !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

// A partir de aquí todas las acciones son de escritura.
if (!usuarioPuedeEscribir()) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para modificar categorías.']);
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'crear' || $accion === 'actualizar') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $errores = validarCategoria($nombre, $descripcion);

    if ($accion === 'actualizar') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) $errores[] = 'ID inválido.';
    }

    if ($errores) {
        http_response_code(422);
        echo json_encode(['error' => implode(' ', $errores)]);
        exit;
    }

    if ($accion === 'crear') {
        ejecutarConsulta($conexion, 'INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)', [$nombre, $descripcion]);
        $id = (int) $conexion->insert_id;
    } else {
        ejecutarConsulta($conexion, 'UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?', [$nombre, $descripcion, $id]);
    }

    echo json_encode(['id' => $id, 'nombre' => $nombre, 'descripcion' => $descripcion]);
    exit;
}

if ($accion === 'eliminar') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(422);
        echo json_encode(['error' => 'ID inválido.']);
        exit;
    }

    try {
        ejecutarConsulta($conexion, 'DELETE FROM categorias WHERE id = ?', [$id]);
        echo json_encode(['success' => true]);
    } catch (\mysqli_sql_exception $e) {
        http_response_code(409);
        echo json_encode(['error' => 'No se puede eliminar: hay productos que usan esta categoría.']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Acción no válida.']);
