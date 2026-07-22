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
    echo json_encode(['error' => 'No tienes permisos para ver proveedores.']);
    exit;
}

try {
    $pdo = obtenerConexionBD();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos.']);
    exit;
}

// Mismas reglas que validan el formulario en proveedores.php (el navegador solo valida
// del lado del cliente; aquí se repiten para no confiar únicamente en eso).
function validarProveedor($nombre, $contacto, $telefono, $email, $direccion) {
    $errores = [];
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.&\-]{3,30}$/u', $nombre)) {
        $errores[] = 'El nombre debe tener entre 3 y 30 caracteres.';
    }
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,30}$/u', $contacto)) {
        $errores[] = 'El contacto debe tener entre 3 y 30 caracteres, solo letras y espacios.';
    }
    if (!preg_match('/^\d{10}$/', $telefono)) {
        $errores[] = 'El teléfono debe contener exactamente 10 dígitos numéricos.';
    }
    if (!preg_match('/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', $email)) {
        $errores[] = 'Ingresa un correo electrónico válido.';
    }
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,#\/\-]{10,120}$/u', $direccion)) {
        $errores[] = 'La dirección debe tener entre 10 y 120 caracteres.';
    }
    return $errores;
}

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $filas = $pdo->query(
        "SELECT id_p AS id, nombre_p AS nombre, contacto_p AS contacto, telefono_p AS telefono,
                email_p AS email, direccion_p AS direccion, saldo_p AS saldo,
                IF(estado_p = 1, 'Activo', 'Inactivo') AS estado
         FROM proveedores ORDER BY id_p"
    )->fetchAll();
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
    echo json_encode(['error' => 'No tienes permisos para modificar proveedores.']);
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'crear' || $accion === 'actualizar') {
    $nombre = trim($_POST['nombre'] ?? '');
    $contacto = trim($_POST['contacto'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $errores = validarProveedor($nombre, $contacto, $telefono, $email, $direccion);

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
        // Un proveedor nuevo siempre entra Activo y con saldo en cero; el saldo se
        // gestiona aparte desde Movimientos financieros, no desde este formulario.
        $stmt = $pdo->prepare(
            'INSERT INTO proveedores (nombre_p, contacto_p, telefono_p, email_p, direccion_p, estado_p, saldo_p)
             VALUES (?, ?, ?, ?, ?, 1, 0)'
        );
        $stmt->execute([$nombre, $contacto, $telefono, $email, $direccion]);
        $id = (int) $pdo->lastInsertId();
        $estado = 'Activo';
        $saldo = 0;
    } else {
        $stmt = $pdo->prepare(
            'UPDATE proveedores SET nombre_p = ?, contacto_p = ?, telefono_p = ?, email_p = ?, direccion_p = ?
             WHERE id_p = ?'
        );
        $stmt->execute([$nombre, $contacto, $telefono, $email, $direccion, $id]);
        $fila = $pdo->prepare("SELECT saldo_p AS saldo, IF(estado_p = 1, 'Activo', 'Inactivo') AS estado FROM proveedores WHERE id_p = ?");
        $fila->execute([$id]);
        $actual = $fila->fetch();
        $saldo = $actual['saldo'] ?? 0;
        $estado = $actual['estado'] ?? 'Activo';
    }

    echo json_encode([
        'id' => $id, 'nombre' => $nombre, 'contacto' => $contacto, 'telefono' => $telefono,
        'email' => $email, 'direccion' => $direccion, 'saldo' => $saldo, 'estado' => $estado,
    ]);
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
        $stmt = $pdo->prepare('DELETE FROM proveedores WHERE id_p = ?');
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(409);
        echo json_encode(['error' => 'No se puede eliminar: hay productos o movimientos asociados a este proveedor.']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Acción no válida.']);
