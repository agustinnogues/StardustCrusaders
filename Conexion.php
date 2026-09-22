<?php
require_once "Configuraciones.php";
class Conexion
{
    public static function conectar()
    {
        try {
            $pdo = new PDO(
                "mysql:host=" . HOST . ";dbname=" . BD . ";charset=utf8",
                USUARIO,
                PASSWORD
            );
            $pdo->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
            return $pdo;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>