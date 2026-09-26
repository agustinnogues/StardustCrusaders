<?php
session_start();
require_once "Conexion.php";

// 1. Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["id_usuario"])) {
    header("Location: Login.php");
    exit();
}

$id_usuario_actual = $_SESSION["id_usuario"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_invitado = trim($_POST["nombre_invitado"] ?? "");

    if (empty($nombre_invitado)) {
        header("Location: Perfil.php?invitacion=no_encontrado");
        exit();
    }

    try {
        $pdo = Conexion::conectar();
        $pdo->beginTransaction();

        // 2. Verificar que el usuario actual realmente sea el líder (Rango == 1)
        $stmt_lider = $pdo->prepare("SELECT Rango FROM USUARIO WHERE ID_U = ?");
        $stmt_lider->execute([$id_usuario_actual]);
        $datos_lider = $stmt_lider->fetch(PDO::FETCH_ASSOC);

        if (!$datos_lider || $datos_lider['Rango'] != 1) {
            header("Location: Perfil.php");
            exit();
        }

        // 3. Obtener el ID del equipo del líder actual
        $stmt_eq = $pdo->prepare("SELECT ID_E FROM INTEGRA WHERE ID_U = ?");
        $stmt_eq->execute([$id_usuario_actual]);
        $equipo_lider = $stmt_eq->fetch(PDO::FETCH_ASSOC);

        if (!$equipo_lider) {
            header("Location: Perfil.php");
            exit();
        }
        $id_equipo = $equipo_lider['ID_E'];

        // 4. Buscar al usuario invitado por su nombre de usuario
        $stmt_user = $pdo->prepare("SELECT ID_U, Nombre_Usuario FROM USUARIO WHERE Nombre_Usuario = ?");
        $stmt_user->execute([$nombre_invitado]);
        $usuario_invitado = $stmt_user->fetch(PDO::FETCH_ASSOC);

        // Si el usuario no existe
        if (!$usuario_invitado) {
            header("Location: Perfil.php?invitacion=no_encontrado");
            exit();
        }

        $id_invitado = $usuario_invitado['ID_U'];

        // Si intenta invitarse a sí mismo
        if ($id_invitado == $id_usuario_actual) {
            header("Location: Perfil.php?invitacion=auto_invitacion");
            exit();
        }

        // 5. Verificar si el usuario invitado ya pertenece a un equipo
        $stmt_check = $pdo->prepare("SELECT ID_E FROM INTEGRA WHERE ID_U = ?");
        $stmt_check->execute([$id_invitado]);
        if ($stmt_check->fetch()) {
            header("Location: Perfil.php?invitacion=ya_tiene_equipo");
            exit();
        }

        // 6. Insertar al usuario invitado directamente en la tabla INTEGRA
        $stmt_insert = $pdo->prepare("INSERT INTO INTEGRA (ID_U, ID_E) VALUES (?, ?)");
        $stmt_insert->execute([$id_invitado, $id_equipo]);

        $pdo->commit();
        header("Location: Perfil.php?invitacion=exito");
        exit();

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: Perfil.php?invitacion=error");
        exit();
    }
} else {
    header("Location: Perfil.php");
    exit();
}
?>