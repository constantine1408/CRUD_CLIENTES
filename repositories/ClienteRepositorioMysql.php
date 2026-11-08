<?php

declare(strict_types=1);

/**
 * Implementación MySQL/MariaDB del CRUD. Recibe y devuelve objetos Cliente;
 * todas las consultas son preparadas (protección contra inyección SQL).
 */
class ClienteRepositorioMysql implements RepositorioClientes
{
    private const COLUMNAS = 'id, nombre, apellido, cedula, email, telefono, direccion';
    private const ERROR_DUPLICADO = 1062;

    public function __construct(private mysqli $conexion) {}

    // ---------- CREATE
    public function crear(Cliente $cliente): Cliente
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO clientes (nombre, apellido, cedula, email, telefono, direccion)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $nombre = $cliente->getNombre();
        $apellido = $cliente->getApellido();
        $cedula = $cliente->getCedula();
        $email = $cliente->getEmail();
        $telefono = $cliente->getTelefono();
        $direccion = $cliente->getDireccion();

        $stmt->bind_param('ssssss', $nombre, $apellido, $cedula, $email, $telefono, $direccion);

        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            throw $this->traducir($e, $cedula);
        } finally {
            $stmt->close();
        }

        $cliente->setId((int) $this->conexion->insert_id);

        return $cliente;
    }

    // ---- READ
    public function obtenerPorId(int $id): ?Cliente
    {
        $stmt = $this->conexion->prepare('SELECT ' . self::COLUMNAS . ' FROM clientes WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $fila === null ? null : $this->aCliente($fila);
    }

    public function listar(): array
    {
        $stmt = $this->conexion->prepare(
            'SELECT ' . self::COLUMNAS . ' FROM clientes ORDER BY apellido, nombre'
        );
        $stmt->execute();

        return $this->aLista($stmt);
    }

    public function buscar(string $texto): array
    {
        $patron = '%' . trim($texto) . '%';

        $stmt = $this->conexion->prepare(
            'SELECT ' . self::COLUMNAS . ' FROM clientes
             WHERE nombre LIKE ? OR apellido LIKE ? OR cedula LIKE ?
             ORDER BY apellido, nombre'
        );
        $stmt->bind_param('sss', $patron, $patron, $patron);
        $stmt->execute();

        return $this->aLista($stmt);
    }

    // --- UPDATE
    public function actualizar(Cliente $cliente): bool
    {
        $id = $cliente->getId();
        if ($id === null) {
            throw new LogicException('El cliente no tiene id: debe guardarse primero con crear().');
        }

        $stmt = $this->conexion->prepare(
            'UPDATE clientes
             SET nombre = ?, apellido = ?, cedula = ?, email = ?, telefono = ?, direccion = ?
             WHERE id = ?'
        );

        $nombre = $cliente->getNombre();
        $apellido = $cliente->getApellido();
        $cedula = $cliente->getCedula();
        $email = $cliente->getEmail();
        $telefono = $cliente->getTelefono();
        $direccion = $cliente->getDireccion();

        $stmt->bind_param('ssssssi', $nombre, $apellido, $cedula, $email, $telefono, $direccion, $id);

        try {
            $stmt->execute();
            $filas = $stmt->affected_rows;
        } catch (mysqli_sql_exception $e) {
            throw $this->traducir($e, $cedula);
        } finally {
            $stmt->close();
        }

        if ($filas > 0) {
            return true;
        }

        // 0 filas = "sin cambios" o "id inexistente": se distingue consultando.
        return $this->obtenerPorId($id) !== null;
    }

    // ------ DELETE
    public function eliminar(int $id): bool
    {
        $stmt = $this->conexion->prepare('DELETE FROM clientes WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $filas = $stmt->affected_rows;
        $stmt->close();

        return $filas > 0;
    }

    // ---------- privados
    /** @return list<Cliente> */
    private function aLista(mysqli_stmt $stmt): array
    {
        $clientes = [];
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $clientes[] = $this->aCliente($fila);
        }
        $stmt->close();

        return $clientes;
    }

    /** @param array<string, mixed> $fila */
    private function aCliente(array $fila): Cliente
    {
        return new Cliente(
            nombre: (string) $fila['nombre'],
            apellido: (string) $fila['apellido'],
            cedula: (string) $fila['cedula'],
            email: (string) $fila['email'],
            telefono: (string) $fila['telefono'],
            direccion: (string) ($fila['direccion'] ?? ''),
            id: (int) $fila['id'],
        );
    }

    private function traducir(mysqli_sql_exception $e, string $cedula): Throwable
    {
        if ($e->getCode() === self::ERROR_DUPLICADO) {
            return new RuntimeException("Ya existe un cliente con la cédula $cedula.");
        }

        return $e;
    }
}
