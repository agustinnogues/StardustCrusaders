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

        // 1. Obtener datos del usuario (su Rango) y su equipo actual
        $stmt_user = $pdo->prepare("SELECT Rango FROM USUARIO WHERE ID_U = ?");
        $stmt_user->execute([$id_usuario]);
        $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

        $stmt_integra = $pdo->prepare("SELECT ID_E FROM INTEGRA WHERE ID_U = ?");
        $stmt_integra->execute([$id_usuario]);
        $integracion = $stmt_integra->fetch(PDO::FETCH_ASSOC);

        if (!$integracion) {
            $pdo->rollBack();
            header("Location: Perfil.php");
            exit();
        }

        $id_equipo = $integracion['ID_E'];
        $es_lider = (isset($user_data['Rango']) && (int)$user_data['Rango'] === 1);

        // 2. Contar cuántos miembros hay en total en ese equipo
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM INTEGRA WHERE ID_E = ?");
        $stmt_count->execute([$id_equipo]);
        $total_miembros = (int)$stmt_count->fetchColumn();

        // 3. REGLA ESTRICTA: Si es líder Y es el único miembro en el equipo (total_miembros <= 1), 
        // ¡NO LO DEJES SALIR! Para evitar que el equipo quede vacío u huérfano.
        if ($es_lider && $total_miembros <= 1) {
            $pdo->rollBack();
            header("Location: Perfil.php?error=lider_con_miembros"); // O puedes usar otro mensaje si prefieres
            exit();
        }

        // 4. Si hay más gente en el equipo, el líder sí puede irse (pero antes de irse, 
        // idealmente deberías asegurar que otro asuma el liderazgo, o simplemente lo saca a él de INTEGRA).
        $stmt_del = $pdo->prepare("DELETE FROM INTEGRA WHERE ID_U = ? AND ID_E = ?");
        $stmt_del->execute([$id_usuario, $id_equipo]);

        // 5. Devolver su Rango a 0 (usuario normal) ya que dejó el equipo/liderazgo
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