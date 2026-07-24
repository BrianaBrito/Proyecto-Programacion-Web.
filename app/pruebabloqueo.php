<?php
session_start();

require_once __DIR__ . '/../assets/php/db.php';

const ROLES_VALIDOS = ['Administrador', 'Almacenista', 'Auditor'];

// Conexión a la base de datos y manejo de errores
//fix errores cambiamos $pdo por $conexion
try {
    $conexion = obtenerConexionBD();
} catch (\mysqli_sql_exception $e) {
    die('Error de conexión a la base de datos.');
}

//consulta de usuario por correo electrónico
//para evitar inyecciones SQL, se utiliza una consulta preparada
function obtenerUsuarioPorEmail($conexion, $email) {
    return filaDe(ejecutarConsulta(
        $conexion,
        'SELECT id_u AS id, nombre_u AS nombre, contrasena_hash, rol_u AS rol, estado_u AS activo
         FROM usuarios WHERE email_u = ?',
        [$email]
    ));
}

function validarCredenciales($email, $password, $usuario) {
    if (!$usuario) return 'Usuario o contraseña incorrectos';
    if (!$usuario['activo']) return 'Cuenta bloqueada. Contacta al administrador.';
    if (!password_verify($password, $usuario['contrasena_hash'])) return 'Usuario o contraseña incorrectos';
    return null;
}

function registrarUsuario($conexion, $nombre, $apellido, $email, $password, $rol) {
    $errores = [];
    if (empty($nombre) || empty($apellido)) $errores[] = 'Nombre y apellido son obligatorios.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Correo inválido.';
    if (strlen($password) < 8) $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    if (!in_array($rol, ROLES_VALIDOS, true)) $errores[] = 'Rol inválido.';
    
    if (empty($errores)) {
        $existente = filaDe(ejecutarConsulta($conexion, 'SELECT id_u FROM usuarios WHERE email_u = ?', [$email]));
        if ($existente) $errores[] = 'Ese correo ya está registrado.';

    }

    if (!empty($errores)) return ['error' => implode(' ', $errores)];

    $hash = password_hash($password, PASSWORD_DEFAULT);
    // la tabla usuarios no tiene columna "apellido" propia, se guarda como nombre completo
    $nombreCompleto = trim($nombre . ' ' . $apellido);
    ejecutarConsulta(
        $conexion,
        'INSERT INTO usuarios (nombre_u, contacto_u, email_u, contrasena_hash, rol_u, estado_u)
         VALUES (?, ?, ?, ?, ?, 1)',
        [$nombreCompleto, '', $email, $hash, $rol]
    );    
    return ['success' => 'Usuario registrado correctamente.'];
}

function cambiarEstadoUsuario($conexion, $id, $activo) {
    ejecutarConsulta($conexion, 'UPDATE usuarios SET estado_u = ? WHERE id_u = ?', [$activo ? 1 : 0, $id]);
    return $activo ? 'Usuario desbloqueado.' : 'Usuario bloqueado.';
}


$errorLogin = '';
$mensajeUsuarios = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $errorLogin = 'Usuario o contraseña incorrectos';
    } else {
        $usuario = obtenerUsuarioPorEmail($conexion, $email);
        $error = validarCredenciales($email, $password, $usuario);
        if ($error) {
            $errorLogin = $error;
        } else {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            header('Location: pruebabloqueo.php');
            exit;
        }
    }
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: pruebabloqueo.php');
    exit;
}

$sesionActiva = isset($_SESSION['usuario_id']);
$esAdmin = $sesionActiva && $_SESSION['usuario_rol'] === 'Administrador';

if ($esAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'registrar_usuario') {
        $resultado = registrarUsuario(
            $conexion,
            trim($_POST['nombre'] ?? ''),
            trim($_POST['apellido'] ?? ''),
            trim($_POST['email'] ?? ''),
            $_POST['password'] ?? '',
            $_POST['rol'] ?? ''
        );
        $mensajeUsuarios = $resultado['error'] ?? $resultado['success'] ?? '';
    } elseif (isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado') {
        $id = (int) $_POST['usuario_id'];
        $activo = (int) ($_POST['activo'] ?? 0);
        $mensajeUsuarios = cambiarEstadoUsuario($conexion, $id, $activo);
    }
}

