<?php
session_start();
require_once "Conexion.php";

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["id_usuario"])) {
    header("Location: Login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$usuario = [];
$mi_equipo = null;
$integrantes = [];

try {
    $pdo = Conexion::conectar();

    // 1. Obtener datos del usuario actual (incluyendo su Rango)
    $stmt = $pdo->prepare("SELECT ID_U, Nombre_Usuario, Correo_Electronico, Rol, Rango FROM USUARIO WHERE ID_U = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Obtener datos del equipo del usuario actual mediante la tabla INTEGRA
    $sql_equipo = "SELECT E.ID_E, E.Nombre_Equipo FROM INTEGRA I 
                   JOIN EQUIPO E ON I.ID_E = E.ID_E 
                   WHERE I.ID_U = ?";
    $stmt_eq = $pdo->prepare($sql_equipo);
    $stmt_eq->execute([$id_usuario]);
    $mi_equipo = $stmt_eq->fetch(PDO::FETCH_ASSOC);

    // 3. Si pertenece a un equipo, obtener todos los integrantes y su Rango de la tabla USUARIO
    if ($mi_equipo) {
        $sql_integrantes = "SELECT U.ID_U, U.Nombre_Usuario, U.Rango FROM INTEGRA I 
                            JOIN USUARIO U ON I.ID_U = U.ID_U 
                            WHERE I.ID_E = ?";
        $stmt_int = $pdo->prepare($sql_integrantes);
        $stmt_int->execute([$mi_equipo['ID_E']]);
        $integrantes = $stmt_int->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    $error_db = $e->getMessage();
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
        <div class="perfilIzquierda">
            <img src="https://i.pravatar.cc/200" class="fotoPerfil">
            <h2><?php echo htmlspecialchars($usuario['Nombre_Usuario'] ?? 'Brahian Amaral'); ?></h2>
            <p>ID: <?php echo str_pad($usuario['ID_U'] ?? 1, 4, "0", STR_PAD_LEFT); ?></p>
            <span class="admin">
                <?php echo (!empty($usuario['Rol'])) ? "👑 Administrador" : "👤 Usuario Estándar"; ?>
            </span>

            <!-- Botón para borrar perfil -->
            <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px;">
                <form action="EliminarPerfil.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar tu perfil permanentemente? Esta acción no se puede deshacer.');">
                    <button type="submit" name="eliminar_cuenta" style="background-color: #d9534f; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%;">
                        🗑️ Borrar mi perfil
                    </button>
                </form>
            </div>
        </div>

        <!-- Columna Derecha: Juegos, puntuaciones y gestión de equipos -->
        <div class="perfilDerecha">
            
            <!-- Alerta si el líder intenta salir del equipo teniendo miembros -->
            <?php if (isset($_GET['error'])): ?>
                <?php if ($_GET['error'] == 'unico_lider' || $_GET['error'] == 'lider_con_miembros'): ?>
                    <div style="background-color: #f2dede; color: #a94442; padding: 12px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ebccd1;">
                        ⚠️ No puedes salir del equipo siendo el líder si todavía hay miembros dentro. Debes expulsarlos primero.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- TARJETA: Mi Equipo -->
            <div class="tarjeta">
                <h3>🛡️ Mi Equipo</h3>
                
                <?php if ($mi_equipo): ?>
                    <p><strong>Nombre del Equipo:</strong> <?php echo htmlspecialchars($mi_equipo['Nombre_Equipo']); ?></p>
                    <p><strong>Tu Rol:</strong> <?php echo ($usuario['Rango'] == 1) ? 'Líder / Fundador' : 'Miembro'; ?></p>
                    
                    <h4 style="margin-top: 15px;">Integrantes:</h4>
                    <ul>
                        <?php foreach ($integrantes as $integ): ?>
                            <li style="margin-bottom: 8px;">
                                <?php echo htmlspecialchars($integ['Nombre_Usuario']); ?> 
                                <?php echo ($integ['Rango'] == 1) ? '👑 (Líder)' : '(Miembro)'; ?>
                                
                                <!-- Si el usuario actual es líder y el integrante listado no es él mismo, mostrar botón Echar -->
                                <?php if ($usuario['Rango'] == 1 && $integ['ID_U'] != $id_usuario): ?>
                                    <form action="EcharMiembro.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_miembro" value="<?php echo $integ['ID_U']; ?>">
                                        <button type="submit" onclick="return confirm('¿Estás seguro de expulsar a este integrante?');" style="background: #d9534f; color: white; border: none; padding: 2px 6px; cursor: pointer; float: right; border-radius: 3px;">
                                            Echar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- SECCIÓN DE INVITACIÓN (Solo visible si el usuario actual es líder) -->
                    <?php if ($usuario['Rango'] == 1): ?>
                        <div style="margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 15px;">
                            <h4 style="margin-bottom: 10px;">➕ Invitar Integrante</h4>
                            
                            <!-- Alertas específicas del proceso de invitación -->
                            <?php if (isset($_GET['invitacion'])): ?>
                                <?php if ($_GET['invitacion'] == 'exito'): ?>
                                    <p style="color: #4cae4c; font-size: 14px; margin-bottom: 10px;">✅ ¡Usuario agregado al equipo con éxito!</p>
                                <?php elseif ($_GET['invitacion'] == 'no_encontrado'): ?>
                                    <p style="color: #d9534f; font-size: 14px; margin-bottom: 10px;">❌ El nombre de usuario no existe.</p>
                                <?php elseif ($_GET['invitacion'] == 'ya_tiene_equipo'): ?>
                                    <p style="color: #d9534f; font-size: 14px; margin-bottom: 10px;">⚠️ El usuario ya forma parte de otro equipo.</p>
                                <?php elseif ($_GET['invitacion'] == 'auto_invitacion'): ?>
                                    <p style="color: #d9534f; font-size: 14px; margin-bottom: 10px;">⚠️ No puedes invitarte a ti mismo.</p>
                                <?php elseif ($_GET['invitacion'] == 'error'): ?>
                                    <p style="color: #d9534f; font-size: 14px; margin-bottom: 10px;">❌ Ocurrió un error al procesar la invitación.</p>
                                <?php endif; ?>
                            <?php endif; ?>

                            <form action="InvitarMiembro.php" method="POST" style="display: flex; gap: 8px;">
                                <input type="text" name="nombre_invitado" placeholder="Nombre de usuario" required style="flex: 1; padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
                                <button type="submit" style="background-color: #337ab7; color: white; border: none; padding: 6px 12px; cursor: pointer; border-radius: 4px;">
                                    Invitar
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 20px;">
                        <!-- Botón para salir del equipo -->
                        <form action="SalirEquipo.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que deseas salir del equipo?');">
                            <button type="submit" style="background-color: #f0ad4e; color: white; border: none; padding: 8px 12px; cursor: pointer; border-radius: 4px;">
                                Salir del equipo
                            </button>
                        </form>
                    </div>

                <?php else: ?>
                    <p>Actualmente no formas parte de ningún equipo.</p>
                    <!-- Botón para redirigir a la creación de equipos -->
                    <a href="CrearEquipo.php" style="display: inline-block; margin-top: 10px; background-color: #5cb85c; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px;">
                        Crear mi propio equipo
                    </a>
                <?php endif; ?>
            </div>

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