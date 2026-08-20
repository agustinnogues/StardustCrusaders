<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Juegos - GameHub</title>
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
        <input type="text" placeholder="Buscar un juego...">
    </div>
    <div class="gridJuegos">
        <div class="cardJuego">
            <img src="https://picsum.photos/400/220?11">
            <h3>Adivina la Bandera</h3>
            <button>Jugar</button>
        </div>
        <div class="cardJuego">
            <img src="https://picsum.photos/400/220?12">
            <h3>Snake</h3>
            <button>Jugar</button>
        </div>
        <div class="cardJuego">
            <img src="https://picsum.photos/400/220?13">
            <h3>Memoria</h3>
            <button>Jugar</button>
        </div>
        <div class="cardJuego">
            <img src="https://picsum.photos/400/220?14">
            <h3>Tetris</h3>
            <button>Jugar</button>
        </div>
        <div class="cardJuego">
            <img src="https://picsum.photos/400/220?15">
            <h3>2048</h3>
            <button>Jugar</button>
        </div>
        <div class="cardJuego">
            <img src="https://picsum.photos/400/220?16">
            <h3>Buscaminas</h3>
            <button>Jugar</button>
        </div>
    </div>
</section>
<?php include("includes/footer.php"); ?>
<script src="Js/scripts.js"></script>
</body>
</html>