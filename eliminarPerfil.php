<?php
session_start();
require_once 'Conexion.php';

// Verificamos usando la variable exacta que definiste en auth.php
if (!isset($_SESSION["id_usuario"])) {
    header("Location: Login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];

try {
    $pdo = Conexion::conectar();
    $pdo->beginTransaction();

    // 1. Borrar dependencias en las otras tablas (por las Foreign Keys)
    $stmt = $pdo->prepare("DELETE FROM INTEGRA WHERE ID_U = ?");
    $stmt->execute([$id_usuario]);

    $stmt = $pdo->prepare("DELETE FROM JUE_PAR WHERE ID_U = ?");
    $stmt->execute([$id_usuario]);

    $stmt = $pdo->prepare("DELETE FROM ADMIN_U WHERE ID_Usu = ? OR ID_Adm = ?");
    $stmt->execute([$id_usuario, $id_usuario]);

    $stmt = $pdo->prepare("DELETE FROM ADMIN_J WHERE ID_Usu = ?");
    $stmt->execute([$id_usuario]);

    // 2. Borrar al usuario de la tabla USUARIO
    $stmt = $pdo->prepare("DELETE FROM USUARIO WHERE ID_U = ?");
    $stmt->execute([$id_usuario]);

    $pdo->commit();

    // 3. Destruir la sesión y redirigir
    $_SESSION = array();
    session_destroy();

    header("Location: Index.php?mensaje=cuenta_eliminada");
    exit();

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error al eliminar la cuenta: " . $e->getMessage();
}
?>