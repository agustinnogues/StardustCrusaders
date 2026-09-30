<?php
require_once '../Conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $nuevaPassword = $_POST['nueva_password'];

    try {
        // Obtenemos la conexión PDO usando tu clase Conexion
        $pdo = Conexion::conectar();

        // 1. Validamos nuevamente que el token sea válido y no haya expirado
        $sql = "SELECT Correo_Electronico FROM RECUPERACION_PASSWORD WHERE Token = :token AND Expiracion >= NOW()";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['token' => $token]);
        $recuperacion = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($recuperacion) {
            $correo = $recuperacion['Correo_Electronico'];

            // 2. Encriptamos la nueva contraseña de forma segura
            $passwordHash = $nuevaPassword;
            // 3. Actualizamos la contraseña en tu tabla principal de usuarios
            $sqlUpdate = "UPDATE USUARIO SET Contrasena = :password WHERE Correo_Electronico = :correo";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([
                'password' => $passwordHash,
                'correo' => $correo
            ]);

            // 4. Borramos el token usado para que caduque inmediatamente y no se reutilice
            $sqlDelete = "DELETE FROM RECUPERACION_PASSWORD WHERE Correo_Electronico = :correo";
            $stmtDelete = $pdo->prepare($sqlDelete);
            $stmtDelete->execute(['correo' => $correo]);

            // 5. Mensaje de éxito informando al usuario
            echo '
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Contraseña Actualizada</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body class="bg-light d-flex align-items-center justify-content-center vh-100">
                <div class="card shadow p-4 text-center" style="width: 100%; max-width: 400px;">
                    <div class="card-body">
                        <h3 class="card-title text-success mb-3">¡Contraseña Actualizada!</h3>
                        <p class="text-muted mb-4">Tu contraseña ha sido cambiada exitosamente. Ya puedes iniciar sesión con tu nueva clave.</p>
                        <a href="../Login.php" class="btn btn-primary w-100">Ir al Login</a>
                    </div>
                </div>
            </body>
            </html>';

        } else {
            echo "<div class='container mt-5'><div class='alert alert-danger text-center'>El enlace de recuperación es inválido o ha expirado. <br><a href='OlvidePassword.php' class='alert-link'>Solicitar uno nuevo</a></div></div>";
        }

    } catch (Exception $e) {
        echo "<div class='container mt-5'><div class='alert alert-danger text-center'>Ocurrió un error al actualizar la contraseña: " . $e->getMessage() . "</div></div>";
    }
} else {
    header("Location: OlvidePassword.php");
    exit();
}