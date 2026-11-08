-- Base de datos y tabla del CRUD de clientes.
-- Importar desde phpMyAdmin (pestaña "Importar") o con:  mysql -u root -p < sql/esquema.sql

CREATE DATABASE IF NOT EXISTS crud_clientes
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE crud_clientes;

CREATE TABLE IF NOT EXISTS clientes (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre    VARCHAR(80)  NOT NULL,
    apellido  VARCHAR(80)  NOT NULL,
    cedula    CHAR(10)     NOT NULL,
    email     VARCHAR(150) NOT NULL,
    telefono  VARCHAR(10)  NOT NULL,
    direccion VARCHAR(200) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    UNIQUE KEY uq_clientes_cedula (cedula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
