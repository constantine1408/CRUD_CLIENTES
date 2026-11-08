<?php

declare(strict_types=1);

/**
 * Controlador del CRUD de clientes.
 *
 *  - GET            → lista (opcionalmente filtrada con ?q=) o formulario de edición (?editar=id)
 *  - POST accion=crear | actualizar | eliminar
 *
 * Tras una operación exitosa redirige al listado (patrón Post/Redirect/Get),
 * así recargar la página no repite el envío del formulario.
 */
class ClienteController
{
    private const MENSAJES = [
        'creado' => 'Cliente creado correctamente.',
        'actualizado' => 'Cliente actualizado correctamente.',
        'eliminado' => 'Cliente eliminado correctamente.',
    ];

    public function __construct(private RepositorioClientes $repositorio) {}

    public function index(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->procesarPost();
            return;
        }

        $errores = [];
        $datos = [];

        if (array_key_exists('editar', $_GET)) {
            $id = $this->idValido($_GET['editar']);
            $cliente = $id === null ? null : $this->repositorio->obtenerPorId($id);

            if ($cliente === null) {
                $errores[] = 'No se encontró el cliente a editar.';
            } else {
                $datos = $this->aDatos($cliente);
            }
        }

        $this->mostrar($errores, $datos);
    }

    private function procesarPost(): void
    {
        if (!$this->tokenValido()) {
            $this->mostrar(['La solicitud no es válida. Recargue la página e intente de nuevo.']);
            return;
        }

        $accion = (string) ($_POST['accion'] ?? '');
        $id = $this->idValido($_POST['id'] ?? null);

        try {
            switch ($accion) {
                case 'crear':
                    $this->repositorio->crear($this->clienteDesdePost(null));
                    $this->redirigir('creado');
                    // no continúa: redirigir() termina la ejecución

                case 'actualizar':
                    if ($id === null) {
                        throw new InvalidArgumentException('El identificador del cliente no es válido.');
                    }
                    if (!$this->repositorio->actualizar($this->clienteDesdePost($id))) {
                        throw new RuntimeException('No se encontró el cliente a actualizar.');
                    }
                    $this->redirigir('actualizado');

                case 'eliminar':
                    if ($id === null || !$this->repositorio->eliminar($id)) {
                        throw new RuntimeException('No se encontró el cliente a eliminar.');
                    }
                    $this->redirigir('eliminado');

                default:
                    throw new InvalidArgumentException('La acción solicitada no es válida.');
            }
        } catch (InvalidArgumentException | RuntimeException $e) {
            // Se vuelve a mostrar el formulario con lo que escribió el usuario.
            $datos = [
                'id' => $id ?? 0,
                'nombre' => (string) ($_POST['nombre'] ?? ''),
                'apellido' => (string) ($_POST['apellido'] ?? ''),
                'cedula' => (string) ($_POST['cedula'] ?? ''),
                'email' => (string) ($_POST['email'] ?? ''),
                'telefono' => (string) ($_POST['telefono'] ?? ''),
                'direccion' => (string) ($_POST['direccion'] ?? ''),
            ];
            $this->mostrar([$e->getMessage()], $accion === 'eliminar' ? [] : $datos);
        }
    }

    /** Construye el objeto Cliente con los datos del formulario (valida al construirse). */
    private function clienteDesdePost(?int $id): Cliente
    {
        return new Cliente(
            nombre: (string) ($_POST['nombre'] ?? ''),
            apellido: (string) ($_POST['apellido'] ?? ''),
            cedula: (string) ($_POST['cedula'] ?? ''),
            email: (string) ($_POST['email'] ?? ''),
            telefono: (string) ($_POST['telefono'] ?? ''),
            direccion: (string) ($_POST['direccion'] ?? ''),
            id: $id,
        );
    }

    /** @return array<string, mixed> */
    private function aDatos(Cliente $c): array
    {
        return [
            'id' => $c->getId(),
            'nombre' => $c->getNombre(),
            'apellido' => $c->getApellido(),
            'cedula' => $c->getCedula(),
            'email' => $c->getEmail(),
            'telefono' => $c->getTelefono(),
            'direccion' => $c->getDireccion(),
        ];
    }

    /** Devuelve un entero positivo o null si el valor no lo es ("abc", "1foo", 0, -1...). */
    private function idValido(mixed $valor): ?int
    {
        $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }

    private function tokenValido(): bool
    {
        $enviado = (string) ($_POST['csrf'] ?? '');

        return $enviado !== '' && hash_equals((string) ($_SESSION['csrf'] ?? ''), $enviado);
    }

    private function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    /**
     * @param list<string>         $errores
     * @param array<string, mixed> $datos   vacío = formulario de alta
     */
    private function mostrar(array $errores = [], array $datos = []): void
    {
        $busqueda = trim((string) ($_GET['q'] ?? ''));
        $clientes = $busqueda === '' ? $this->repositorio->listar() : $this->repositorio->buscar($busqueda);
        $accion = ((int) ($datos['id'] ?? 0)) > 0 ? 'actualizar' : 'crear';
        $mensaje = self::MENSAJES[(string) ($_GET['msg'] ?? '')] ?? null;
        $token = $this->token();

        require __DIR__ . '/../views/clientes.php';
    }

    private function redirigir(string $mensaje): never
    {
        header('Location: index.php?msg=' . urlencode($mensaje));
        exit;
    }
}
