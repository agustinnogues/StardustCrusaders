<?php
session_start();
require_once "Conexion.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}
$usuario = $_POST["usuario"] ?? "";
$password = $_POST["password"] ?? "";
try {
    $pdo = Conexion::conectar();
    $sql = "SELECT ID_U, Nombre_Usuario, Correo_Electronico, Contrasena
            FROM USUARIO
            WHERE Nombre_Usuario = :usuario
            AND Contrasena = :password";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":usuario" => $usuario,
        ":password" => $password
    ]);
    $usuarioBD = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuarioBD) {
        $_SESSION["id_usuario"] = $usuarioBD["ID_U"];
        $_SESSION["nombre_usuario"] = $usuarioBD["Nombre_Usuario"];
        $_SESSION["correo"] = $usuarioBD["Correo_Electronico"];
        header("Location: Index.php");
        exit;
    } else {
        header("Location: Login.php?error=credenciales");
        exit;
    }
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
    exit;
}