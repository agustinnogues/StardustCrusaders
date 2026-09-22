<header id="header">
    <div class="logo">
        🎮 <span>S.C</span>
    </div>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="juegos.php">Juegos</a>
        <a href="novedades.php">Novedades</a>
        <a href="nosotros.php">Nosotros</a>
    </nav>
    <div class="acciones">
        <button id="modoOscuro">
            🌙
        </button>
    <div class="perfil">
        <img src="https://i.pravatar.cc/45" alt="Perfil" id="fotoPerfil">
        <div class="menuPerfil" id="menuPerfil">
            <a href="perfil.php">Mi perfil</a>
            <a href="#">Configuración</a>
            <a href="#">Mis estadísticas</a>
            <a href="#">Mis juegos</a>
               <hr>
               <?php if (isset($_SESSION["rol"]) && $_SESSION["rol"] == 1): ?>
                    <a href="admin.php">Panel Admin</a>
                <?php endif; ?>
                <?php if (isset($_SESSION["rango"]) && $_SESSION["rango"] == 1): ?>
                     <a href="equipo.php">Panel Equipo</a>
                <?php endif; ?>
                <?php if (
                    (isset($_SESSION["rol"]) && $_SESSION["rol"] == 1) ||
                    (isset($_SESSION["rango"]) && $_SESSION["rango"] == 1)
                ): ?>
                <hr>
                <?php endif; ?>
            <hr>
                <a href="logout.php">Cerrar sesión</a>
            <hr>
        </div>
    </div> 
</header>