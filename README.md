# SEMANA 2

# CRUD de Clientes — PHP Y MySQL

Tarea: _crear el objeto Cliente y usarlo para crear los métodos del CRUD, con POO._

## Estructura y conceptos de POO

```text
crud-clientes-php/
├── index.php                              # Entrada: arma las piezas y llama al controlador
├── models/Cliente.php                     # Entidad: atributos privados + getters/setters con validación
├── interfaces/RepositorioClientes.php     # Contrato del CRUD (abstracción)
├── repositories/ClienteRepositorioMysql.php    # CRUD real contra MySQL/MariaDB
├── repositories/ClienteRepositorioMemoria.php  # Implementación para pruebas (sin BD)
├── controllers/ClienteController.php      # Recibe las peticiones y usa el repositorio
├── views/clientes.php                     # Formulario y tabla (salida escapada)
├── config/database.php                    # Conexión por variables de entorno
├── sql/esquema.sql                        # Base de datos y tabla
├── tests/run.php                          # Pruebas unitarias
├── style.css, Dockerfile, compose.yaml
```

| Concepto                  | Dónde                                                                                 |
| ------------------------- | ------------------------------------------------------------------------------------- |
| Clase y objeto            | `Cliente` (cada fila de la tabla es un objeto `Cliente`)                              |
| Encapsulación             | Atributos `private`; se modifican solo con setters que validan                        |
| Abstracción / interfaz    | `RepositorioClientes` define el CRUD                                                  |
| Polimorfismo              | El controlador funciona con cualquier implementación de la interfaz (MySQL o memoria) |
| Inyección de dependencias | `new ClienteController(new ClienteRepositorioMysql($conexion))`                       |

## Métodos del CRUD (`RepositorioClientes`)

| Operación | Método                                                       |
| --------- | ------------------------------------------------------------ |
| Create    | `crear(Cliente $cliente): Cliente`                           |
| Read      | `obtenerPorId(int $id)`, `listar()`, `buscar(string $texto)` |
| Update    | `actualizar(Cliente $cliente): bool`                         |
| Delete    | `eliminar(int $id): bool`                                    |

Seguridad incluida: consultas preparadas (inyección SQL), salida con `htmlspecialchars` (XSS) y token CSRF en los formularios.

## Ejecutar

### Opción A — XAMPP / WAMP

1. Copiar la carpeta a `htdocs/`.
2. Abrir **phpMyAdmin → Importar** y cargar `sql/esquema.sql`.
3. Abrir `http://localhost/crud-clientes-php/`.

Por defecto conecta a `localhost`, usuario `root`, sin clave, base `crud_clientes`. Si su configuración es distinta, cambiar los valores por defecto en `config/database.php` o definir las variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.

### Opción B — Docker

```bash
docker compose up --build -d
```

Abrir `http://localhost:8080`. Detener con `docker compose down` (los datos se conservan en el volumen).

## Pruebas

```bash
php tests/run.php
```

Cubren la entidad (validaciones) y el CRUD completo sobre el repositorio en memoria.
