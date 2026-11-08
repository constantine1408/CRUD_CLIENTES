<?php

declare(strict_types=1);

/**
 * Crea la conexión MySQL/MariaDB a partir de variables de entorno.
 *
 * Valores por defecto echos para XAMPP/WAMP (localhost, root, sin clave).
 * Con Docker Compose las variables se definen en compose.yaml.
 */
function conectar(): mysqli
{
    // Que mysqli lance excepciones (mysqli_sql_exception) en vez de warnings.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  //excepción cuando algo falla//

    $host = getenv('DB_HOST') ?: 'localhost';
    $puerto = (int) (getenv('DB_PORT') ?: '3306');
    $base = getenv('DB_NAME') ?: 'crud_clientes';
    $usuario = getenv('DB_USER') ?: 'root';
    $clave = getenv('DB_PASS');
    $clave = $clave === false ? '' : $clave;

    $conexion = new mysqli($host, $usuario, $clave, $base, $puerto);
    $conexion->set_charset('utf8mb4');

    return $conexion;
}
