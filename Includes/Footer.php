<?php
$ruta = preg_match('~/(Admin|Gestionusuarios|Gestionequipos)/~i', $_SERVER['PHP_SELF']) ? '../' : '';
?>
<footer id="footer">
    <div>
        © 2026 Stardust Crusaders
    </div>
    <a href="<?= $ruta ?>index.php">🏠</a>
    <a href="<?= $ruta ?>juegos.php">🎮</a>
    <a href="<?= $ruta ?>novedades.php">📰</a>
    <a href="<?= $ruta ?>Gestionusuarios/Perfil.php">👤</a>
</footer>