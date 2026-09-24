<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$ruta = str_contains($_SERVER['PHP_SELF'], '/Admin/') ? '../' : '';
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
                <a href="<?= $ruta ?>perfil.php">
                    Mi perfil
                </a>
                <a href="#">
                    Configuración
                </a>
                <a href="<?= $ruta ?>Rankings.php">
                    Rankings 
                </a>
                <a href="#">
                    Mis juegos
                </a>
                <?php if (!empty($_SESSION['Rol'])): ?>
                    <hr>
                    <a href="<?= $ruta ?>Admin/Admin.php">
                        Panel Admin
                    </a>
                <?php endif; ?>
                <?php if (!empty($_SESSION['Rango'])): ?>
                    <hr>
                    <a href="#">
                        Panel Equipo
                    </a>
                <?php endif; ?>
                <hr>
                <a href="<?= $ruta ?>Logout.php">
                    Cerrar sesión
                </a>
            </div>
        </div>
    </div>
</header>