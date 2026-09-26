<?php
session_start();
require_once "Conexion.php";

// 1. Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["id_usuario"])) {
    header("Location: Login.php");
    exit();
}

$id_lider_actual = $_SESSION["id_usuario"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibimos el ID del miembro que se quiere expulsar desde el formulario del perfil
    $id_miembro_a_echar = $_POST["id_miembro"] ?? null;

    if (!$id_miembro_a_echar) {
        header("Location: Perfil.php?error=miembro_no_encontrado");
        exit();
    }

    try {
        $pdo = Conexion::conectar();
        $pdo->beginTransaction();

        // 2. Verificar que el usuario actual realmente sea el líder (Rango == 1)
        $stmt_lider = $pdo->prepare("SELECT Rango FROM USUARIO WHERE ID_U = ?");
        $stmt_lider->execute([$id_lider_actual]);
        $datos_lider = $stmt_lider->fetch(PDO::FETCH_ASSOC);

        if (!$datos_lider || (int)$datos_lider['Rango'] !== 1) {
            $pdo->rollBack();
            header("Location: Perfil.php?error=no_autorizado");
            exit();
        }

        // 3. Obtener el ID del equipo del líder actual
        $stmt_eq = $pdo->prepare("SELECT ID_E FROM INTEGRA WHERE ID_U = ?");
        $stmt_eq->execute([$id_lider_actual]);
        $equipo_lider = $stmt_eq->fetch(PDO::FETCH_ASSOC);

        if (!$equipo_lider) {
            $pdo->rollBack();
            header("Location: Perfil.php?error=sin_equipo");
            exit();
        }
        $id_equipo = $equipo_lider['ID_E'];

        // 4. Verificar que el usuario a echar pertenezca al MISMO equipo
        $stmt_check = $pdo->prepare("SELECT ID_U FROM INTEGRA WHERE ID_U = ? AND ID_E = ?");
        $stmt_check->execute([$id_miembro_a_echar, $id_equipo]);
        
        if (!$stmt_check->fetch()) {
            $pdo->rollBack();
            header("Location: Perfil.php?error=fuera_de_equipo");
            exit();
        }

        // 5. Evitar que el líder se expulse a sí mismo por este medio
        if ((int)$id_lider_actual === (int)$id_miembro_a_echar) {
            $pdo->rollBack();
            header("Location: Perfil.php?error=auto_expulsion");
            exit();
        }

        // 6. Eliminar al miembro de la tabla INTEGRA
        $stmt_del = $pdo->prepare("DELETE FROM INTEGRA WHERE ID_U = ? AND ID_E = ?");
        $stmt_del->execute([$id_miembro_a_echar, $id_equipo]);

        $pdo->commit();
        header("Location: Perfil.php?exito=miembro_echado");
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