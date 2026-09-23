<?php
require_once "Conexion.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: Registro.php");
    exit;
}
$usuario = $_POST["usuario"] ?? "";
$correo = $_POST["correo"] ?? "";
$password = $_POST["password"] ?? "";
if (empty($usuario) || empty($correo) || empty($password)) {
    header("Location: Registro.php?error=campos");
    exit;
}
try {
    $pdo = Conexion::conectar();
    // Comenzar transacción
    $pdo->beginTransaction();
    // 1. Crear usuario
    $sql = "INSERT INTO USUARIO
            (Nombre_Usuario, Correo_Electronico, Contrasena)
            VALUES
            (:usuario, :correo, :password)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":usuario" => $usuario,
        ":correo" => $correo,
        ":password" => $password
    ]);
    // Obtener el ID generado
    $idUsuario = $pdo->lastInsertId();
    // Confirmar cambios
    $pdo->commit();
    // Ir al login
    header("Location: Login.php?registro=ok");
    exit;
} catch (PDOException $e) {
    // Si algo salió mal, deshacer cambios
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERROR: " . $e->getMessage();
    exit;
}