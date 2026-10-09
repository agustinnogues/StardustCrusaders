<?php
session_start();
require_once "conversorPuntaje.php";

header("Content-Type: application/json; charset=utf-8");

function responderError($mensaje, $codigo) {
    http_response_code($codigo);
    echo json_encode(["status" => "error", "mensaje" => $mensaje]);
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

if (!$resultado) {
    responderError("No se pudo guardar en la base de datos", 500);
}

echo json_encode(["status" => "success", "mensaje" => "Puntaje guardado correctamente"]);