$listaUsuarios = [];
if ($esAdmin) {
    $listaUsuarios = filasDe(ejecutarConsulta(
        $conexion,
        'SELECT id_u AS id, nombre_u AS nombre, email_u AS email, rol_u AS rol, estado_u AS activo
         FROM usuarios ORDER BY id_u'
    ));
}
?>
<?php


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advitium | Panel</title>
    <link rel="shortcut icon" href="assets/img/icons/inicioicono.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
     <link rel="shortcut icon" href="../assets/img/icons/inicioicono.png" type="image/x-icon">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bloqueo.css">
    <link rel="stylesheet" href="../assets/css/inicio.css">
    <link rel="stylesheet" href="../assets/css/pruebabloqueo.css">

</head>
<style>


</style>
<body>

<?php if (!$sesionActiva): ?>
    <div id="lock-screen" style="display:flex;">
        <div class="lock-card">
            <div class="lock-header">
                <h1>ADVITIUM</h1>
                <p class="subtitle">Tu inventario al alcance de tu mano</p>
            </div>
            <hr class="lock-divider">
            <div class="empresa-info"><!-- Se podria mostrar información de la empresa con la base de datos despues -->
                <p><strong>Empresa:</strong> NOMBREMPRESAA S.A. de C.V.</p>
                <p><strong>RFC:</strong> ASI1234567B8</p>
                <p><strong>Dirección:</strong> Calle Morelos #123, Col. Centro</p>
                <p><strong>Población:</strong> Zacatepec, Morelos</p>
                <p><strong>Teléfono:</strong> +52 777 123 4567</p>
                <div class="serie">
                    <span><strong>Licencia:</strong> ADV-2026-001</span>
                    <span style="float: right;"><strong>Usuarios:</strong> 1</span>
                </div>
            </div>

            <form class="lock-form" method="POST" action="pruebabloqueo.php">
                <input type="hidden" name="accion" value="login">
                <label for="email">Usuario (correo)</label>
                <input type="email" id="email" name="email" placeholder="ej. admin@advitium.com" required>
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
                <button type="submit" class="btn-unlock">Ingresar</button>
                <?php if ($errorLogin): ?>
                    <p id="error-msg" class="error-msg" style="display:block;"><?= htmlspecialchars($errorLogin) ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>

<?php else: ?>
    <div id="navbar-container"></div>

    <div id="main-content" style="display:block;">
        <div class="main-container">
            <div class="card-inicio">
                <div class="encabezado-dashboard">
                    <h1 class="titulo-inicio">ADVITIUM</h1>
                    <div class="user-info">
                        <span>Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong></span>
                        <span class="user-role"><?= htmlspecialchars($_SESSION['usuario_rol']) ?></span>
                        <a href="pruebabloqueo.php?logout=1" class="btn-logout">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
                            </svg>
                            Cerrar sesión
                        </a>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="dash-card">
                        <div class="dash-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="var(--color-principal)" class="dash-svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                        </div>
                        <div class="dash-info">
                            <h3>Productos</h3>
                            <p class="dash-num">342</p>
                        </div>
                    </div>

                    <div class="dash-card warning">
                        <div class="dash-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#EF4444" class="dash-svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="dash-info">
                            <h3>Stock Bajo</h3>
                            <p class="dash-num">12</p>
                        </div>
                    </div>

                    <div class="dash-card highlight">
                        <div class="dash-info" style="width:100%;">
                            <h3 style="margin-bottom:8px;">Actividad reciente</h3>
                            <div class="actividad-item">
                                <span class="punto azul"></span>
                                <p>Entrada: 50 Laptops</p>
                            </div>
                            <div class="actividad-item">
                                <span class="punto rojo"></span>
                                <p>Salida: 2 Multímetros</p>
                            </div>
                        </div>
                    </div>

                    <div class="dash-card">
                        <div class="dash-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="var(--color-principal)" class="dash-svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <div class="dash-info">
                            <h3>Proveedores</h3>
                            <p class="dash-num">15</p>
                        </div>
                    </div>
                </div>

                <div class="seccion-dashboard">
                    <h2 class="titulo-seccion">Acciones Rápidas</h2>
                    <div class="quick-actions">
                        <a href="../app/inventario.php" class="action-btn primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Nuevo Producto
                        </a>
                        <a href="../app/proveedores.php" class="action-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.66-1.332c-.1.034-.201.066-.303.096A24.067 24.067 0 014 19.235z" /></svg>
                            Añadir Proveedor
                        </a>
                        <a href="../app/movimientos.php" class="action-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                            Movimientos
                        </a>
                    </div>
                </div>

                <div class="seccion-dashboard">
                    <h2 class="titulo-seccion">Últimos Movimientos Registrados</h2>
                    <div class="table-responsive">
                        <table class="tabla-resumen">
                            <thead>
                                <tr><th>Fecha</th><th>Tipo</th><th>Producto</th><th>Cantidad</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>04/07/2026</td><td><span class="badge badge-entrada">Entrada</span></td><td>Laptops Dell XPS</td><td>+50</td></tr>
                                <tr><td>02/07/2026</td><td><span class="badge badge-salida">Salida</span></td><td>Multímetro Digital</td><td>-2</td></tr>
                                <tr><td>01/07/2026</td><td><span class="badge badge-entrada">Entrada</span></td><td>Teclados Mecánicos</td><td>+15</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($esAdmin): ?>
                <div class="admin-panel">
                    <details>
                        <summary><svg xmlns="http://w3.org" viewBox="0 0 24 24" width="18" height="18" style="margin-right: 6px; vertical-align: middle;">
  <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="#0D47A1"/>
