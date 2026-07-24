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
// -> verificarAcceso() (El almacenista si puede escribir en modulos financieros).
if (!in_array(obtenerRolUsuario(), ['Administrador', 'Almacenista', 'Auditor'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para ver movimientos financieros.']);
    exit;
}

try {
    $conexion = obtenerConexionBD();
} catch (\mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos.']);
    exit;
}

// Nombres de tabla/columna según el tipo de entidad. $tipo siempre se valida contra
// ['proveedor', 'cliente'] antes de llamar esta función, así que es seguro llevarlos
// directo en el SQL
function tablaMovimientoPara($tipo) {
    return $tipo === 'proveedor'
        ? ['tabla' => 'movimientos_cuenta_proveedores', 'columnaEntidad' => 'id_proveedor',
           'tablaEntidad' => 'proveedores', 'columnaIdEntidad' => 'id_p', 'columnaEstado' => 'estado_p']
        : ['tabla' => 'movimientos_cuenta_clientes', 'columnaEntidad' => 'id_cliente',
           'tablaEntidad' => 'clientes', 'columnaIdEntidad' => 'id_c', 'columnaEstado' => 'estado_c'];
}

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $tipo = $_GET['tipo'] ?? '';
    if (!in_array($tipo, ['proveedor', 'cliente'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Tipo no válido.']);
        exit;
    }

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
    exit;
}

//cambiamos de get a post para los movimientos financieros, ya que se van a crear nuevos registros y no solo consultar
if ($metodo !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

//crear un movimiento es trabajo de Administrador y de Almacenista (recibir/surtir
//mercancía implica cargos/pagos a proveedores); el Auditor solo consulta.
if (!usuarioPuedeRegistrarMovimientos()) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para registrar movimientos financieros.']);
    exit;
}

$accion = $_POST['accion'] ?? '';
if ($accion !== 'crear') {
    http_response_code(400);
    echo json_encode(['error' => 'Acción no válida.']);
    exit;
}

$tipoEntidad = $_POST['tipo'] ?? '';
if (!in_array($tipoEntidad, ['proveedor', 'cliente'], true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Tipo no válido.']);
    exit;
}

$idEntidad = (int) ($_POST['id_entidad'] ?? 0);
$tipoMovimiento = $_POST['tipo_movimiento'] ?? ''; // 'Cargo' | 'Pago'
$monto = $_POST['monto'] ?? '';
$motivo = trim($_POST['motivo'] ?? '');

$errores = [];
if ($idEntidad <= 0) {
    $errores[] = $tipoEntidad === 'proveedor' ? 'Elige un proveedor.' : 'Elige un cliente.';
}
if (!in_array($tipoMovimiento, ['Cargo', 'Pago'], true)) {
    $errores[] = 'El tipo de movimiento debe ser Cargo o Pago.';
}
if (!is_numeric($monto) || (float) $monto <= 0 || (float) $monto > 99999999.99) {
    $errores[] = 'El monto debe ser un número mayor a cero.';
}
if (mb_strlen($motivo) < 3 || mb_strlen($motivo) > 200) {
    $errores[] = 'El motivo debe tener entre 3 y 200 caracteres.';
}

if ($errores) {
    http_response_code(422);
    echo json_encode(['error' => implode(' ', $errores)]);
    exit;
}

$config = tablaMovimientoPara($tipoEntidad);

//confirmar que el proveedor/cliente elegido existe y está activo antes de insertar
$entidad = filaDe(ejecutarConsulta(
    $conexion,
    "SELECT {$config['columnaEstado']} AS estado FROM {$config['tablaEntidad']} WHERE {$config['columnaIdEntidad']} = ?",
    [$idEntidad]
));
if (!$entidad) {
    http_response_code(422);
    echo json_encode(['error' => $tipoEntidad === 'proveedor' ? 'Ese proveedor no existe.' : 'Ese cliente no existe.']);
    exit;
}
if (!$entidad['estado']) {
    http_response_code(422);
    echo json_encode(['error' => $tipoEntidad === 'proveedor'
        ? 'Ese proveedor está bloqueado/inactivo; no se le pueden registrar movimientos.'
        : 'Ese cliente está bloqueado/inactivo; no se le pueden registrar movimientos.']);
    exit;
}

$tipoValor = $tipoMovimiento === 'Cargo' ? 1 : 0;

try {
    ejecutarConsulta(
        $conexion,
        "INSERT INTO {$config['tabla']} ({$config['columnaEntidad']}, tipo, monto, motivo) VALUES (?, ?, ?, ?)",
        [$idEntidad, $tipoValor, (float) $monto, $motivo]
    );
} catch (\mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar el movimiento.']);
    exit;
}

echo json_encode([
    'success' => true,
    'id' => (int) $conexion->insert_id,
]);