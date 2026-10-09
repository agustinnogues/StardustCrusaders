<?php
session_start();
require_once "../Basededatos/Conexion.php";
// 1. COMPROBAR QUE EL USUARIO SEA ADMINISTRADOR
if (empty($_SESSION["Rol"])) {
    header("Location: Admin.php");
    exit;
}
// 2. COMPROBAR QUE EL FORMULARIO SE ENVÍE POR POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: Admin.php?tab=agregar");
    exit;
}
// 3. RECIBIR LOS DATOS DEL FORMULARIO
$nombre = trim($_POST["nombre"] ?? "");
$carpeta = trim($_POST["carpeta"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$puntosMaximos = $_POST["puntos_maximos"] ?? "";
// 4. VALIDAR DATOS
if ($nombre === "") {
    header("Location: Admin.php?tab=agregar&error=nombre");
    exit;
}
if ($carpeta === "") {
    header("Location: Admin.php?tab=agregar&error=carpeta");
    exit;
}
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $carpeta)) {
    header("Location: Admin.php?tab=agregar&error=carpeta_invalida");
    exit;
}
if ($puntosMaximos === "" || !is_numeric($puntosMaximos) || $puntosMaximos < 0) {
    header("Location: Admin.php?tab=agregar&error=puntos");
    exit;
}
// 5. COMPROBAR QUE SE HAYA SUBIDO UN ARCHIVO
if (!isset($_FILES["archivo_juego"])) {
    header("Location: Admin.php?tab=agregar&error=archivo");
    exit;
}
$archivo = $_FILES["archivo_juego"];
// 6. COMPROBAR ERRORES DE SUBIDA
if ($archivo["error"] !== UPLOAD_ERR_OK) {
    header("Location: Admin.php?tab=agregar&error=subida");
    exit;
}
// 7. COMPROBAR QUE SEA UN ZIP
$extension = strtolower(
    pathinfo($archivo["name"], PATHINFO_EXTENSION)
);
if ($extension !== "zip") {

    header("Location: Admin.php?tab=agregar&error=zip");
    exit;
}
// 8. COMPROBAR QUE ZIPARCHIVE ESTÉ DISPONIBLE
if (!class_exists("ZipArchive")) {
    echo "<h2>Error</h2>";
    echo "<p>La extensión ZIP de PHP no está habilitada.</p>";
    echo "<p>Activá la extensión ZIP en php.ini y reiniciá Apache.</p>";
    exit;
}
// 9. CREAR CONEXIÓN
try {
    $conexion = Conexion::conectar();
} catch (PDOException $e) {
    echo "Error al conectar con la base de datos.";
    exit;
}
// 10. COMPROBAR QUE NO EXISTA OTRO JUEGO CON ESA CARPETA
$consulta = $conexion->prepare(
    "SELECT ID_J
     FROM JUEGO
     WHERE Nombre = ?"
);
$consulta->execute([$nombre]);
if ($consulta->fetch()) {
    header("Location: Admin.php?tab=agregar&error=nombre_existe");
    exit;
}
// 11. DEFINIR CARPETA DE JUEGOS
$carpetaJuegos = dirname(__DIR__) . DIRECTORY_SEPARATOR . "juegos";
// Si no existe /juegos/, crearla.
if (!is_dir($carpetaJuegos)) {
    if (!mkdir($carpetaJuegos, 0777, true)) {
        header("Location: Admin.php?tab=agregar&error=carpeta_juegos");
        exit;
    }
}
// 12. CREAR CARPETA DEL JUEGO
$rutaJuego = $carpetaJuegos
           . DIRECTORY_SEPARATOR
           . $carpeta;
