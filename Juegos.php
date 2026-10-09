<?php
require_once "Basededatos/Conexion.php";
try {
    $conexion = Conexion::conectar();
    $consulta = $conexion->query(
        "SELECT ID_J, Nombre, Carpeta, Descripcion, Puntos_Maximos
         FROM JUEGO
         ORDER BY ID_J DESC"
    );
    $juegos = $consulta->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $juegos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juegos - Stardust Crusaders</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<?php include("includes/header.php"); ?>
<section class="pagina">
    <h1>🎮 Juegos</h1>
    <p class="subtitulo">
        Explora todos los juegos disponibles en Stardust Crusaders.
    </p>
    <div class="buscador">
        <input
            type="text"
            id="buscarJuego"
            placeholder="Buscar un juego..."
        >
    </div>
    <div class="gridJuegos" id="listaJuegos">
        <?php if (count($juegos) > 0): ?>
            <?php foreach ($juegos as $juego): ?>
                <div
                    class="cardJuego"
                    data-nombre="<?= htmlspecialchars(strtolower($juego['Nombre'])) ?>"
                >
                    <img
                        src="juegos/<?= htmlspecialchars($juego['Carpeta']) ?>/portada.png"
                        alt="<?= htmlspecialchars($juego['Nombre']) ?>"
                        onerror="this.src='https://picsum.photos/400/220?random=<?= $juego['ID_J'] ?>'"
                    >
                    <h3>
                        <?= htmlspecialchars($juego['Nombre']) ?>
                    </h3>
                    <a
                        href="juegos/<?= htmlspecialchars($juego['Carpeta']) ?>/index.html"
                        class="botonJuego"
                    >
                        Jugar
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>
                No hay juegos disponibles actualmente.
            </p>
        <?php endif; ?>
    </div>
</section>
<?php include("includes/footer.php"); ?>
<script src="Js/scripts.js"></script>
<script>
document.getElementById("buscarJuego").addEventListener("input", function () {
    const texto = this.value.toLowerCase();
    document.querySelectorAll(".cardJuego").forEach(function (juego) {
        const nombre = juego.dataset.nombre;
        if (nombre.includes(texto)) {
            juego.style.display = "";
        } else {
            juego.style.display = "none";
        }
    });
});
</script>
</body>
</html>