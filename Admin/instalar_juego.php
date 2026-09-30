<?php

session_start();

require_once "../Conexion.php";

/*
|--------------------------------------------------------------------------
| 1. COMPROBAR ADMINISTRADOR
|--------------------------------------------------------------------------
*/

if (empty($_SESSION["Rol"])) {
    die("Error: no tenés permisos de administrador.");
}

/*
|--------------------------------------------------------------------------
| 2. COMPROBAR POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Error: el formulario no fue enviado correctamente.");
}

/*
|--------------------------------------------------------------------------
| 3. RECIBIR DATOS
|--------------------------------------------------------------------------
*/

$nombre = trim($_POST["nombre"] ?? "");
$carpeta = trim($_POST["carpeta"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$puntosMaximos = $_POST["puntos_maximos"] ?? "";

/*
|--------------------------------------------------------------------------
| 4. VALIDACIONES
|--------------------------------------------------------------------------
*/

if ($nombre === "") {
    die("Error: falta el nombre del juego.");
}

if ($carpeta === "") {
    die("Error: falta el nombre de la carpeta.");
}

if (!preg_match('/^[a-zA-Z0-9_-]+$/', $carpeta)) {
    die("Error: el nombre de la carpeta solo puede contener letras, números, guiones y guiones bajos.");
}

if (
    $puntosMaximos === "" ||
    !is_numeric($puntosMaximos) ||
    $puntosMaximos < 0
) {
    die("Error: los puntos máximos no son válidos.");
}

/*
|--------------------------------------------------------------------------
| 5. COMPROBAR ARCHIVO
|--------------------------------------------------------------------------
*/

if (!isset($_FILES["archivo_juego"])) {
    die("Error: no se recibió ningún archivo ZIP.");
}

$archivo = $_FILES["archivo_juego"];

if ($archivo["error"] !== UPLOAD_ERR_OK) {

    $errores = [
        UPLOAD_ERR_INI_SIZE =>
            "El archivo supera el tamaño máximo permitido por PHP.",

        UPLOAD_ERR_FORM_SIZE =>
            "El archivo supera el tamaño máximo permitido por el formulario.",

        UPLOAD_ERR_PARTIAL =>
            "El archivo se subió parcialmente.",

        UPLOAD_ERR_NO_FILE =>
            "No se seleccionó ningún archivo.",

        UPLOAD_ERR_NO_TMP_DIR =>
            "Falta la carpeta temporal de PHP.",

        UPLOAD_ERR_CANT_WRITE =>
            "PHP no pudo escribir el archivo.",

        UPLOAD_ERR_EXTENSION =>
            "Una extensión de PHP detuvo la subida."
    ];

    $mensaje = $errores[$archivo["error"]] ?? "Error desconocido al subir el archivo.";

    die("Error al subir el juego: " . $mensaje);
}

/*
|--------------------------------------------------------------------------
| 6. COMPROBAR ZIP
|--------------------------------------------------------------------------
*/

$extension = strtolower(
    pathinfo($archivo["name"], PATHINFO_EXTENSION)
);

if ($extension !== "zip") {
    die("Error: el archivo debe ser un ZIP.");
}

/*
|--------------------------------------------------------------------------
| 7. COMPROBAR ZIPARCHIVE
|--------------------------------------------------------------------------
*/

if (!class_exists("ZipArchive")) {
    die("
        <h2>Error</h2>
        <p>La extensión ZIP de PHP no está habilitada.</p>
        <p>Activá ZIP en php.ini y reiniciá Apache.</p>
    ");
}

/*
|--------------------------------------------------------------------------
| 8. CONEXIÓN A BASE DE DATOS
|--------------------------------------------------------------------------
*/

try {

    $conexion = Conexion::conectar();

} catch (PDOException $e) {

    die("
        <h2>Error de conexión</h2>
        <p>No se pudo conectar con la base de datos.</p>
        <p>Error: " .
        htmlspecialchars($e->getMessage()) .
        "</p>
    ");
}

/*
|--------------------------------------------------------------------------
| 9. COMPROBAR SI YA EXISTE EL JUEGO
|--------------------------------------------------------------------------
*/

$consulta = $conexion->prepare(
    "SELECT ID_J
     FROM JUEGO
     WHERE Nombre = ?"
);

$consulta->execute([$nombre]);

if ($consulta->fetch()) {

    die("
        <h2>Error</h2>
        <p>Ya existe un juego llamado <strong>" .
        htmlspecialchars($nombre) .
        "</strong>.</p>
        <p><a href='Admin.php?tab=agregar'>Volver</a></p>
    ");
}

/*
|--------------------------------------------------------------------------
| 10. CARPETA /JUEGOS/
|--------------------------------------------------------------------------
*/

$carpetaJuegos =
    dirname(__DIR__) .
    DIRECTORY_SEPARATOR .
    "juegos";

if (!is_dir($carpetaJuegos)) {

    if (!mkdir($carpetaJuegos, 0777, true)) {

        die("
            <h2>Error</h2>
            <p>No se pudo crear la carpeta juegos.</p>
            <p>Ruta:</p>
            <pre>" .
            htmlspecialchars($carpetaJuegos) .
            "</pre>
        ");
    }
}

/*
|--------------------------------------------------------------------------
| 11. CARPETA DEL JUEGO
|--------------------------------------------------------------------------
*/

$rutaJuego =
    $carpetaJuegos .
    DIRECTORY_SEPARATOR .
    $carpeta;

if (file_exists($rutaJuego)) {

    die("
        <h2>Error</h2>
        <p>Ya existe una carpeta llamada:</p>
        <pre>" .
        htmlspecialchars($carpeta) .
        "</pre>
    ");
}

if (!mkdir($rutaJuego, 0777, true)) {

    die("
        <h2>Error</h2>
        <p>No se pudo crear la carpeta del juego.</p>
        <p>Ruta:</p>
        <pre>" .
        htmlspecialchars($rutaJuego) .
        "</pre>
    ");
}

/*
|--------------------------------------------------------------------------
| 12. ABRIR ZIP
|--------------------------------------------------------------------------
*/

$zip = new ZipArchive();

$resultado = $zip->open($archivo["tmp_name"]);

if ($resultado !== true) {

    eliminarCarpeta($rutaJuego);

    die("
        <h2>Error</h2>
        <p>No se pudo abrir el archivo ZIP.</p>
        <p>Código de error: " .
        htmlspecialchars((string)$resultado) .
        "</p>
    ");
}

/*
|--------------------------------------------------------------------------
| 13. ARCHIVOS PERMITIDOS
|--------------------------------------------------------------------------
*/

$archivosPermitidos = [
    "html",
    "htm",
    "css",
    "js",
    "json",
    "txt",
    "xml",
    "svg",
    "png",
    "jpg",
    "jpeg",
    "gif",
    "webp",
    "ico",
    "mp3",
    "wav",
    "ogg",
    "mp4",
    "webm",
    "woff",
    "woff2",
    "ttf",
    "otf"
];

$indexEncontrado = false;

/*
|--------------------------------------------------------------------------
| 14. REVISAR ZIP
|--------------------------------------------------------------------------
*/

for ($i = 0; $i < $zip->numFiles; $i++) {

    $entrada = $zip->getNameIndex($i);

    if ($entrada === false) {

        $zip->close();
        eliminarCarpeta($rutaJuego);

        die("Error: no se pudo leer un archivo del ZIP.");
    }

    $entrada = str_replace("\\", "/", $entrada);

    /*
    | Seguridad contra ZIP Slip
    */

    if (
        str_starts_with($entrada, "/") ||
        preg_match('#(^|/)\.\.(/|$)#', $entrada)
    ) {

        $zip->close();
        eliminarCarpeta($rutaJuego);

        die("Error: el ZIP contiene una ruta no permitida.");
    }

    /*
    | Ignorar carpetas
    */

    if (str_ends_with($entrada, "/")) {
        continue;
    }

    /*
    | Extensión
    */

    $extensionArchivo = strtolower(
        pathinfo($entrada, PATHINFO_EXTENSION)
    );

    if (!in_array($extensionArchivo, $archivosPermitidos)) {

        $zip->close();
        eliminarCarpeta($rutaJuego);

        die(
            "Error: el archivo <strong>" .
            htmlspecialchars($entrada) .
            "</strong> no está permitido dentro del ZIP."
        );
    }

    /*
    | INDEX.HTML
    */

    if ($entrada === "index.html") {
        $indexEncontrado = true;
    }
}

/*
|--------------------------------------------------------------------------
| 15. COMPROBAR INDEX.HTML
|--------------------------------------------------------------------------
*/

if (!$indexEncontrado) {

    $zip->close();
    eliminarCarpeta($rutaJuego);

    die("
        <h2>Error</h2>
        <p>El ZIP debe contener un archivo <strong>index.html</strong> en la raíz.</p>
        <p>No debe estar dentro de otra carpeta.</p>
    ");
}

/*
|--------------------------------------------------------------------------
| 16. EXTRAER ZIP
|--------------------------------------------------------------------------
*/

for ($i = 0; $i < $zip->numFiles; $i++) {

    $entrada = $zip->getNameIndex($i);

    $entrada = str_replace("\\", "/", $entrada);

    /*
    | Ignorar carpetas
    */

    if (str_ends_with($entrada, "/")) {

        $carpetaDestino =
            $rutaJuego .
            DIRECTORY_SEPARATOR .
            $entrada;

        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        continue;
    }

    /*
    | Ruta del archivo
    */

    $archivoDestino =
        $rutaJuego .
        DIRECTORY_SEPARATOR .
        str_replace(
            "/",
            DIRECTORY_SEPARATOR,
            $entrada
        );

    /*
    | Crear carpeta padre
    */

    $carpetaPadre = dirname($archivoDestino);

    if (!is_dir($carpetaPadre)) {

        if (!mkdir($carpetaPadre, 0777, true)) {

            $zip->close();
            eliminarCarpeta($rutaJuego);

            die("Error: no se pudo crear una carpeta interna del juego.");
        }
    }

    /*
    | Leer archivo ZIP
    */

    $entradaZip = $zip->getStream($entrada);

    if ($entradaZip === false) {

        $zip->close();
        eliminarCarpeta($rutaJuego);

        die(
            "Error: no se pudo extraer el archivo " .
            htmlspecialchars($entrada)
        );
    }

    /*
    | Crear archivo
    */

    $archivoSalida = fopen($archivoDestino, "wb");

    if ($archivoSalida === false) {

        fclose($entradaZip);
        $zip->close();
        eliminarCarpeta($rutaJuego);

        die(
            "Error: no se pudo crear el archivo " .
            htmlspecialchars($entrada)
        );
    }

    /*
    | Copiar
    */

    stream_copy_to_stream(
        $entradaZip,
        $archivoSalida
    );

    fclose($entradaZip);
    fclose($archivoSalida);
}

$zip->close();

/*
|--------------------------------------------------------------------------
| 17. GUARDAR EN BASE DE DATOS
|--------------------------------------------------------------------------
*/

try {

    $conexion->beginTransaction();

    /*
    | Insertar juego
    */

    $insertarJuego = $conexion->prepare(
        "INSERT INTO JUEGO
        (Nombre, Carpeta, Descripcion, Puntos_Maximos)
        VALUES (?, ?, ?, ?)"
    );

    $insertarJuego->execute([
        $nombre,
        $carpeta,
        $descripcion,
        (int)$puntosMaximos
    ]);

    $idJuego = $conexion->lastInsertId();

    /*
    | Registrar acción del administrador
    */

    $idAdministrador =
        $_SESSION["id_usuario"] ?? null;

    if ($idAdministrador !== null) {

        $accion =
            "Agregó el juego " .
            $nombre .
            " (ID: " .
            $idJuego .
            ")";

        $registro = $conexion->prepare(
            "INSERT INTO ADMIN_J
            (ID_Usu, ID_Jue, Fecha, Accion)
            VALUES (?, ?, NOW(), ?)"
        );

        $registro->execute([
            $idAdministrador,
            $idJuego,
            $accion
        ]);
    }

    /*
    | Confirmar
    */

    $conexion->commit();

} catch (PDOException $e) {

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    eliminarCarpeta($rutaJuego);

    die("
        <h2>Error al registrar el juego</h2>

        <p>
            El ZIP se procesó correctamente,
            pero hubo un problema al guardar el juego en la base de datos.
        </p>

        <p><strong>Error de MySQL:</strong></p>

        <pre style='background:#eee;padding:15px;'>" .
        htmlspecialchars($e->getMessage()) .
        "</pre>

        <p>
            <a href='Admin.php?tab=agregar'>
                Volver al administrador
            </a>
        </p>
    ");
}

/*
|--------------------------------------------------------------------------
| 18. ÉXITO
|--------------------------------------------------------------------------
*/

header(
    "Location: Admin.php?tab=agregar&exito=1"
);

exit;

/*
|--------------------------------------------------------------------------
| FUNCIÓN PARA ELIMINAR CARPETAS
|--------------------------------------------------------------------------
*/

function eliminarCarpeta($carpeta)
{
    if (!is_dir($carpeta)) {
        return;
    }

    $elementos = scandir($carpeta);

    foreach ($elementos as $elemento) {

        if (
            $elemento === "." ||
            $elemento === ".."
        ) {
            continue;
        }

        $ruta =
            $carpeta .
            DIRECTORY_SEPARATOR .
            $elemento;

        if (is_dir($ruta)) {

            eliminarCarpeta($ruta);

        } else {

            unlink($ruta);
        }
    }

    rmdir($carpeta);
}

?>