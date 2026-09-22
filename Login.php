<?php
require_once "Instalar.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Stardust Crusaders</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="row g-0">
                    <!-- FORMULARIO -->
                    <div class="col-md-6 p-4">
                        <h2 class="text-center mb-4">
                            Iniciar Sesión
                        </h2>
                        <?php if (isset($_GET["error"])): ?>
                            <div class="alert alert-danger">
                                Usuario o contraseña incorrectos.
                            </div>
                        <?php endif; ?>
                        <form action="auth.php" method="POST">
                            <!-- USUARIO -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Usuario
                                </label>
                                <input
                                    type="text"
                                    name="usuario"
                                    class="form-control"
                                    required
                                >
                            </div>
                            <!-- CONTRASEÑA -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Contraseña
                                </label>
                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    required
                                >
                            </div>
                            <!-- BOTÓN -->
                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                Entrar
                            </button>
                        </form>
                    </div>
                    <!-- IMAGEN -->
                    <div class="col-md-6 d-flex align-items-center">
                        <img
                            src="https://picsum.photos/400/300"
                            class="img-fluid rounded-end"
                            alt="Imagen login"
                        >
                    </div>
                    <p class="text-center mt-3">
                    ¿No tenés una cuenta?
                    <a href="Registro.php">Registrate</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>