// Comprobar que no exista.
if (file_exists($rutaJuego)) {
    header("Location: Admin.php?tab=agregar&error=carpeta_existe");
    exit;
}
if (!mkdir($rutaJuego, 0777, true)) {
    header("Location: Admin.php?tab=agregar&error=crear_carpeta");
    exit;
}
// 13. ABRIR EL ZIP
$zip = new ZipArchive();
$resultado = $zip->open($archivo["tmp_name"]);
if ($resultado !== true) {
    rmdir($rutaJuego);
    header("Location: Admin.php?tab=agregar&error=zip_invalido");
    exit;
}
// 14. COMPROBAR LOS ARCHIVOS DEL ZIP
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
for ($i = 0; $i < $zip->numFiles; $i++) {
    $entrada = $zip->getNameIndex($i);
    if ($entrada === false) {
        $zip->close();
        header("Location: Admin.php?tab=agregar&error=zip_invalido");
        exit;
    }
    // Normalizar las barras.
    $entrada = str_replace("\\", "/", $entrada);
    // PROTECCIÓN CONTRA ZIP SLIP
    if (
        str_starts_with($entrada, "/") ||
        preg_match('#(^|/)\.\.(/|$)#', $entrada)
    ) {
        $zip->close();
        // Eliminar carpeta creada.
        eliminarCarpeta($rutaJuego);
        header("Location: Admin.php?tab=agregar&error=zip_seguridad");
        exit;
    }
    // IGNORAR CARPETAS
    if (str_ends_with($entrada, "/")) {
        continue;
    }
    // COMPROBAR EXTENSIÓN
    $extensionArchivo = strtolower(
        pathinfo($entrada, PATHINFO_EXTENSION)
    );
    if (!in_array($extensionArchivo, $archivosPermitidos)) {
        $zip->close();
        eliminarCarpeta($rutaJuego);
        header("Location: Admin.php?tab=agregar&error=archivo_no_permitido");
        exit;
    }
    // COMPROBAR INDEX.HTML
    if ($entrada === "index.html") {
        $indexEncontrado = true;
    }
}
// 15. EL ZIP DEBE TENER INDEX.HTML
if (!$indexEncontrado) {
    $zip->close();
    eliminarCarpeta($rutaJuego);
    header("Location: Admin.php?tab=agregar&error=index");
    exit;
}
// 16. EXTRAER ARCHIVOS DE FORMA CONTROLADA
for ($i = 0; $i < $zip->numFiles; $i++) {
    $entrada = $zip->getNameIndex($i);
    $entrada = str_replace("\\", "/", $entrada);
    // Ignorar carpetas.
    if (str_ends_with($entrada, "/")) {
        $carpetaDestino = $rutaJuego
                        . DIRECTORY_SEPARATOR
                        . $entrada;
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }
        continue;
    }
    // Ruta final del archivo.
    $archivoDestino = $rutaJuego
                    . DIRECTORY_SEPARATOR
                    . str_replace("/", DIRECTORY_SEPARATOR, $entrada);
    // Crear carpeta padre.
    $carpetaPadre = dirname($archivoDestino);
    if (!is_dir($carpetaPadre)) {
        mkdir($carpetaPadre, 0777, true);
    }
    // Abrir archivo dentro del ZIP.
    $entradaZip = $zip->getStream($entrada);
    if ($entradaZip === false) {
        $zip->close();
        eliminarCarpeta($rutaJuego);
        header("Location: Admin.php?tab=agregar&error=extraccion");
        exit;
    }
    // Crear archivo.
    $archivoSalida = fopen($archivoDestino, "wb");
    if ($archivoSalida === false) {
        fclose($entradaZip);
        $zip->close();
        eliminarCarpeta($rutaJuego);
        header("Location: Admin.php?tab=agregar&error=extraccion");
        exit;
    }
    // Copiar contenido.
    stream_copy_to_stream(
        $entradaZip,
        $archivoSalida
    );
    fclose($entradaZip);
    fclose($archivoSalida);
}
$zip->close();
// 17. REGISTRAR EL JUEGO EN LA BASE DE DATOS
try {
    $conexion->beginTransaction();
    // INSERTAR JUEGO
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
    // REGISTRAR ACCIÓN DEL ADMINISTRADOR
    $idAdministrador = $_SESSION["id_usuario"] ?? null;
    if ($idAdministrador !== null) {
        $accion = "Agregó el juego "
                . $nombre
                . " (ID: "
                . $idJuego
                . ")";
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
    // Confirmar cambios.
    $conexion->commit();
} catch (PDOException $e) {
    // Si ocurre un error en la BD,
    // deshacer la transacción.
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    // Eliminar los archivos que acabamos de instalar.
    eliminarCarpeta($rutaJuego);
    echo "<h2>Error al registrar el juego</h2>";
    echo "<p>No se pudo registrar el juego en la base de datos.</p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='Admin.php?tab=agregar'>Volver</a></p>";
    exit;
}
// 18. VOLVER AL PANEL
header(
    "Location: Admin.php?tab=agregar&exito=1"
);
exit;
// FUNCIÓN PARA ELIMINAR CARPETAS
function eliminarCarpeta($carpeta)
{
    if (!is_dir($carpeta)) {
        return;
    }
    $elementos = scandir($carpeta);
    foreach ($elementos as $elemento) {

        if ($elemento === "." || $elemento === "..") {
            continue;
        }
        $ruta = $carpeta
              . DIRECTORY_SEPARATOR
              . $elemento;
        if (is_dir($ruta)) {
            eliminarCarpeta($ruta);
        } else {
            unlink($ruta);
        }
    }
    rmdir($carpeta);
}
?>