<?php
require_once "Configuraciones.php";
try {
    // Conectarse al servidor MySQL
    $pdo = new PDO(
        "mysql:host=" . HOST . ";charset=utf8",
        USUARIO,
        PASSWORD
    );
    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );
    // 1. Crear la base de datos y las tablas
    $sql = file_get_contents(
        __DIR__ . "/crearbase.sql"
    );
    $pdo->exec($sql);
    // 2. Cargar los datos iniciales
    $sqlDatos = file_get_contents(
    __DIR__ . "/datoscargados.sql"
    );
    $pdo->exec($sqlDatos);
} catch (PDOException $e) {
    echo "Error durante la instalación: "
     . $e->getMessage();
}
?>