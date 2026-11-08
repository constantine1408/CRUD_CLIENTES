<?php
/**
 * Vista del CRUD de clientes. 
 *
 * @var list<Cliente>       $clientes
 * @var list<string>        $errores
 * @var array<string,mixed> $datos     datos del formulario (vacío = alta)
 * @var string              $accion    crear | actualizar
 * @var string|null         $mensaje
 * @var string              $token     token CSRF
 * @var string              $busqueda
 */

function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$editando = $accion === 'actualizar';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD DE CLIENTES</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="panel">
    <header>
        <h1>CRUD de clientes</h1>
        <p class="subtitulo">CRUD  PHP y MySQL</p>
    </header>

    <?php if ($mensaje !== null): ?>
        <p class="aviso" role="status"><?= e($mensaje) ?></p>
    <?php endif; ?>

    <?php if ($errores !== []): ?>
        <ul class="errores" role="alert">
            <?php foreach ($errores as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2><?= $editando ? 'Editar cliente' : 'Nuevo cliente' ?></h2>
    <form class="formulario" method="post" action="index.php">
        <input type="hidden" name="csrf" value="<?= e($token) ?>">
        <input type="hidden" name="accion" value="<?= e($accion) ?>">
        <input type="hidden" name="id" value="<?= (int) ($datos['id'] ?? 0) ?>">

        <div class="campo">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= e($datos['nombre'] ?? '') ?>" required>
        </div>
        <div class="campo">
            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" value="<?= e($datos['apellido'] ?? '') ?>" required>
        </div>
        <div class="campo">
            <label for="cedula">Cédula (10 dígitos)</label>
            <input type="text" id="cedula" name="cedula" inputmode="numeric" maxlength="10"
                   value="<?= e($datos['cedula'] ?? '') ?>" required>
        </div>
        <div class="campo">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" value="<?= e($datos['email'] ?? '') ?>" required>
        </div>
        <div class="campo">
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" inputmode="numeric" maxlength="10"
                   value="<?= e($datos['telefono'] ?? '') ?>" required>
        </div>
        <div class="campo">
            <label for="direccion">Dirección <span class="opcional">(opcional)</span></label>
            <input type="text" id="direccion" name="direccion" value="<?= e($datos['direccion'] ?? '') ?>">
        </div>

        <div class="acciones">
            <button type="submit" class="boton boton--primario">
                <?= $editando ? 'Guardar cambios' : 'Registrar cliente' ?>
            </button>
            <?php if ($editando): ?>
                <a class="boton boton--secundario" href="index.php">Cancelar</a>
            <?php endif; ?>
        </div>
    </form>

    <h2>Clientes registrados (<?= count($clientes) ?>)</h2>
    <form class="busqueda" method="get" action="index.php">
        <input type="search" name="q" value="<?= e($busqueda) ?>" placeholder="Buscar por nombre, apellido o cédula">
        <button type="submit" class="boton boton--secundario">Buscar</button>
        <?php if ($busqueda !== ''): ?>
            <a href="index.php">Limpiar</a>
        <?php endif; ?>
    </form>

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>ID</th><th>Nombre</th><th>Cédula</th><th>Correo</th>
                    <th>Teléfono</th><th>Dirección</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($clientes === []): ?>
                <tr><td colspan="7" class="vacio">No hay clientes para mostrar.</td></tr>
            <?php endif; ?>
            <?php foreach ($clientes as $c): ?>
                <tr>
                    <td><?= (int) $c->getId() ?></td>
                    <td><?= e($c->getNombreCompleto()) ?></td>
                    <td><?= e($c->getCedula()) ?></td>
                    <td><?= e($c->getEmail()) ?></td>
                    <td><?= e($c->getTelefono()) ?></td>
                    <td><?= e($c->getDireccion() === '' ? '—' : $c->getDireccion()) ?></td>
                    <td class="acciones-fila">
                        <a href="index.php?editar=<?= (int) $c->getId() ?>">Editar</a>
                        <form method="post" action="index.php"
                              onsubmit="return confirm('¿Eliminar a este cliente?');">
                            <input type="hidden" name="csrf" value="<?= e($token) ?>">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= (int) $c->getId() ?>">
                            <button type="submit" class="enlace-peligro">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
