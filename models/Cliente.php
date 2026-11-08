<?php

declare(strict_types=1);

/**
 *  Cliente.
 *
 * Los atributos son privados y solo se modifican mediante
 * setters que validan el dato. Si el dato no es válido se lanza
 * InvalidArgumentException, de modo que nunca existe un Cliente inválido.
 */
class Cliente
{
    private ?int $id;
    private string $nombre;
    private string $apellido;
    private string $cedula;
    private string $email;
    private string $telefono;
    private string $direccion;

    public function __construct(
        string $nombre,
        string $apellido,
        string $cedula,
        string $email,
        string $telefono,
        string $direccion = '',
        ?int $id = null,
    ) {
        $this->id = $id;
        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setCedula($cedula);
        $this->setEmail($email);
        $this->setTelefono($telefono);
        $this->setDireccion($direccion);
    }

    // ---- getters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function getCedula(): string
    {
        return $this->cedula;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTelefono(): string
    {
        return $this->telefono;
    }

    public function getDireccion(): string
    {
        return $this->direccion;
    }

    public function getNombreCompleto(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    // ------ setters

    /** Lo asigna el repositorio al guardar el cliente por primera vez. */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setNombre(string $nombre): void
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre no puede estar vacío.');
        }
        $this->nombre = $nombre;
    }

    public function setApellido(string $apellido): void
    {
        $apellido = trim($apellido);
        if ($apellido === '') {
            throw new InvalidArgumentException('El apellido no puede estar vacío.');
        }
        $this->apellido = $apellido;
    }

    public function setCedula(string $cedula): void
    {
        $cedula = trim($cedula);
        if (preg_match('/^\d{10}$/', $cedula) !== 1) {
            throw new InvalidArgumentException('La cédula debe tener 10 dígitos numéricos.');
        }
        $this->cedula = $cedula;
    }

    public function setEmail(string $email): void
    {
        $email = trim($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('El correo electrónico no es válido.');
        }
        $this->email = $email;
    }

    public function setTelefono(string $telefono): void
    {
        $telefono = trim($telefono);
        if (preg_match('/^\d{7,10}$/', $telefono) !== 1) {
            throw new InvalidArgumentException('El teléfono debe tener entre 7 y 10 dígitos.');
        }
        $this->telefono = $telefono;
    }

    public function setDireccion(string $direccion): void
    {
        $this->direccion = trim($direccion);
    }
}
