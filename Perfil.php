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
            <h2>Brahian Amaral</h2>
            <p>ID: 0001</p>
            <span class="admin">👑 Administrador</span>
        </div>

        <!-- Columna Derecha: Juegos, puntuaciones y gestión de equipos -->
        <div class="perfilDerecha">
            
            <!-- Alerta si intenta salir del equipo siendo el único líder -->
            <?php if (isset($_GET['error']) && $_GET['error'] == 'unico_lider'): ?>
                <div style="background-color: #f2dede; color: #a94442; padding: 12px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #ebccd1;">
                    ⚠️ No puedes salir del equipo siendo el único líder. Debes asignar a otro líder primero o ceder el puesto.
                </div>
            <?php endif; ?>

            <!-- NUEVA TARJETA: Mi Equipo -->
            <div class="tarjeta">
                <h3>🛡️ Mi Equipo</h3>
                
                <?php if ($mi_equipo): ?>
                    <p><strong>Nombre del Equipo:</strong> <?php echo htmlspecialchars($mi_equipo['Nombre_Equipo']); ?></p>
                    <p><strong>Tu Rol:</strong> <?php echo htmlspecialchars($mi_equipo['Rol']); ?></p>
                    
                    <h4 style="margin-top: 15px;">Integrantes:</h4>
                    <ul>
                        <?php foreach ($integrantes as $integ): ?>
                            <li style="margin-bottom: 8px;">
                                <?php echo htmlspecialchars($integ['Nombre_Usuario']); ?> (<?php echo htmlspecialchars($integ['Rol']); ?>)
                                
                                <!-- Si el usuario actual es líder y el integrante listado no es él mismo, mostrar botón Echar -->
                                <?php if ($mi_equipo['Rol'] == 'lider' && $integ['ID_U'] != $id_usuario): ?>
                                    <form action="EcharMiembro.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_miembro" value="<?php echo $integ['ID_U']; ?>">
                                        <button type="submit" onclick="return confirm('¿Estás seguro de expulsar a este integrante?');" style="background: #d9534f; color: white; border: none; padding: 2px 6px; cursor: pointer; float: right; border-radius: 3px;">
                                            Echar
                                        </button>
                                    </form>
                                <?php elseif ($integ['ID_U'] == $id_usuario && $mi_equipo['Rol'] == 'lider'): ?>
                                    <button style="background: #ccc; color: white; border: none; padding: 2px 6px; cursor: pointer; float: right; border-radius: 3px;" disabled>Echar</button>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div style="margin-top: 20px;">
                        <!-- Botón para salir del equipo (validando si es el único líder) -->
                        <form action="SalirEquipo.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que deseas salir del equipo?');">
                            <button type="submit" style="background-color: #f0ad4e; color: white; border: none; padding: 8px 12px; cursor: pointer; border-radius: 4px;">
                                Salir del equipo
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <p>Actualmente no formas parte de ningún equipo.</p>
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