<?php
session_start();
require_once "conversorPuntaje.php";

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "Usuario no autenticado"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id_usuario'];
    $id_juego = $_POST['id_juego'] ?? null;
    $puntos = $_POST['puntos'] ?? null;

    if ($id_juego && $puntos !== null) {
        $resultado = conversorPuntajes::guardarPartida($id_usuario, $id_juego, $puntos);
        
        if ($resultado) {
            echo json_encode(["status" => "success", "mensaje" => "Puntaje guardado correctamente"]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "No se pudo guardar en la base de datos"]);
        }
    } else {
        echo json_encode(["status" => "error", "mensaje" => "Datos incompletos"]);
    }
} else {
    echo json_encode(["status" => "error", "mensaje" => "Método no permitido"]);
}