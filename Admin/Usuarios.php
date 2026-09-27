<?php
/*USUARIOS*/
$idAdministrador = $_SESSION["id_usuario"];
$mensaje = "";
$error = "";
// CAMBIAR ROL
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["cambiar_rol"])
) {
    $idUsuario =
        intval($_POST["id_usuario"]);
    if ($idUsuario == $idAdministrador) {
        $error =
            "No podés modificar tu propio rol.";
    } else {
        try {
            $consulta = $conexion->prepare("
                SELECT
                    ID_U,
                    Nombre_Usuario,
                    Rol
                FROM USUARIO
                WHERE ID_U = ?
            ");
            $consulta->execute([
                $idUsuario
            ]);
            $usuario =
                $consulta->fetch(PDO::FETCH_ASSOC);
            if (!$usuario) {
                $error =
                    "El usuario no existe.";
            } else {
                $nuevoRol =
                    ($usuario["Rol"] == 1)
                    ? 0
                    : 1;
                $actualizar = $conexion->prepare("
                    UPDATE USUARIO
                    SET Rol = ?
                    WHERE ID_U = ?
                ");
                $actualizar->execute([
                    $nuevoRol,
                    $idUsuario
                ]);
                if ($nuevoRol == 1) {
                    $accion =
                        "Otorgó permisos de administrador al usuario "
                        . $usuario["Nombre_Usuario"]
                        . " (ID: "
                        . $idUsuario
                        . ")";
                } else {
                    $accion =
                        "Quitó los permisos de administrador al usuario "
                        . $usuario["Nombre_Usuario"]
                        . " (ID: "
                        . $idUsuario
                        . ")";
                }
                $registro = $conexion->prepare("
                    INSERT INTO ADMIN_U
                    (
                        ID_Usu,
                        ID_Adm,
                        Fecha,
                        Accion
                    )
                    VALUES (?, ?, NOW(), ?)
                ");
                $registro->execute([
                    $idUsuario,
                    $idAdministrador,
                    $accion
                ]);
                $mensaje =
                    $accion;
            }
        } catch (PDOException $e) {
            $error =
                "ERROR: "
                . $e->getMessage();
        }
    }
}
// ELIMINAR USUARIO
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["eliminar_usuario"])
) {
    $idUsuario =
        intval($_POST["id_usuario"]);
    if ($idUsuario == $idAdministrador) {
        $error =
            "No podés eliminar tu propio usuario.";
    } else {
        try {
            $consulta = $conexion->prepare("
                SELECT
                    ID_U,
                    Nombre_Usuario
                FROM USUARIO
                WHERE ID_U = ?
            ");
            $consulta->execute([
                $idUsuario
            ]);
            $usuario =
                $consulta->fetch(PDO::FETCH_ASSOC);
            if (!$usuario) {
                $error =
                    "El usuario no existe.";
            } else {
                $nombreUsuario =
                    $usuario["Nombre_Usuario"];
                $conexion->beginTransaction();
                // Registrar acción
                $accion =
                    "Eliminó al usuario "
                    . $nombreUsuario
                    . " (ID: "
                    . $idUsuario
                    . ")";
                $registro = $conexion->prepare("
                    INSERT INTO ADMIN_U
                    (
                        ID_Usu,
                        ID_Adm,
                        Fecha,
                        Accion
                    )
                    VALUES (?, ?, NOW(), ?)
                ");
                $registro->execute([
                    $idUsuario,
                    $idAdministrador,
                    $accion
                ]);
                // Eliminar partidas
                $eliminarPartidas =
                    $conexion->prepare("
                        DELETE FROM JUE_PAR
                        WHERE ID_U = ?
                    ");
                $eliminarPartidas->execute([
                    $idUsuario
                ]);
                // Eliminar equipo
                $eliminarEquipo =
                    $conexion->prepare("
                        DELETE FROM INTEGRA
                        WHERE ID_U = ?
                    ");
                $eliminarEquipo->execute([
                    $idUsuario
                ]);
                // Eliminar usuario
                $eliminarUsuario =
                    $conexion->prepare("
                        DELETE FROM USUARIO
                        WHERE ID_U = ?
                    ");
                $eliminarUsuario->execute([
                    $idUsuario
                ]);
                $conexion->commit();
                $mensaje =
                    "El usuario "
                    . $nombreUsuario
                    . " fue eliminado correctamente.";
            }
        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $error =
                "ERROR: "
                . $e->getMessage();
        }
    }
}
// EDITAR USUARIO
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["editar_usuario"])
) {
    $idUsuario =
        intval($_POST["id_usuario"]);
    $nombre =
        trim(
            $_POST["nombre_usuario"] ?? ""
        );
    $correo =
        trim(
            $_POST["correo"] ?? ""
        );
    $rango =
        isset($_POST["rango"])
        ? 1
        : 0;
    $password =
        trim(
            $_POST["password"] ?? ""
        );
    if (
        $nombre === ""
        || $correo === ""
    ) {
        $error =
            "El nombre y el correo son obligatorios.";
    } else {
        try {
            if ($password !== "") {
                $actualizar =
                    $conexion->prepare("
                        UPDATE USUARIO
                        SET
                            Nombre_Usuario = ?,
                            Correo_Electronico = ?,
                            Contrasena = ?,
                            Rango = ?
                        WHERE ID_U = ?
                    ");
                $actualizar->execute([
                    $nombre,
                    $correo,
                    $password,
                    $rango,
                    $idUsuario
                ]);
            } else {
                $actualizar =
                    $conexion->prepare("
                        UPDATE USUARIO
                        SET
                            Nombre_Usuario = ?,
                            Correo_Electronico = ?,
                            Rango = ?
                        WHERE ID_U = ?
                    ");
                $actualizar->execute([
                    $nombre,
                    $correo,
                    $rango,
                    $idUsuario
                ]);
            }
            $accion =
                "Modificó los datos del usuario "
                . $nombre
                . " (ID: "
                . $idUsuario
                . ")";
            $registro =
                $conexion->prepare("
                    INSERT INTO ADMIN_U
                    (
                        ID_Usu,
                        ID_Adm,
                        Fecha,
                        Accion
                    )
                    VALUES (?, ?, NOW(), ?)
                ");
            $registro->execute([
                $idUsuario,
                $idAdministrador,
                $accion
            ]);
            $mensaje =
                "Usuario modificado correctamente.";
        } catch (PDOException $e) {
            $error =
                "ERROR: "
                . $e->getMessage();
        }
    }
}
// OBTENER USUARIOS
$consultaUsuarios =
    $conexion->query("
        SELECT
            ID_U,
            Nombre_Usuario,
            Correo_Electronico,
            Rol,
            Rango
        FROM USUARIO
        ORDER BY ID_U ASC
    ");
$usuarios =
    $consultaUsuarios->fetchAll(
        PDO::FETCH_ASSOC
    );
// USUARIO PARA EDITAR
$usuarioEditar = null;
if (isset($_GET["editar_usuario"])) {
    $idEditar =
        intval($_GET["editar_usuario"]);
    $consulta =
        $conexion->prepare("
            SELECT
                ID_U,
                Nombre_Usuario,
                Correo_Electronico,
                Rol,
                Rango
            FROM USUARIO
            WHERE ID_U = ?
        ");
    $consulta->execute([
        $idEditar
    ]);
    $usuarioEditar =
        $consulta->fetch(
            PDO::FETCH_ASSOC
        );
}
?>
<!-- MENSAJES -->
<?php if ($mensaje !== ""): ?>
    <div class="mensajeExito">
        <?= htmlspecialchars($mensaje) ?>
    </div>
<?php endif; ?>
<?php if ($error !== ""): ?>
    <div class="mensajeError">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>
<!-- EDITAR USUARIO -->
<?php if ($usuarioEditar): ?>
    <div class="tarjetaAdmin">
        <h2>
            Editar usuario
        </h2>
        <form
            method="POST"
            class="formAdmin"
        >
            <input
                type="hidden"
                name="id_usuario"
                value="<?= $usuarioEditar["ID_U"] ?>"
            >
            <div class="campoAdmin">
                <label>
                    Nombre de usuario
                </label>
                <input
                    type="text"
                    name="nombre_usuario"
                    value="<?= htmlspecialchars(
                        $usuarioEditar["Nombre_Usuario"]
                    ) ?>"
                    required
                >
            </div>
            <div class="campoAdmin">
                <label>
                    Correo electrónico
                </label>
                <input
                    type="email"
                    name="correo"
                    value="<?= htmlspecialchars(
                        $usuarioEditar["Correo_Electronico"]
                    ) ?>"
                    required
                >
            </div>
            <div class="campoAdmin">
                <label>
                    Nueva contraseña
                </label>
                <input
                    type="text"
                    name="password"
                    placeholder="Dejar vacío para mantener la actual"
                >
            </div>
            <div class="campoAdmin">
                <label>
                    <input
                        type="checkbox"
                        name="rango"
                        <?= $usuarioEditar["Rango"] == 1
                            ? "checked"
                            : "" ?>
                    >
                    Líder de equipo
                </label>
            </div>
            <div>
                <button
                    type="submit"
                    name="editar_usuario"
                    class="botonAdmin botonCrear"
                >
                    Guardar cambios
                </button>
                <a
                    href="Admin.php?tab=usuarios"
                    class="botonAdmin botonCancelar"
                >
                    Cancelar
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>
<!--LISTA DE USUARIOS-->
<div class="tarjetaAdmin">
    <h2>
        Usuarios registrados
    </h2>
    <div class="tablaResponsive">
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
                    <td>
                        <?= $usuario["ID_U"] ?>
                    </td>
                    <td>
                        <?= htmlspecialchars(
                            $usuario["Nombre_Usuario"]
                        ) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars(
                            $usuario["Correo_Electronico"]
                        ) ?>
                    </td>
                    <td>
                        <?php if ($usuario["Rol"] == 1): ?>
                            <span class="rolAdmin">
                                Administrador
                            </span>
                        <?php else: ?>
                            Jugador
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($usuario["Rango"] == 1): ?>
                            Líder
                        <?php else: ?>
                            Jugador
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- EDITAR -->
                        <a
                            href="Admin.php?tab=usuarios&editar_usuario=<?= $usuario["ID_U"] ?>"
                            class="botonAdmin botonEditar"
                        >
                            Editar
                        </a>
                        <?php if (
                            $usuario["ID_U"]
                            != $idAdministrador
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
</div>