<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Stardust Crusaders</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">
                        Crear cuenta
                    </h2>
                    <form action="registrar.php" method="POST">
                        <!-- USUARIO -->
                        <div class="mb-3">
                            <label class="form-label">
                                Nombre de usuario
                            </label>
                            <input
                                type="text"
                                name="usuario"
                                class="form-control"
                                required
                            >
                        </div>
                        <!-- CORREO -->
                        <div class="mb-3">
                            <label class="form-label">
                                Correo electrónico
                            </label>
                            <input
                                type="email"
                                name="correo"
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
                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Registrarse
                        </button>
                    </form>
                    <p class="text-center mt-3">
                        ¿Ya tenés una cuenta?
                        <a href="Login.php">
                            Iniciar sesión
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>