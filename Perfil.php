<?php
session_start();
// Verificar que haya iniciado sesión
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}
require_once "Conexion.php";
try {
    $pdo = Conexion::conectar();
    // Buscar los datos del usuario
    $sql = "SELECT ID_U, Nombre_Usuario, Correo_Electronico, Rol, Rango
            FROM USUARIO
            WHERE ID_U = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":id" => $_SESSION["id_usuario"]
    ]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Stardust Crusaders</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<?php include("includes/header.php"); ?>
<section class="pagina">
    <div class="perfilContainer">
        <!-- INFORMACIÓN DEL USUARIO -->
        <div class="perfilIzquierda">
            <img
                src="https://i.pravatar.cc/200"
                class="fotoPerfil"
                alt="Foto de perfil"
            >
            <h2>
                <?php echo htmlspecialchars($usuario["Nombre_Usuario"]); ?>
            </h2>
            <p>
                ID: <?php echo $usuario["ID_U"]; ?>
            </p>
            <p>
                <?php echo htmlspecialchars($usuario["Correo_Electronico"]); ?>
            </p>
            <?php if ($usuario["Rol"] == 1): ?>
                <span class="admin">👑 Administrador</span>
            <?php endif; ?>
        </div>
        <div class="perfilDerecha">
            <div class="tarjeta">
                <h3>🎮 Juegos recientes</h3>
                <ul>
                    <li>Adivina la Bandera</li>
                    <li>Snake</li>
                    <li>Memoria</li>
                    <li>Tetris</li>
                </ul>
            </div>
            <div class="tarjeta">
                <h3>🏆 Puntuaciones</h3>
                <table>
                    <tr>
                        <th>Juego</th>
                        <th>Puntos</th>
                    </tr>
                    <tr>
                        <td>Snake</td>
                        <td>560</td>
                    </tr>
                    <tr>
                        <td>Memoria</td>
                        <td>320</td>
                    </tr>
                    <tr>
                        <td>Adivina la Bandera</td>
                        <td>920</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include("includes/footer.php"); ?>
<script src="Js/scripts.js"></script>
</body>
</html>