</svg>
 Gestión de usuarios</summary>
                        <div class="panel-content">
                            <?php if ($mensajeUsuarios): ?>
                                <div class="mensaje"><?= htmlspecialchars($mensajeUsuarios) ?></div>
                            <?php endif; ?>

                            <form class="form-inline" method="POST" action="pruebabloqueo.php#admin-panel">
                                <input type="hidden" name="accion" value="registrar_usuario">
                                <input type="text" name="nombre" placeholder="Nombre" required>
                                <input type="text" name="apellido" placeholder="Apellido" required>
                                <input type="email" name="email" placeholder="Correo" required>
                                <input type="password" name="password" placeholder="Contraseña (min. 8)" minlength="8" required>
                                <select name="rol" required>
                                    <option value="Administrador">Administrador</option>
                                    <option value="Almacenista">Almacenista</option>
                                    <option value="Auditor">Auditor</option>
                                </select>
                                <button type="submit">Registrar</button>
                            </form>

                            <table>
                                <thead>
                                    <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acción</th></tr>
                                </thead>
                                <tbody>
                                <?php foreach ($listaUsuarios as $u): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['nombre']) ?></td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td><?= htmlspecialchars($u['rol']) ?></td>
                                        <td><span class="badge <?= $u['activo'] ? 'badge-activo' : 'badge-bloqueado' ?>"><?= $u['activo'] ? 'Activo' : 'Bloqueado' ?></span></td>
                                        <td>
                                            <form method="POST" action="pruebabloqueo.php#admin-panel" style="display:inline;">
                                                <input type="hidden" name="accion" value="cambiar_estado">
                                                <input type="hidden" name="usuario_id" value="<?= (int) $u['id'] ?>">
                                                <input type="hidden" name="activo" value="<?= $u['activo'] ? '0' : '1' ?>">
                                                <button type="submit" class="btn-estado"><?= $u['activo'] ? 'Bloquear' : 'Desbloquear' ?></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script>
        fetch('../app/navbar.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('navbar-container').innerHTML = data;
                const links = document.querySelectorAll('nav ul li a');
                links.forEach(link => {
                    if (link.textContent.trim() === 'Prueba') {
                        link.classList.add('active-link');
                    }
                });
            })
            .catch(error => console.error("Error al cargar el navbar:", error));
    </script>
      <script src="../assets/scripts/almacenamiento.js"></script>
    <script src="../assets/scripts/dashboard.js"></script>
<?php endif; ?>

</body>
</html>