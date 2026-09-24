<?php
session_start();
require_once "../Conexion.php";

/*
==========================================================
SEGURIDAD
==========================================================
*/
// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit();
}
// Verificar que sea administrador
if (empty($_SESSION["Rol"])) {
    header("Location: ../perfil.php");
    exit();
}
$conexion = Conexion::conectar();
$idAdministrador = $_SESSION["id_usuario"];
$mensaje = "";
$error = "";
/*
==========================================================
CAMBIAR ROL DEL USUARIO
==========================================================
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cambiar_rol"])) {

    $idUsuario = intval($_POST["id_usuario"]);

    // No permitir modificar el propio rol
    if ($idUsuario == $idAdministrador) {

        $error = "No podés modificar tu propio rol.";

    } else {

        try {

            // Buscar usuario
            $consulta = $conexion->prepare("
                SELECT ID_U, Nombre_Usuario, Rol
                FROM USUARIO
                WHERE ID_U = ?
            ");

            $consulta->execute([$idUsuario]);

            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {

                $error = "El usuario no existe.";

            } else {

                // Cambiar entre jugador (0) y administrador (1)
                $nuevoRol = ($usuario["Rol"] == 1) ? 0 : 1;

                $actualizar = $conexion->prepare("
                    UPDATE USUARIO
                    SET Rol = ?
                    WHERE ID_U = ?
                ");

                $actualizar->execute([
                    $nuevoRol,
                    $idUsuario
                ]);

                // Crear descripción de la acción
                if ($nuevoRol == 1) {

                    $accion = "Otorgó permisos de administrador al usuario "
                            . $usuario["Nombre_Usuario"];

                } else {

                    $accion = "Quitó los permisos de administrador al usuario "
                            . $usuario["Nombre_Usuario"];
                }

                /*
                REGISTRAR ACCIÓN EN ADMIN_U
                */

                $registro = $conexion->prepare("
                    INSERT INTO ADMIN_U
                    (ID_Usu, ID_Adm, Fecha, Accion)
                    VALUES (?, ?, NOW(), ?)
                ");

                $registro->execute([
                    $idUsuario,
                    $idAdministrador,
                    $accion
                ]);

                $mensaje = $accion;
            }

        } catch (PDOException $e) {

            $error = "Ocurrió un error al cambiar el rol del usuario.";
        }
    }
}


/*
==========================================================
ELIMINAR USUARIO
==========================================================
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["eliminar_usuario"])) {

    $idUsuario = intval($_POST["id_usuario"]);
    // No permitir eliminarse a sí mismo
    if ($idUsuario == $idAdministrador) {
        $error = "No podés eliminar tu propio usuario.";
    } else {
        try {
            // Buscar usuario
            $consulta = $conexion->prepare("
                SELECT ID_U, Nombre_Usuario
                FROM USUARIO
                WHERE ID_U = ?
            ");
            $consulta->execute([$idUsuario]);
            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);
            if (!$usuario) {
                $error = "El usuario no existe.";
            } else {
                $nombreUsuario = $usuario["Nombre_Usuario"];
                $conexion->beginTransaction();
                /*
                Registrar la acción ANTES de eliminar
                */
                $accion = "Eliminó al usuario "
                        . $nombreUsuario
                        . " (ID: "
                        . $idUsuario
                        . ")";
                $registro = $conexion->prepare("
                    INSERT INTO ADMIN_U
                    (ID_Usu, ID_Adm, Fecha, Accion)
                    VALUES (?, ?, NOW(), ?)
                ");
                $registro->execute([
                    $idUsuario,
                    $idAdministrador,
                    $accion
                ]);
                /*
                Eliminar partidas del usuario
                */
                $eliminarPartidas = $conexion->prepare("
                    DELETE FROM JUE_PAR
                    WHERE ID_U = ?
                ");
                $eliminarPartidas->execute([$idUsuario]);
                /*
                Eliminar relación con equipo
                */
                $eliminarEquipo = $conexion->prepare("
                    DELETE FROM INTEGRA
                    WHERE ID_U = ?
                ");
                $eliminarEquipo->execute([$idUsuario]);
                /*
                Eliminar usuario
                */
                $eliminarUsuario = $conexion->prepare("
                    DELETE FROM USUARIO
                    WHERE ID_U = ?
                ");
                $eliminarUsuario->execute([$idUsuario]);
                /*
                Confirmar todos los cambios
                */
                $conexion->commit();
                $mensaje = "El usuario " . $nombreUsuario .
                           " fue eliminado correctamente.";
            }
        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $error = "ERROR: " . $e->getMessage();
        }
    }
}
/*
==========================================================
OBTENER TODOS LOS USUARIOS
==========================================================
*/

