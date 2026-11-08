<?php

declare(strict_types=1);

/**
 * Contrato del CRUD de clientes.
 *
 * El controlador depende de esta interfaz, no de MySQL: se puede cambiar el
 * almacenamiento (MySQL, memoria para pruebas, etc.) sin tocar el controlador.
 */
interface RepositorioClientes
{
    /**
     * CREATE. Guarda el cliente y le asigna su id.
     *
     * @throws RuntimeException si ya existe un cliente con esa cédula
     */
    public function crear(Cliente $cliente): Cliente;

    /** READ. Devuelve el cliente con ese id o null si no existe. */
    public function obtenerPorId(int $id): ?Cliente;

    /**
     * READ. Todos los clientes ordenados por apellido y nombre.
     *
     * @return list<Cliente>
     */
    public function listar(): array;

    /**
     * READ. Clientes cuyo nombre, apellido o cédula contienen el texto.
     *
     * @return list<Cliente>
     */
    public function buscar(string $texto): array;

    /**
     * UPDATE. Sobrescribe los datos de un cliente existente.
     *
     * @return bool false si el id no existe
     * @throws RuntimeException si la cédula ya pertenece a otro cliente
     */
    public function actualizar(Cliente $cliente): bool;

    /**
     * DELETE.
     *
     * @return bool false si el id no existe
     */
    public function eliminar(int $id): bool;
}
