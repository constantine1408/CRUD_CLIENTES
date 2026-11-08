<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Cliente.php';
require_once __DIR__ . '/interfaces/RepositorioClientes.php';
require_once __DIR__ . '/repositories/ClienteRepositorioMysql.php';
require_once __DIR__ . '/controllers/ClienteController.php';

try {
    $conexion = conectar();
} catch (mysqli_sql_exception $e) {
    http_response_code(503);
    exit('No se pudo conectar con la base de datos. Revise la configuración (config/database.php).');
}

$controlador = new ClienteController(new ClienteRepositorioMysql($conexion));
$controlador->index();
