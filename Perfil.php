<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - GameHub</title>
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