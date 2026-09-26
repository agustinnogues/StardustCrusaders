<?php
session_start();
require_once "Conexion.php";

// 1. Verificar sesión
if (!isset($_SESSION["id_usuario"])) {
    header("Location: Login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$mensaje_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_equipo = trim($_POST["nombre_equipo"] ?? "");

    if (!empty($nombre_equipo)) {
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();

            // A. Verificar si el usuario ya pertenece a un equipo
            $stmt_check = $pdo->prepare("SELECT ID_E FROM INTEGRA WHERE ID_U = ?");
            $stmt_check->execute([$id_usuario]);
            if ($stmt_check->fetch()) {
                throw new Exception("Ya formas parte de un equipo. Debes salir del actual antes de crear uno nuevo.");
            }

            // B. Insertar el nuevo equipo
            $stmt_eq = $pdo->prepare("INSERT INTO EQUIPO (Nombre_Equipo) VALUES (?)");
            $stmt_eq->execute([$nombre_equipo]);
            $id_equipo = $pdo->lastInsertId();

            // C. Asociar al usuario al equipo en INTEGRA
            $stmt_int = $pdo->prepare("INSERT INTO INTEGRA (ID_U, ID_E) VALUES (?, ?)");
            $stmt_int->execute([$id_usuario, $id_equipo]);

            // D. Actualizar el Rango del usuario a TRUE (1) para indicar que es el líder
            $stmt_rango = $pdo->prepare("UPDATE USUARIO SET Rango = 1 WHERE ID_U = ?");
            $stmt_rango->execute([$id_usuario]);

            $pdo->commit();

            // Redirigir al perfil con éxito
            header("Location: Perfil.php?exito=equipo_creado");
            exit();

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensaje_error = $e->getMessage();
        }
    } else {
        $mensaje_error = "El nombre del equipo no puede estar vacío.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Equipo - Stardust Crusaders</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<?php include("includes/header.php"); ?>

<section class="pagina">
    <div class="perfilContainer" style="max-width: 600px; margin: 0 auto; display: block;">
        <div class="tarjeta">
            <h3>🛡️ Crear un Nuevo Equipo</h3>
            
            <?php if (!empty($mensaje_error)): ?>
                <div style="background-color: #f2dede; color: #a94442; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                    <?php echo htmlspecialchars($mensaje_error); ?>
                </div>
            <?php endif; ?>

            <form action="CrearEquipo.php" method="POST">
                <div style="margin-bottom: 15px;">
                    <label for="nombre_equipo" style="display: block; margin-bottom: 5px; font-weight: bold;">Nombre del Equipo:</label>
                    <input type="text" id="nombre_equipo" name="nombre_equipo" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <button type="submit" style="background-color: #5cb85c; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; width: 100%;">
                    Crear Equipo y ser Líder
                </button>
            </form>

            <div style="margin-top: 15px; text-align: center;">
                <a href="Perfil.php" style="color: #337ab7; text-decoration: none;">Volver al Perfil</a>
            </div>
        </div>
    </div>
</section>

<?php include("includes/footer.php"); ?>
<script src="Js/scripts.js"></script>
</body>
</html>