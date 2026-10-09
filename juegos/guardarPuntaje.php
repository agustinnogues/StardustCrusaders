<?php
session_start();
require_once "conversorPuntaje.php";

header("Content-Type: application/json; charset=utf-8");

function responderError($mensaje, $codigo) {
    http_response_code($codigo);
    echo json_encode(["status" => "error", "mensaje" => $mensaje]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $idJuego = filter_input(INPUT_GET, "id_juego", FILTER_VALIDATE_INT);
    if ($idJuego === false || $idJuego === null || $idJuego <= 0) {
        responderError("El identificador del juego debe ser un entero válido", 400);
    }

    try {
        $puntajeMaximo = conversorPuntajes::obtenerPuntajeMaximo($idJuego);
    } catch (Exception $e) {
        error_log("No se pudo consultar el puntaje máximo del juego: " . $e->getMessage());
        responderError("No se pudo consultar el puntaje máximo", 500);
    }

    if ($puntajeMaximo === null) {
        responderError("No se encontró el juego solicitado", 404);
    }

    echo json_encode(["status" => "success", "puntaje_maximo" => $puntajeMaximo]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responderError("Método no permitido", 405);
}

if (empty($_SESSION["id_usuario"])) {
    responderError("Usuario no autenticado", 401);
}

$idJuego = filter_input(INPUT_POST, "id_juego", FILTER_VALIDATE_INT);
$puntos = filter_input(INPUT_POST, "puntos", FILTER_VALIDATE_INT);

if ($idJuego === false || $idJuego === null || $idJuego <= 0 || $puntos === false || $puntos === null) {
    responderError("El identificador del juego y el puntaje deben ser enteros válidos", 400);
}

$resultado = conversorPuntajes::guardarPartida(
    (int)$_SESSION["id_usuario"],
    $idJuego,
    $puntos
);

if ($resultado === null) {
    responderError("El juego no existe o el puntaje supera el máximo permitido", 400);
}
if ($resultado === false) {
    responderError("No se pudo guardar en la base de datos", 500);
}

echo json_encode(["status" => "success", "mensaje" => "Puntaje guardado correctamente"]);