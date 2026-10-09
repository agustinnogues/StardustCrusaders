<?php
require_once __DIR__ . '/../Basededatos/Conexion.php';

class conversorPuntajes {

    // Método para guardar una nueva partida
    public static function guardarPartida($id_usuario, $id_juego, $puntos) {
        try {
            $pdo = Conexion::conectar();
            $sql = "INSERT INTO JUE_PAR (ID_U, ID_J, Fecha, Puntos) VALUES (:id_u, :id_j, NOW(), :puntos)";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                'id_u' => $id_usuario,
                'id_j' => $id_juego,
                'puntos' => $puntos
            ]);
        } catch (Exception $e) {
            error_log("No se pudo guardar el resultado de la partida: " . $e->getMessage());
            return false;
        }
    }

   // Método para obtener el puntaje máximo y el total acumulado por juego de un usuario
    public static function obtenerPuntajesPorUsuario($id_usuario) {
        try {
            $pdo = Conexion::conectar();
            
            // Calculamos tanto el MAX (récord) como la SUM (acumulado total)
            $sql = "SELECT j.Nombre, MAX(jp.Puntos) AS Max_Puntos, SUM(jp.Puntos) AS Puntos_Totales 
                    FROM JUE_PAR jp 
                    JOIN JUEGO j ON jp.ID_J = j.ID_J 
                    WHERE jp.ID_U = :id_u 
                    GROUP BY j.ID_J, j.Nombre";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id_u' => $id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>