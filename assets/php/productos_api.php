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
    echo json_encode(['error' => 'No tienes permisos para ver el inventario.']);
    exit;
}

try {
    $conexion = obtenerConexionBD();
} catch (\mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos.']);
    exit;
}

// Mismas reglas que validan el formulario en inventario.php (el navegador solo valida
// del lado del cliente; aquí se repiten para no confiar únicamente en eso).
function validarProducto($nombre, $descripcion, $stock, $precio) {
    $errores = [];
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-()]{3,30}$/u', $nombre)) {
        $errores[] = 'El nombre debe tener entre 3 y 30 caracteres.';
    }
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,;:()+\-]{3,60}$/u', $descripcion)) {
        $errores[] = 'La descripción debe tener entre 3 y 60 caracteres.';
    }
    if (!preg_match('/^[0-9]{1,5}$/', (string) $stock)) {
        $errores[] = 'El stock debe ser un número entero válido (hasta 5 dígitos).';
    }
    if (!is_numeric($precio) || $precio < 0) {
        $errores[] = 'El precio debe ser un número válido mayor o igual a 0.';
    }
    return $errores;
}

function resolverCategoria($conexion, $nombreCategoria) {
    $fila = filaDe(ejecutarConsulta($conexion, 'SELECT id FROM categorias WHERE nombre = ?', [$nombreCategoria]));
    return $fila ? (int) $fila['id'] : null;
}

function resolverProveedor($conexion, $nombreProveedor) {
    $fila = filaDe(ejecutarConsulta($conexion, 'SELECT id_p FROM proveedores WHERE nombre_p = ?', [$nombreProveedor]));
    return $fila ? (int) $fila['id_p'] : null;
}

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $filas = $conexion->query(
        'SELECT p.id_pr AS id, p.nombre_pr AS nombre, p.descripcion_pr AS descripcion,
                c.nombre AS categoria, pr.nombre_p AS proveedor,
                p.stock_actual AS stock, p.precio_unitario_pr AS precio
         FROM productos p
         LEFT JOIN categorias c ON c.id = p.id_categoria
         LEFT JOIN proveedores pr ON pr.id_p = p.id_proveedor
         ORDER BY p.id_pr'
    )->fetch_all(MYSQLI_ASSOC);
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
    echo json_encode(['error' => 'No tienes permisos para modificar el inventario.']);
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'crear' || $accion === 'actualizar') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $nombreCategoria = trim($_POST['categoria'] ?? '');
    $nombreProveedor = trim($_POST['proveedor'] ?? '');

    $errores = validarProducto($nombre, $descripcion, $stock, $precio);

    $idCategoria = resolverCategoria($conexion, $nombreCategoria);
    if ($idCategoria === null) $errores[] = 'Selecciona una categoría válida.';

    $idProveedor = resolverProveedor($conexion, $nombreProveedor);
    if ($idProveedor === null) $errores[] = 'Selecciona un proveedor válido.';

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
            'INSERT INTO productos (nombre_pr, descripcion_pr, precio_unitario_pr, stock_actual, id_categoria, id_proveedor)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$nombre, $descripcion, $precio, (int) $stock, $idCategoria, $idProveedor]
        );
        $id = (int) $conexion->insert_id;
    } else {
        ejecutarConsulta(
            $conexion,
            'UPDATE productos SET nombre_pr = ?, descripcion_pr = ?, precio_unitario_pr = ?, stock_actual = ?,
                    id_categoria = ?, id_proveedor = ?
             WHERE id_pr = ?',
            [$nombre, $descripcion, $precio, (int) $stock, $idCategoria, $idProveedor, $id]
        );
    }

    echo json_encode([
        'id' => $id, 'nombre' => $nombre, 'descripcion' => $descripcion,
        'categoria' => $nombreCategoria, 'proveedor' => $nombreProveedor,
        'stock' => (int) $stock, 'precio' => (float) $precio,
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
        ejecutarConsulta($conexion, 'DELETE FROM productos WHERE id_pr = ?', [$id]);
        echo json_encode(['success' => true]);
    } catch (\mysqli_sql_exception $e) {
        http_response_code(409);
        echo json_encode(['error' => 'No se puede eliminar: hay movimientos asociados a este producto.']);
    }
    exit;
}

if ($accion === 'salida') {
    $id = (int) ($_POST['id'] ?? 0);
    $cantidad = (int) ($_POST['cantidad'] ?? 0);

    if ($id <= 0 || $cantidad <= 0) {
        http_response_code(422);
        echo json_encode(['error' => 'Cantidad inválida.']);
        exit;
    }

    try {
        $conexion->begin_transaction();

        $producto = filaDe(ejecutarConsulta($conexion, 'SELECT stock_actual FROM productos WHERE id_pr = ? FOR UPDATE', [$id]));

        if (!$producto) {
            $conexion->rollback();
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado.']);
            exit;
        }

        if ($cantidad > (int) $producto['stock_actual']) {
            $conexion->rollback();
            http_response_code(422);
            echo json_encode(['error' => "No hay suficiente stock (disponible: {$producto['stock_actual']})."]);
            exit;
        }

        ejecutarConsulta($conexion, 'UPDATE productos SET stock_actual = stock_actual - ? WHERE id_pr = ?', [$cantidad, $id]);

        ejecutarConsulta(
            $conexion,
            'INSERT INTO movimientos (id_producto, tipo_m, cantidad_m, motivo_m, id_user) VALUES (?, 0, ?, ?, ?)',
            [$id, $cantidad, 'Salida registrada desde Inventario', $_SESSION['usuario_id']]
        );

        $conexion->commit();

        $stockRestante = (int) $producto['stock_actual'] - $cantidad;
        echo json_encode(['success' => true, 'stock' => $stockRestante]);
    } catch (\mysqli_sql_exception $e) {
        $conexion->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Error al registrar la salida.']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Acción no válida.']);
