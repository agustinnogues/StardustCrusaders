<?php

session_start();

require_once "Conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: Login.php");
    exit();
}

$conexion = Conexion::conectar();

$idUsuario = $_SESSION["id_usuario"];


/*
==========================================================
DATOS DEL USUARIO
==========================================================
*/

$consultaUsuario = $conexion->prepare("
    SELECT ID_U, Nombre_Usuario
    FROM USUARIO
    WHERE ID_U = ?
");

$consultaUsuario->execute([$idUsuario]);

$usuarioActual = $consultaUsuario->fetch(PDO::FETCH_ASSOC);


/*
==========================================================
RANKING DE USUARIOS
==========================================================
*/

$consultaRanking = $conexion->query("
    SELECT
        U.ID_U,
        U.Nombre_Usuario,
        COALESCE(SUM(JP.Puntos), 0) AS Puntos
    FROM USUARIO U
    LEFT JOIN JUE_PAR JP
        ON U.ID_U = JP.ID_U
    GROUP BY
        U.ID_U,
        U.Nombre_Usuario
    ORDER BY Puntos DESC, U.ID_U ASC
");

$rankingUsuarios = $consultaRanking->fetchAll(PDO::FETCH_ASSOC);


/*
==========================================================
POSICIÓN DEL USUARIO
==========================================================
*/

$posicionUsuario = 0;
$usuarioRanking = null;

foreach ($rankingUsuarios as $posicion => $usuario) {

    if ($usuario["ID_U"] == $idUsuario) {

        $posicionUsuario = $posicion + 1;
        $usuarioRanking = $usuario;

        break;
    }
}


/*
==========================================================
TOP 10
==========================================================
*/

$topUsuarios = array_slice($rankingUsuarios, 0, 10);


/*
==========================================================
EQUIPO DEL USUARIO
==========================================================
*/

$consultaEquipo = $conexion->prepare("
    SELECT
        E.ID_E,
        E.Nombre_Equipo
    FROM INTEGRA I
    INNER JOIN EQUIPO E
        ON I.ID_E = E.ID_E
    WHERE I.ID_U = ?
");

$consultaEquipo->execute([$idUsuario]);

$equipoUsuario = $consultaEquipo->fetch(PDO::FETCH_ASSOC);


/*
==========================================================
RANKING DE EQUIPOS
==========================================================
*/

$consultaEquipos = $conexion->query("
    SELECT
        E.ID_E,
        E.Nombre_Equipo,
        COALESCE(SUM(JP.Puntos), 0) AS Puntos
    FROM EQUIPO E

    LEFT JOIN INTEGRA I
        ON E.ID_E = I.ID_E

    LEFT JOIN JUE_PAR JP
        ON I.ID_U = JP.ID_U

    GROUP BY
        E.ID_E,
        E.Nombre_Equipo

    ORDER BY Puntos DESC, E.ID_E ASC
");

$rankingEquipos = $consultaEquipos->fetchAll(PDO::FETCH_ASSOC);


/*
==========================================================
 POSICIÓN DEL EQUIPO
==========================================================
*/

$posicionEquipo = 0;
$equipoRanking = null;

if ($equipoUsuario) {

    foreach ($rankingEquipos as $posicion => $equipo) {

        if ($equipo["ID_E"] == $equipoUsuario["ID_E"]) {

            $posicionEquipo = $posicion + 1;
            $equipoRanking = $equipo;

            break;
        }
    }
}


/*
==========================================================
TOP 3 EQUIPOS
==========================================================
*/

$topEquipos = array_slice($rankingEquipos, 0, 3);


/*
==========================================================
ÚLTIMOS 3 JUEGOS
==========================================================
*/

$consultaUltimos = $conexion->prepare("
    SELECT
        J.Nombre,
        JP.Puntos,
        JP.Fecha
    FROM JUE_PAR JP

    INNER JOIN JUEGO J
        ON JP.ID_J = J.ID_J

    WHERE JP.ID_U = ?

    ORDER BY JP.Fecha DESC

    LIMIT 3
");

$consultaUltimos->execute([$idUsuario]);

$ultimosJuegos = $consultaUltimos->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Rankings - Stardust Crusaders</title>

    <link
        rel="stylesheet"
        href="css/estilos.css"
    >

    <style>

        .rankingsPagina {
            width: 90%;
            max-width: 1200px;
            margin: 110px auto 50px;
        }

        .rankingsPagina h1 {
            margin-bottom: 30px;
        }

        .rankingsGrid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            align-items: start;
        }

        .tarjetaRanking {
            background: var(--card);
            color: var(--texto);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 15px var(--sombra);
            margin-bottom: 25px;
        }

        .tarjetaRanking h2 {
            margin-bottom: 20px;
        }

        .filaRanking {
            display: grid;
            grid-template-columns: 60px 1fr 120px;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #ddd;
            border-radius: 6px;
        }

        .filaRanking:last-child {
            border-bottom: none;
        }

        .posicion {
            font-weight: bold;
        }

        .nombreRanking {
            font-weight: 600;
        }

        .puntosRanking {
            text-align: right;
            font-weight: bold;
        }

        .miRanking {
            background: var(--azul);
            color: white;
            font-weight: bold;
        }

        .miRanking .puntosRanking {
            color: white;
        }

        .miPosicion {
            background: var(--card);
            color: var(--texto);
            border: 3px solid var(--azul);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .miPosicion h3 {
            margin-bottom: 10px;
        }

        .separadorRanking {
            margin: 20px 0;
            border: none;
            border-top: 2px solid #ddd;
        }

        .juegoReciente {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #ddd;
        }

        .juegoReciente:last-child {
            border-bottom: none;
        }

        .nombreJuego {
            font-weight: 600;
        }

        .puntosJuego {
            font-weight: bold;
        }

        .sinDatos {
            opacity: 0.7;
            padding: 10px 0;
        }

        @media (max-width: 800px) {

            .rankingsGrid {
                grid-template-columns: 1fr;
            }

            .filaRanking {
                grid-template-columns: 45px 1fr 90px;
            }

        }

    </style>

</head>

<body>

<?php include("includes/header.php"); ?>


<main class="rankingsPagina">

    <h1>🏆 Rankings</h1>


    <div class="rankingsGrid">


        <!-- ==================================================
             RANKING DE USUARIOS
        =================================================== -->

        <div class="tarjetaRanking">

            <h2>🏆 10 mejores jugadores</h2>

            <?php foreach ($topUsuarios as $posicion => $usuario): ?>

                <?php
                $esUsuarioActual =
                    $usuario["ID_U"] == $idUsuario;
                ?>

                <div class="filaRanking <?= $esUsuarioActual ? 'miRanking' : '' ?>">

                    <div class="posicion">
                        #<?= $posicion + 1 ?>
                    </div>

                    <div class="nombreRanking">
                        <?= htmlspecialchars(
                            $usuario["Nombre_Usuario"]
                        ) ?>
                    </div>

                    <div class="puntosRanking">
                        <?= $usuario["Puntos"] ?> pts
                    </div>

                </div>

            <?php endforeach; ?>


            <!-- USUARIO FUERA DEL TOP 10 -->

            <?php if ($posicionUsuario > 10): ?>

                <div class="miPosicion">

                    <h3>Tu posición</h3>

                    <div class="filaRanking miRanking">

                        <div class="posicion">
                            #<?= $posicionUsuario ?>
                        </div>

                        <div class="nombreRanking">
                            <?= htmlspecialchars(
                                $usuarioActual["Nombre_Usuario"]
                            ) ?>
                        </div>

                        <div class="puntosRanking">
                            <?= $usuarioRanking["Puntos"] ?> pts
                        </div>

                    </div>

                </div>

            <?php endif; ?>

        </div>


        <!-- ==================================================
             COLUMNA DERECHA
        =================================================== -->

        <div>


            <!-- ==================================================
                 TOP 3 EQUIPOS
            =================================================== -->

            <div class="tarjetaRanking">

                <h2>👥 Mejores equipos</h2>

                <?php foreach ($topEquipos as $posicion => $equipo): ?>

                    <?php
                    $esMiEquipo =
                        $equipoUsuario &&
                        $equipo["ID_E"] == $equipoUsuario["ID_E"];
                    ?>

                    <div class="filaRanking <?= $esMiEquipo ? 'miRanking' : '' ?>">

                        <div class="posicion">
                            #<?= $posicion + 1 ?>
                        </div>

                        <div class="nombreRanking">
                            <?= htmlspecialchars(
                                $equipo["Nombre_Equipo"]
                            ) ?>
                        </div>

                        <div class="puntosRanking">
                            <?= $equipo["Puntos"] ?> pts
                        </div>

                    </div>

                <?php endforeach; ?>


                <!-- EQUIPO FUERA DEL TOP 3 -->

                <?php if (
                    $equipoUsuario &&
                    $posicionEquipo > 3
                ): ?>

                    <div class="miPosicion">

                        <h3>Tu equipo</h3>

                        <div class="filaRanking miRanking">

                            <div class="posicion">
                                #<?= $posicionEquipo ?>
                            </div>

                            <div class="nombreRanking">
                                <?= htmlspecialchars(
                                    $equipoRanking["Nombre_Equipo"]
                                ) ?>
                            </div>

                            <div class="puntosRanking">
                                <?= $equipoRanking["Puntos"] ?> pts
                            </div>

                        </div>

                    </div>

                <?php elseif (!$equipoUsuario): ?>
                    <hr>
                    <div class="sinDatos">
                        No pertenecés a ningún equipo.
                    </div>

                <?php endif; ?>

            </div>


            <!-- ==================================================
                 ÚLTIMOS JUEGOS
            =================================================== -->

            <div class="tarjetaRanking">

                <h2>🎮 Últimos juegos</h2>

                <?php if (count($ultimosJuegos) > 0): ?>

                    <?php foreach ($ultimosJuegos as $juego): ?>

                        <div class="juegoReciente">

                            <div>

                                <div class="nombreJuego">
                                    <?= htmlspecialchars(
                                        $juego["Nombre"]
                                    ) ?>
                                </div>

                                <small>
                                    <?= htmlspecialchars(
                                        $juego["Fecha"]
                                    ) ?>
                                </small>

                            </div>

                            <div class="puntosJuego">
                                <?= $juego["Puntos"] ?> pts
                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="sinDatos">
                        Todavía no jugaste ningún juego.
                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</main>


<?php include("includes/footer.php"); ?>

<script src="Js/scripts.js"></script>

</body>

</html>