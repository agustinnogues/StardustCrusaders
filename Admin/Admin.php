<?php
session_start();
require_once "../Basededatos/Conexion.php";
// SEGURIDAD
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../GestionSesion/Login.php");
    exit();
}

if (empty($_SESSION["Rol"])) {
    header("Location: ../Gestionusuarios/Perfil.php");
    exit();
}
// CONEXIÓN
$conexion = Conexion::conectar();
// PESTAÑA ACTUAL
$tab = $_GET["tab"] ?? "usuarios";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>
        Panel de Administración
    </title>
    <!-- CSS GENERAL -->
    <link
        rel="stylesheet"
        href="../css/Estilos.css"
    >
    <!-- CSS DEL ADMIN -->
    <link
        rel="stylesheet"
        href="../css/Admin.css"
    >
</head>
<body>
<?php
include "../includes/Header.php";
?>
<main class="adminContainer">
    <!--
         TÍTULO -->
    <div class="adminTitulo">
        <h1>
            Panel de Administración
        </h1>
        <p>
            Administración de usuarios y juegos
        </p>
    </div>
    <!-- Pestañas -->
    <div class="adminTabs">
        <a
            href="Admin.php?tab=usuarios"
            class="adminTab
            <?= $tab === "usuarios" ? "activa" : "" ?>"
        >
             Usuarios
        </a>
        <a
            href="Admin.php?tab=juegos"
            class="adminTab
            <?= $tab === "juegos" ? "activa" : "" ?>"
        >
            Juegos
        </a>
        <a
            href="Admin.php?tab=agregar"
            class="adminTab
            <?= $tab === "agregar" ? "activa" : "" ?>"
        >
            Agregar juego
        </a>
    </div>
    <!-- Contenidos -->
    <div class="adminContenido">
        <?php
        if ($tab === "usuarios") {
            include "Editar_Usuarios.php";
        } elseif ($tab === "juegos") {
            include "Editar_Juegos.php";
        } elseif ($tab === "agregar") {
            include "agregar_juego.php";
        } else {
            include "Editar_Usuarios.php";
        }
        ?>
    </div>
</main>
<?php
include "../includes/Footer.php";
?>
<script src="../Js/Scripts.js"></script>
</body>
</html>