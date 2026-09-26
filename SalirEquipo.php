<?php
session_start();
require_once "Conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: Login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = Conexion::conectar();
        $pdo->beginTransaction();

        // 1. Obtener datos del usuario (incluyendo su Rango/Líder) y su equipo actual
        $stmt_user = $pdo->prepare("SELECT Rango FROM USUARIO WHERE ID_U = ?");
        $stmt_user->execute([$id_usuario]);
        $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

        $stmt_integra = $pdo->prepare("SELECT ID_E FROM INTEGRA WHERE ID_U = ?");
        $stmt_integra->execute([$id_usuario]);
        $integracion = $stmt_integra->fetch(PDO::FETCH_ASSOC);

        if (!$integracion) {
            header("Location: Perfil.php");
            exit();
        }

        $id_equipo = $integracion['ID_E'];
        $es_lider = ($user_data['Rango'] == 1); // 1 significa que es el líder

        // 2. Si es líder, verificar si hay más integrantes en el equipo
        if ($es_lider) {
            $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM INTEGRA WHERE ID_E = ?");
            $stmt_count->execute([$id_equipo]);
            $total_miembros = $stmt_count->fetchColumn();

            // Si hay más miembros además del líder, no puede salir así nada más
            if ($total_miembros > 1) {
                header("Location: Perfil.php?error=lider_con_miembros");
                exit();
            }
        }

        // 3. Eliminar al usuario de la tabla INTEGRA
        $stmt_del = $pdo->prepare("DELETE FROM INTEGRA WHERE ID_U = ?");
        $stmt_del->execute([$id_usuario]);

        // 4. Si era líder, devolver su Rango a 0 (usuario normal)
        if ($es_lider) {
            $stmt_upd = $pdo->prepare("UPDATE USUARIO SET Rango = 0 WHERE ID_U = ?");
            $stmt_upd->execute([$id_usuario]);
        }

        $pdo->commit();
        header("Location: Perfil.php?exito=salida_exitosa");
        exit();

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: Perfil.php?error=db_error");
        exit();
    }
} else {
    header("Location: Perfil.php");
    exit();
}
?>