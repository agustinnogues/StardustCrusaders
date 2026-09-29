<?php
/*JUEGOS*/
$idAdministrador = $_SESSION["id_usuario"];

$mensaje = "";
$error = "";
// EDITAR JUEGO
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["editar_juego"])
) {
    $idJuego =
        intval($_POST["id_juego"]);
    $nombre =
        trim(
            $_POST["nombre_juego"] ?? ""
        );
    $carpeta =
        trim(
            $_POST["carpeta"] ?? ""
        );
    $descripcion =
        trim(
            $_POST["descripcion"] ?? ""
        );
    $puntosMaximos =
        intval(
            $_POST["puntos_maximos"] ?? 0
        );
    /*VALIDACIONES*/
    if ($nombre === "") {
        $error =
            "El nombre del juego es obligatorio.";
    } elseif ($carpeta === "") {
        $error =
            "El nombre de la carpeta es obligatorio.";
    } elseif ($puntosMaximos < 0) {
        $error =
            "Los puntos máximos no pueden ser negativos.";
    } else {
        try {
            /*BUSCAR JUEGO ACTUAL*/
            $consulta =
                $conexion->prepare("
                    SELECT
                        ID_J,
                        Nombre,
                        Carpeta,
                        Descripcion,
                        Puntos_Maximos
                    FROM JUEGO
                    WHERE ID_J = ?
                ");
            $consulta->execute([
                $idJuego
            ]);
            $juego =
                $consulta->fetch(
                    PDO::FETCH_ASSOC
                );
            if (!$juego) {
                throw new Exception(
                    "El juego no existe."
                );
            }
            /* VALIDAR NOMBRE DE CARPETA*/
            if (
                !preg_match(
                    '/^[a-zA-Z0-9_-]+$/',
                    $carpeta
                )
            ) {
                throw new Exception(
                    "El nombre de la carpeta solo puede contener letras, números, guion y guion bajo."
                );
            }
            /*CARPETA PRINCIPAL DE JUEGOS*/
            $carpetaJuegos =
                dirname(__DIR__) . "/juegos";
            if (!is_dir($carpetaJuegos)) {
                mkdir(
                    $carpetaJuegos,
                    0777,
                    true
                );
            }
            /*CARPETA ANTERIOR*/
            $carpetaAnterior =
                $juego["Carpeta"];
            $rutaAnterior =
                $carpetaJuegos
                . "/"
                . $carpetaAnterior;
            $rutaNueva =
                $carpetaJuegos
                . "/"
                . $carpeta;
            /*
            CAMBIAR NOMBRE DE CARPETA
            */
            if (
                $carpetaAnterior !== $carpeta
            ) {
                /*
                Si la nueva carpeta ya existe,
                no podemos utilizarla.
                */
                if (
                    is_dir($rutaNueva)
                ) {
                    throw new Exception(
                        "Ya existe una carpeta con ese nombre."
                    );
                }
                /*
                Si existe la carpeta anterior,
                la renombramos.
                */
                if (
                    is_dir($rutaAnterior)
                ) {
                    if (
                        !rename(
                            $rutaAnterior,
                            $rutaNueva
                        )
                    ) {
                        throw new Exception(
                            "No se pudo cambiar el nombre de la carpeta."
                        );
                    }
                } else {
                    /*
                    Si el juego no tenía carpeta,
                    la creamos.
                    */
                    if (
                        !mkdir(
                            $rutaNueva,
                            0777,
                            true
                        )
                    ) {
                        throw new Exception(
                            "No se pudo crear la carpeta del juego."
                        );
                    }
                }
            }
            /*
            ACTUALIZAR JUEGO
            */
            $actualizar =
                $conexion->prepare("
                    UPDATE JUEGO
                    SET
                        Nombre = ?,
                        Carpeta = ?,
                        Descripcion = ?,
                        Puntos_Maximos = ?
                    WHERE ID_J = ?
                ");
            $actualizar->execute([
                $nombre,
                $carpeta,
                $descripcion,
                $puntosMaximos,
                $idJuego
            ]);
            /*
            REGISTRAR ACCIÓN
            */
            $accion =
                "Modificó el juego "
                . $nombre
                . " (ID: "
                . $idJuego
                . ")";
            $registro =
                $conexion->prepare("
                    INSERT INTO ADMIN_J
                    (
                        ID_Usu,
                        ID_Jue,
                        Fecha,
                        Accion
                    )
                    VALUES (?, ?, NOW(), ?)
                ");
            $registro->execute([
                $idAdministrador,
                $idJuego,
                $accion
            ]);
            $mensaje =
                "El juego fue modificado correctamente.";
        } catch (Exception $e) {
            $error =
                "ERROR: "
                . $e->getMessage();
        }
    }
}
// OBTENER JUEGOS
$consultaJuegos =
    $conexion->query("
        SELECT
            ID_J,
            Nombre,
            Carpeta,
            Descripcion,
            Puntos_Maximos
        FROM JUEGO
        ORDER BY ID_J ASC
    ");
$juegos =
    $consultaJuegos->fetchAll(
        PDO::FETCH_ASSOC
    );
// JUEGO QUE SE ESTÁ EDITANDO
$juegoEditar = null;
if (isset($_GET["editar_juego"])) {
    $idEditar =
        intval($_GET["editar_juego"]);
    $consulta =
        $conexion->prepare("
            SELECT
                ID_J,
                Nombre,
                Carpeta,
                Descripcion,
                Puntos_Maximos
            FROM JUEGO
            WHERE ID_J = ?
        ");
    $consulta->execute([
        $idEditar
    ]);
    $juegoEditar =
        $consulta->fetch(
            PDO::FETCH_ASSOC
        );
}
?>
<!-- MENSAJES-->
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
<!-- EDITAR JUEGO -->
<?php if ($juegoEditar): ?>
    <div class="tarjetaAdmin">
        <h2>
            Editar juego
        </h2>
        <form
            method="POST"
            class="formAdmin"
        >
            <input
                type="hidden"
                name="id_juego"
                value="<?= $juegoEditar["ID_J"] ?>"
            >
            <!-- NOMBRE -->
            <div class="campoAdmin">
                <label>
                    Nombre del juego
                </label>
                <input
                    type="text"
                    name="nombre_juego"
                    value="<?= htmlspecialchars(
                        $juegoEditar["Nombre"]
                    ) ?>"
                    required
                >
            </div>
            <!-- CARPETA -->
            <div class="campoAdmin">
                <label>
                    Nombre de la carpeta
                </label>
                <input
                    type="text"
                    name="carpeta"
                    value="<?= htmlspecialchars(
                        $juegoEditar["Carpeta"] ?? ""
                    ) ?>"
                    required
                >
                <div class="infoCarpeta">
                    juegos/
                    <?= htmlspecialchars(
                        $juegoEditar["Carpeta"] ?? ""
                    ) ?>
                </div>
                <small>
                    Solo letras, números,
                    <strong>_</strong>
                    y
                    <strong>-</strong>.
                </small>
            </div>
            <!-- DESCRIPCIÓN -->
            <div class="campoAdmin">
                <label>
                    Descripción
                </label>
                <textarea
                    name="descripcion"
                ><?= htmlspecialchars(
                    $juegoEditar["Descripcion"] ?? ""
                ) ?></textarea>
            </div>
            <!-- PUNTOS -->
            <div class="campoAdmin">
                <label>
                    Puntos máximos
                </label>
                <input
                    type="number"
                    name="puntos_maximos"
                    min="0"
                    value="<?= $juegoEditar["Puntos_Maximos"] ?>"
                    required
                >
            </div>
            <!-- BOTONES -->
            <div>
                <button
                    type="submit"
                    name="editar_juego"
                    class="botonAdmin botonCrear"
                >
                    Guardar cambios
                </button>
                <a
                    href="Admin.php?tab=juegos"
                    class="botonAdmin botonCancelar"
                >
                    Cancelar
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>
<!-- LISTADO DE JUEGOS -->
<div class="tarjetaAdmin">
    <div class="tituloSeccion">
        <div>
            <h2>
                Juegos registrados
            </h2>
            <p>
                Desde aquí podés modificar los juegos
                existentes.
            </p>
        </div>
        <a
            href="Admin.php?tab=agregar"
            class="botonAdmin botonCrear"
        >
            + Agregar juego
        </a>
    </div>
    <?php if (count($juegos) === 0): ?>
        <p>
            Todavía no hay juegos registrados.
        </p>
    <?php else: ?>
        <div class="tablaResponsive">
            <table class="tablaAdmin">
                <thead>
                    <tr>
                        <th>
                            ID
                        </th>
                        <th>
                            Juego
                        </th>
                        <th>
                            Carpeta
                        </th>
                        <th>
                            Descripción
                        </th>
                        <th>
                            Puntos máximos
                        </th>
                        <th>
                            Acción
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($juegos as $juego): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars(
                                $juego["ID_J"]
                            ) ?>
                        </td>
                        <td>
                            <strong>
                                <?= htmlspecialchars(
                                    $juego["Nombre"]
                                ) ?>
                            </strong>
                        </td>
                        <td>
                            <span class="nombreCarpeta">
                                <?= htmlspecialchars(
                                    $juego["Carpeta"] ?? ""
                                ) ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars(
                                $juego["Descripcion"] ?? ""
                            ) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars(
                                $juego["Puntos_Maximos"]
                            ) ?>
                        </td>
                        <td>
                            <a
                                href="Admin.php?tab=juegos&editar_juego=<?= $juego["ID_J"] ?>"
                                class="botonAdmin botonEditar"
                            >
                                Editar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>