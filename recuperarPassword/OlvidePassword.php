<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
        <div class="card-body">
            <h3 class="card-title text-center mb-3">¿Olvidaste tu contraseña?</h3>
            <p class="text-muted text-center small mb-4">
                Ingresa tu correo electrónico registrado y te enviaremos las instrucciones para restablecerla.
            </p>

            <!-- Formulario que envía los datos al procesador dentro de la misma carpeta -->
            <form action="ProcesarOlvidePassword.php" method="POST">
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo" required placeholder="tu-correo@ejemplo.com">
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary">Enviar enlace de recuperación</button>
                </div>
                <div class="text-center">
                    <a href="../GestionSesion/Login.php" class="text-decoration-none small">Volver al inicio de sesión</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>