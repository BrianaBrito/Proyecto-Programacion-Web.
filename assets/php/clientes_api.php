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
    echo json_encode(['error' => 'No tienes permisos para ver clientes.']);
    exit;
}

try {
    $conexion = obtenerConexionBD();
} catch (\mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos.']);
    exit;
}

function validarCliente($nombre, $contacto, $telefono, $correo, $direccion) {
    $errores = [];
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,30}$/u', $nombre)) {
        $errores[] = 'El nombre debe tener entre 3 y 30 caracteres, solo letras y espacios.';
    }
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,30}$/u', $contacto)) {
        $errores[] = 'El contacto debe tener entre 3 y 30 caracteres, solo letras y espacios.';
    }
    if (!preg_match('/^\d{10}$/', $telefono)) {
        $errores[] = 'El teléfono debe contener exactamente 10 dígitos numéricos.';
    }
    if (!preg_match('/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/', $correo)) {
        $errores[] = 'Ingresa un correo electrónico válido.';
    }
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,#\/\-]{10,120}$/u', $direccion)) {
        $errores[] = 'La dirección debe tener entre 10 y 120 caracteres.';
    }
    return $errores;
}

$puedeEscribir = usuarioPuedeGestionarFinanzas();

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $filas = $conexion->query(
        "SELECT id_c AS id, nombre_c AS nombre, contacto_c AS contacto, telefono_c AS telefono,
                email_c AS correo, direccion_c AS direccion, saldo_c AS saldo,
                IF(estado_c = 1, 'Activo', 'Inactivo') AS estado
         FROM clientes ORDER BY id_c"
    )->fetch_all(MYSQLI_ASSOC);
    echo json_encode($filas);
    exit;
}

if ($metodo !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

if (!$puedeEscribir) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para modificar clientes.']);
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'crear' || $accion === 'actualizar') {
    $nombre = trim($_POST['nombre'] ?? '');
    $contacto = trim($_POST['contacto'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $errores = validarCliente($nombre, $contacto, $telefono, $correo, $direccion);

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
        ejecutarConsulta(
            $conexion,
            'INSERT INTO clientes (nombre_c, contacto_c, telefono_c, email_c, direccion_c, estado_c, saldo_c)
             VALUES (?, ?, ?, ?, ?, 1, 0)',
            [$nombre, $contacto, $telefono, $correo, $direccion]
        );
        $id = (int) $conexion->insert_id;
        $estado = 'Activo';
        $saldo = 0;
    } else {
        ejecutarConsulta(
            $conexion,
            'UPDATE clientes SET nombre_c = ?, contacto_c = ?, telefono_c = ?, email_c = ?, direccion_c = ?
             WHERE id_c = ?',
            [$nombre, $contacto, $telefono, $correo, $direccion, $id]
        );
        $actual = filaDe(ejecutarConsulta(
            $conexion,
            "SELECT saldo_c AS saldo, IF(estado_c = 1, 'Activo', 'Inactivo') AS estado FROM clientes WHERE id_c = ?",
            [$id]
        ));
        $saldo = $actual['saldo'] ?? 0;
        $estado = $actual['estado'] ?? 'Activo';
    }

    echo json_encode([
        'id' => $id, 'nombre' => $nombre, 'contacto' => $contacto, 'telefono' => $telefono,
        'correo' => $correo, 'direccion' => $direccion, 'saldo' => $saldo, 'estado' => $estado,
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
        ejecutarConsulta($conexion, 'DELETE FROM clientes WHERE id_c = ?', [$id]);
        echo json_encode(['success' => true]);
    } catch (\mysqli_sql_exception $e) {
        http_response_code(409);
        echo json_encode(['error' => 'No se puede eliminar: hay movimientos asociados a este cliente.']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Acción no válida.']);