$consultaUsuarios = $conexion->query("
    SELECT
        ID_U,
        Nombre_Usuario,
        Correo_Electronico,
        Rol,
        Rango
    FROM USUARIO
    ORDER BY ID_U ASC
");

$usuarios = $consultaUsuarios->fetchAll(PDO::FETCH_ASSOC);

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
        Panel de Administración - Stardust Crusaders
    </title>

    <link
        rel="stylesheet"
        href="../css/estilos.css"
    >

    <style>

        .adminContainer {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
        }

        .adminContainer h1 {
            margin-bottom: 10px;
        }

        .adminDescripcion {
            margin-bottom: 30px;
        }

        .mensajeExito {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .mensajeError {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .tablaAdmin {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .tablaAdmin th,
        .tablaAdmin td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .tablaAdmin th {
            font-weight: bold;
        }

        .botonAdmin {
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            margin: 2px;
        }

        .botonRol {
            background: #ffc107;
        }

        .botonEliminar {
            background: #dc3545;
            color: white;
        }

        .rolAdmin {
            font-weight: bold;
        }

        .rolJugador {
            font-weight: normal;
        }

        .tarjetaAdmin {
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 10px;
            background: var(--card);
            color: var(--texto);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .dark .mensajeExito {
            background: #1e4620;
            color: #d4edda;
        }

        .dark .mensajeError {
            background: #5a1f24;
            color: #f8d7da;
        }

        .dark .tablaAdmin th,
        .dark .tablaAdmin td {
            border-color: #444;
        }

    </style>

</head>

<body>
<?php include("../includes/header.php"); ?>
<section class="pagina">

    <div class="adminContainer">

        <h1>
            Panel de Administración
        </h1>

        <p class="adminDescripcion">
            Desde este panel podés administrar los usuarios de
            Stardust Crusaders.
        </p>


        <!-- MENSAJE DE ÉXITO -->

        <?php if ($mensaje != ""): ?>

            <div class="mensajeExito">

                <?= htmlspecialchars($mensaje) ?>

            </div>

        <?php endif; ?>


        <!-- MENSAJE DE ERROR -->

        <?php if ($error != ""): ?>

            <div class="mensajeError">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <!-- USUARIOS -->

        <div class="tarjetaAdmin">

            <h2>
                Usuarios registrados
            </h2>

            <table class="tablaAdmin">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Usuario</th>

                        <th>Correo</th>

                        <th>Rol</th>

                        <th>Rango</th>

                        <th>Acciones</th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($usuarios as $usuario): ?>

                    <tr>

                        <!-- ID -->

                        <td>

                            <?= htmlspecialchars(
                                $usuario["ID_U"]
                            ) ?>

                        </td>


                        <!-- USUARIO -->

                        <td>

                            <?= htmlspecialchars(
                                $usuario["Nombre_Usuario"]
                            ) ?>

                        </td>


                        <!-- CORREO -->

                        <td>

                            <?= htmlspecialchars(
                                $usuario["Correo_Electronico"]
                            ) ?>

                        </td>


                        <!-- ROL -->

                        <td>

                            <?php if ($usuario["Rol"] == 1): ?>

                                <span class="rolAdmin">
                                    Administrador
                                </span>

                            <?php else: ?>

                                <span class="rolJugador">
                                    Jugador
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- RANGO -->

                        <td>

                            <?php if ($usuario["Rango"] == 1): ?>

                                Lider

                            <?php else: ?>

                                Jugador

                            <?php endif; ?>

                        </td>


                        <!-- ACCIONES -->

                        <td>

                            <?php if (
                                $usuario["ID_U"] != $idAdministrador
                            ): ?>


                                <!-- CAMBIAR ROL -->

                                <form
                                    method="POST"
                                    style="display:inline;"
                                >

                                    <input
                                        type="hidden"
                                        name="id_usuario"
                                        value="<?= $usuario["ID_U"] ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="cambiar_rol"
                                        class="botonAdmin botonRol"
                                    >

                                        <?php if (
                                            $usuario["Rol"] == 1
                                        ): ?>

                                            Quitar Admin

                                        <?php else: ?>

                                            Hacer Admin

                                        <?php endif; ?>

                                    </button>

                                </form>


                                <!-- ELIMINAR -->

                                <form
                                    method="POST"
                                    style="display:inline;"
                                    onsubmit="
                                        return confirm(
                                            '¿Seguro que querés eliminar este usuario?'
                                        );
                                    "
                                >

                                    <input
                                        type="hidden"
                                        name="id_usuario"
                                        value="<?= $usuario["ID_U"] ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="eliminar_usuario"
                                        class="botonAdmin botonEliminar"
                                    >

                                        Eliminar

                                    </button>

                                </form>


                            <?php else: ?>

                                <strong>
                                    Vos
                                </strong>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>


        <!-- INFORMACIÓN -->

        <div class="tarjetaAdmin">

            <h2>
                ℹ️ Información
            </h2>

            <p>
                Los administradores pueden cambiar los permisos
                de los usuarios y eliminar cuentas.
            </p>

            <p>
                Las modificaciones de permisos quedan registradas
                en la tabla <strong>ADMIN_U</strong>.
            </p>

        </div>

    </div>

</section>


<?php include("../includes/footer.php"); ?>


<script src="../Js/scripts.js"></script>

</body>

</html>