<?php
/*
AGREGAR JUEGO
*/
$idAdministrador = $_SESSION["id_usuario"];
$mensaje = "";
$error = "";
// CREAR JUEGO
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["crear_juego"])
) {
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
    /* 
    VALIDACIONES
    */
    if ($nombre === "") {
        $error =
            "El nombre del juego es obligatorio.";
    } elseif ($carpeta === "") {
        $error =
            "El nombre de la carpeta es obligatorio.";
    } elseif ($puntosMaximos < 0) {
        $error =
            "Los puntos máximos no pueden ser negativos.";
    } elseif (
        !preg_match(
            '/^[a-zA-Z0-9_-]+$/',
            $carpeta
        )
    ) {
        $error =
            "El nombre de la carpeta solo puede contener letras, números, guion y guion bajo.";
    } else {
        try {
            /*
            CARPETA PRINCIPAL
            */
            $carpetaJuegos =
                dirname(__DIR__) . "/juegos";
            /*
            Si no existe /juegos,la crea.
            */
            if (!is_dir($carpetaJuegos)) {
                if (
                    !mkdir(
                        $carpetaJuegos,
                        0777,
                        true
                    )
                ) {
                    throw new Exception(
                        "No se pudo crear la carpeta principal de juegos."
                    );
                }
            }
            /*
            COMPROBAR CARPETA
            */
            $rutaJuego =
                $carpetaJuegos
                . "/"
                . $carpeta;
            if (is_dir($rutaJuego)) {
                throw new Exception(
                    "Ya existe una carpeta llamada "
                    . $carpeta
                    . ". Elegí otro nombre."
                );
            }
            /*
            COMPROBAR BASE DE DATOS 
            */
            $buscarCarpeta =
                $conexion->prepare("
                    SELECT ID_J
                    FROM JUEGO
                    WHERE Carpeta = ?
                ");
            $buscarCarpeta->execute([
                $carpeta
            ]);
            if (
                $buscarCarpeta->fetch()
            ) {
                throw new Exception(
                    "Ya existe un juego utilizando esa carpeta."
                );
            }
            /*
            CREAR CARPETA
            */
            if (
                !mkdir(
                    $rutaJuego,
                    0777,
                    true
                )
            ) {
                throw new Exception(
                    "No se pudo crear la carpeta del juego."
                );
            }
            /*
            INSERTAR JUEGO
            */
            $insertar =
                $conexion->prepare("
                    INSERT INTO JUEGO
                    (
                        Nombre,
                        Carpeta,
                        Descripcion,
                        Puntos_Maximos
                    )
                    VALUES (?, ?, ?, ?)
                ");
            $insertar->execute([
                $nombre,
                $carpeta,
                $descripcion,
                $puntosMaximos
            ]);
            /*
            Obtener ID generado
            */
            $idJuego =
                $conexion->lastInsertId();
            /*
            REGISTRAR ACCIÓN ADMINISTRATIVA
            */
            $accion =
                "Creó el juego "
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
            /*
            MENSAJE
            */
            $mensaje =
                "Juego creado correctamente.";
        } catch (Exception $e) {
            /*
            Si algo falla después de crear
            la carpeta, intentamos eliminarla.
            */
            if (
                isset($rutaJuego)
                && is_dir($rutaJuego)
            ) {
                @rmdir($rutaJuego);
            }
            $error =
                "ERROR: "
                . $e->getMessage();
        }
    }
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
<!-- FORMULARIO -->
<div class="tarjetaAdmin">
    <h2>
        Agregar nuevo juego
    </h2>
    <p>
        Completá los datos del juego.
        Al crearlo se generará automáticamente
        una carpeta dentro de
        <strong>/juegos/</strong>.
    </p>
    <form
        method="POST"
        class="formAdmin"
    >
        <!-- NOMBRE -->
        <div class="campoAdmin">
            <label>
                Nombre del juego
            </label>
            <input
                type="text"
                name="nombre_juego"
                placeholder="Ej: Pokémon Battle"
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
                placeholder="Ej: pokemon_battle"
                required
            >
            <small>
                Utilizá solamente letras,
                números,
                <strong>_</strong>
                y
                <strong>-</strong>.
            </small>
            <div class="infoCarpeta">
                Se creará:
                <br>
                <strong>
                    juegos/nombre_de_tu_carpeta/
                </strong>
            </div>
        </div>
        <!-- DESCRIPCIÓN -->
        <div class="campoAdmin">
            <label>
                Descripción
            </label>
            <textarea
                name="descripcion"
                placeholder="Escribí una descripción del juego..."
            ></textarea>
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
                value="100"
                required
            >
        </div>
        <!-- BOTÓN -->
        <div>
            <button
                type="submit"
                name="crear_juego"
                class="botonAdmin botonCrear"
            >
                Crear juego
            </button>
        </div>
    </form>
</div>