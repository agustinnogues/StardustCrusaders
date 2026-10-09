<?php
require_once "Basededatos/Conexion.php";

try {
    $conexion = Conexion::conectar();
    $consulta = $conexion->query(
        "SELECT ID_J, Nombre, Carpeta
         FROM JUEGO
         ORDER BY ID_J DESC
         LIMIT 3"
    );
    $ultimosJuegos = $consulta->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ultimosJuegos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Stardust Cruzaders</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<?php include("includes/header.php"); ?>
<section class="hero">
    <h1>Bienvenido a Stardust Crusaders</h1>
    <p>
        Descubre nuevos juegos y compite con otros jugadores.
    </p>
</section>
<section class="titulo">
    <h2>🔥 Últimos juegos agregados</h2>
</section>
<section class="contenedorJuegos">
    <?php if (!empty($ultimosJuegos)): ?>
        <?php foreach ($ultimosJuegos as $juego): ?>
            <?php
            $carpetaJuego = htmlspecialchars($juego["Carpeta"] ?? "", ENT_QUOTES, "UTF-8");
            $nombreJuego = htmlspecialchars($juego["Nombre"], ENT_QUOTES, "UTF-8");
            ?>
            <div class="juego">
                <img
                    src="juegos/<?= $carpetaJuego ?>/portada.png"
                    alt="<?= $nombreJuego ?>"
                    onerror="this.src='https://picsum.photos/350/180?random=<?= (int)$juego['ID_J'] ?>'"
                >
                <h3><?= $nombreJuego ?></h3>
                <a href="juegos/<?= $carpetaJuego ?>/index.html?id_juego=<?= (int)$juego['ID_J'] ?>" class="botonJuego">
                    Jugar
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay juegos disponibles actualmente.</p>
    <?php endif; ?>
</section>
<section class="informacion">
    <h2>Sobre Stardust Cruzaders</h2>
    <p>
        Este sitio reúne juegos desarrollados por nosotros, 
        compite con otros jugadores y descubrir nuevos juegos.
    </p>
    <br><br><br><br><br><br><br><br><br>
</section>
<?php include("includes/footer.php"); ?>
<script src="Js/scripts.js"></script>
</body>
</html>