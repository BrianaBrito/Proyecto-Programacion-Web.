<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'advitium_db');
define('DB_USER', 'root');
define('DB_PASS', '');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function obtenerConexionBD() {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conexion->set_charset('utf8mb4');
    return $conexion;
}

function ejecutarConsulta(mysqli $conexion, string $sql, array $parametros = []) {
    $stmt = $conexion->prepare($sql);

    if ($parametros) {
        $tipos = '';
        foreach ($parametros as $parametro) {
            if (is_int($parametro)) {
                $tipos .= 'i';
            } elseif (is_float($parametro)) {
                $tipos .= 'd';
            } else {
                $tipos .= 's';
            }
        }
        $stmt->bind_param($tipos, ...$parametros);
    }

    $stmt->execute();
    return $stmt;
}

function filasDe(mysqli_stmt $stmt) {
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function filaDe(mysqli_stmt $stmt) {
    return $stmt->get_result()->fetch_assoc();
}
