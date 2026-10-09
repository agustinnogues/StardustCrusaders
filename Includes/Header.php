<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$ruta = preg_match('~/(Admin|Gestionusuarios|Gestionequipos)/~i', $_SERVER['PHP_SELF']) ? '../' : '';
?>
<header id="header">
    <div class="logo">
        🎮 <span>S.C</span>
    </div>
    <nav>
        <a href="<?= $ruta ?>index.php">Inicio</a>
        <a href="<?= $ruta ?>juegos.php">Juegos</a>
        <a href="<?= $ruta ?>novedades.php">Novedades</a>
        <a href="<?= $ruta ?>nosotros.php">Nosotros</a>
    </nav>
    <div class="acciones">
        <button id="modoOscuro">
            🌙
        </button>
        <div class="perfil">
            <img
                src="https://i.pravatar.cc/45"
                alt="Perfil"
                id="fotoPerfil"
            >
            <div class="menuPerfil" id="menuPerfil">
                <a href="<?= $ruta ?>Gestionusuarios/Perfil.php">
                    Mi perfil
                </a>
                <a href="<?= $ruta ?>Rankings.php">
                    Rankings 
                </a>
                <?php if (!empty($_SESSION['Rol'])): ?>
                    <hr>
                    <a href="<?= $ruta ?>Admin/Admin.php">
                        Panel Admin
                    </a>
                <?php endif; ?>
                <hr>
                <a href="<?= $ruta ?>GestionSesion/Logout.php">
                    Cerrar sesión
                </a>
            </div>
        </div>
    </div>
</header>