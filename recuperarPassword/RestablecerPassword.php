<?php
require_once '../Conexion.php';

$mensaje = "";
$tokenValido = false;

// 1. Verificamos que el token llegue por la URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        // Obtenemos la conexión PDO usando tu clase Conexion
        $pdo = Conexion::conectar();

        // 2. Buscamos el token en la base de datos y validamos que no haya expirado
        $sql = "SELECT * FROM RECUPERACION_PASSWORD WHERE Token = :token AND Expiracion >= NOW()";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['token' => $token]);
        $recuperacion = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($recuperacion) {
            $tokenValido = true;
        } else {
            $mensaje = "El enlace de recuperación es inválido o ha expirado.";
        }
    } catch (Exception $e) {
        $mensaje = "Error en la base de datos: " . $e->getMessage();
    }
} else {
    $mensaje = "No se ha proporcionado ningún token de recuperación.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
        <div class="card-body">
            <h3 class="card-title text-center mb-3">Nueva Contraseña</h3>

            <?php if ($tokenValido): ?>
                <!-- Formulario para escribir la nueva contraseña -->
                <form action="ActualizarPassword.php" method="POST">
                    <!-- Pasamos el token oculto para usarlo en el siguiente archivo -->
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="mb-3">
                        <label for="nueva_password" class="form-label">Ingresa tu nueva contraseña</label>
                        <input type="password" class="form-control" id="nueva_password" name="nueva_password" required placeholder="Mínimo 6 caracteres">
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-success">Actualizar contraseña</button>
                    </div>
                </form>
            <?php else: ?>
                <!-- Mensaje de error si el token no sirve -->
                <div class="alert alert-danger text-center">
                    <?php echo $mensaje; ?>
                </div>
                <div class="d-grid">
                    <a href="OlvidePassword.php" class="btn btn-primary">Solicitar nuevo enlace</a>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>