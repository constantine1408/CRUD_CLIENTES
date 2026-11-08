<?php

declare(strict_types=1);

/**
 * Pruebas unitarias sin base de datos (usan el repositorio en memoria).
 * Ejecutar:  php tests/run.php
 */

require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../interfaces/RepositorioClientes.php';
require_once __DIR__ . '/../repositories/ClienteRepositorioMemoria.php';

function nuevoCliente(string $cedula = '1712345678', string $nombre = 'Ana'): Cliente
{
    return new Cliente($nombre, 'Pérez', $cedula, 'ana@correo.com', '0991234567', 'Quito');
}

function lanza(callable $fn, string $clase): bool
{
    try {
        $fn();
    } catch (Throwable $e) {
        return $e instanceof $clase;
    }

    return false;
}

$tests = [
    'Cliente conserva sus datos' => function (): bool {
        $c = nuevoCliente();

        return $c->getId() === null && $c->getNombreCompleto() === 'Edwin Sanchez'
            && $c->getCedula() === '1712345678' && $c->getDireccion() === 'Quito';
    },
    'Cliente recorta espacios' => fn (): bool => (new Cliente('  Edwin ', ' Sanchez ', ' 1712345678 ', 'esanchez@inuandes.com', ' 0991234567 '))->getNombre() === 'Edwin',
    'Cliente rechaza nombre vacío' => fn (): bool => lanza(fn () => new Cliente('  ', 'P', '1712345678', 'esanchez@inuandes.com', '0991234567'), InvalidArgumentException::class),
    'Cliente rechaza cédula inválida' => fn (): bool => lanza(fn () => nuevoCliente('123'), InvalidArgumentException::class)
        && lanza(fn () => nuevoCliente('12345678ab'), InvalidArgumentException::class),
    'Cliente rechaza correo inválido' => fn (): bool => lanza(fn () => new Cliente('A', 'B', '1712345678', 'no-es-correo', '0991234567'), InvalidArgumentException::class),
    'Cliente rechaza teléfono inválido' => fn (): bool => lanza(fn () => new Cliente('A', 'B', '1712345678', 'a@b.com', '12'), InvalidArgumentException::class),
    'Los setters también validan' => function (): bool {
        $c = nuevoCliente();

        return lanza(fn () => $c->setEmail('x'), InvalidArgumentException::class);
    },
    'El repositorio cumple la interfaz' => fn (): bool => new ClienteRepositorioMemoria() instanceof RepositorioClientes,
    'crear asigna id y obtenerPorId lo recupera' => function (): bool {
        $repo = new ClienteRepositorioMemoria();
        $c = $repo->crear(nuevoCliente());

        return $c->getId() === 1 && $repo->obtenerPorId(1)?->getNombre() === 'Edwin' && $repo->obtenerPorId(99) === null;
    },
    'crear rechaza cédula duplicada' => function (): bool {
        $repo = new ClienteRepositorioMemoria();
        $repo->crear(nuevoCliente());

        return lanza(fn () => $repo->crear(nuevoCliente()), RuntimeException::class);
    },
    'listar ordena por apellido y nombre' => function (): bool {
        $repo = new ClienteRepositorioMemoria();
        $repo->crear(new Cliente('Luis', 'Zambrano', '1000000001', 'l@c.com', '0991111111'));
        $repo->crear(new Cliente('Edwin', 'Sanchez', '1000000002', 'esanchez@inuandes.com', '0992222222'));
        $nombres = array_map(fn (Cliente $c) => $c->getNombre(), $repo->listar());

        return $nombres === ['Ana', 'Luis'];
    },
    'buscar por nombre, apellido o cédula' => function (): bool {
        $repo = new ClienteRepositorioMemoria();
        $repo->crear(nuevoCliente('1712345678', 'Edwin'));
        $repo->crear(nuevoCliente('1712345679', 'Luis'));

        return count($repo->buscar('luis')) === 1 && count($repo->buscar('pérez')) === 2
            && count($repo->buscar('45679')) === 1 && $repo->buscar('zzz') === [];
    },
    'actualizar modifica un cliente existente' => function (): bool {
        $repo = new ClienteRepositorioMemoria();
        $c = $repo->crear(nuevoCliente());
        $c->setTelefono('0987654321');

        return $repo->actualizar($c) && $repo->obtenerPorId(1)?->getTelefono() === '0987654321';
    },
    'actualizar devuelve false si el id no existe' => function (): bool {
        $repo = new ClienteRepositorioMemoria();
        $fantasma = new Cliente('A', 'B', '1712345678', 'a@b.com', '0991234567', '', 50);

        return $repo->actualizar($fantasma) === false;
    },
    'actualizar rechaza la cédula de otro cliente' => function (): bool {
        $repo = new ClienteRepositorioMemoria();
        $repo->crear(nuevoCliente('1712345678'));
        $otro = $repo->crear(nuevoCliente('1712345679', 'Luis'));
        $otro->setCedula('1712345678');

        return lanza(fn () => $repo->actualizar($otro), RuntimeException::class);
    },
    'eliminar borra y devuelve false si no existe' => function (): bool {
        $repo = new ClienteRepositorioMemoria();
        $repo->crear(nuevoCliente());

        return $repo->eliminar(1) === true && $repo->listar() === [] && $repo->eliminar(1) === false;
    },
];

$fallidas = 0;
foreach ($tests as $nombre => $test) {
    $ok = false;
    try {
        $ok = $test();
    } catch (Throwable $e) {
        echo "  (excepción: {$e->getMessage()})\n";
    }
    echo ($ok ? '✔' : '✘') . " $nombre\n";
    $fallidas += $ok ? 0 : 1;
}

echo "\n" . (count($tests) - $fallidas) . '/' . count($tests) . " pruebas correctas\n";
exit($fallidas === 0 ? 0 : 1);
