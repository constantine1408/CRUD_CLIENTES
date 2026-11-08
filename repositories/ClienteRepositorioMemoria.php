<?php

declare(strict_types=1);

/**
 * Implementación en memoria del contrato RepositorioClientes.
 * Se usa en las pruebas (tests/run.php) para no depender de una base de datos.
 */
class ClienteRepositorioMemoria implements RepositorioClientes
{
    /** @var array<int, Cliente> */
    private array $clientes = [];
    private int $siguienteId = 1;

    public function crear(Cliente $cliente): Cliente
    {
        $this->validarCedulaUnica($cliente->getCedula(), null);
        $cliente->setId($this->siguienteId++);
        $this->clientes[$cliente->getId()] = $cliente;

        return $cliente;
    }

    public function obtenerPorId(int $id): ?Cliente
    {
        return $this->clientes[$id] ?? null;
    }

    public function listar(): array
    {
        $lista = array_values($this->clientes);
        usort($lista, fn (Cliente $a, Cliente $b) => [$this->clave($a->getApellido()), $this->clave($a->getNombre())]
            <=> [$this->clave($b->getApellido()), $this->clave($b->getNombre())]);

        return $lista;
    }

    /** Clave de orden sin tildes ni mayúsculas, como la collation utf8mb4_unicode_ci de MySQL. */
    private function clave(string $texto): string
    {
        return strtr(mb_strtolower($texto), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);
    }

    public function buscar(string $texto): array
    {
        $texto = mb_strtolower(trim($texto));

        return array_values(array_filter(
            $this->listar(),
            fn (Cliente $c) => str_contains(mb_strtolower($c->getNombre()), $texto)
                || str_contains(mb_strtolower($c->getApellido()), $texto)
                || str_contains($c->getCedula(), $texto)
        ));
    }

    public function actualizar(Cliente $cliente): bool
    {
        $id = $cliente->getId();
        if ($id === null || !isset($this->clientes[$id])) {
            return false;
        }
        $this->validarCedulaUnica($cliente->getCedula(), $id);
        $this->clientes[$id] = $cliente;

        return true;
    }

    public function eliminar(int $id): bool
    {
        if (!isset($this->clientes[$id])) {
            return false;
        }
        unset($this->clientes[$id]);

        return true;
    }

    private function validarCedulaUnica(string $cedula, ?int $idPropio): void
    {
        foreach ($this->clientes as $existente) {
            if ($existente->getCedula() === $cedula && $existente->getId() !== $idPropio) {
                throw new RuntimeException("Ya existe un cliente con la cédula $cedula.");
            }
        }
    }
}
