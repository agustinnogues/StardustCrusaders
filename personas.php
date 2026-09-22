<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

require_once "Conexion.php";

$conexion = Conexion::conectar();

$sql = "SELECT ID_U, Nombre_Usuario, Correo_Electronico, Rol, Rango
        FROM USUARIO";

$stmt = $conexion->prepare($sql);
$stmt->execute();

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de usuarios</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body>

<?php include("includes/header.php"); ?>

<section class="titulo">
    <h2>Listado de usuarios</h2>
</section>

<section class="informacion">

```
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre de usuario</th>
            <th>Correo electrónico</th>
            <th>Rol</th>
            <th>Rango</th>
        </tr>
    </thead>

    <tbody>

        <?php foreach ($usuarios as $usuario): ?>

            <tr>
                <td><?= $usuario["ID_U"] ?></td>
                <td><?= htmlspecialchars($usuario["Nombre_Usuario"]) ?></td>
                <td><?= htmlspecialchars($usuario["Correo_Electronico"]) ?></td>
                <td><?= $usuario["Rol"] ? "Administrador" : "Usuario" ?></td>
                <td><?= $usuario["Rango"] ? "Rango alto" : "Rango bajo" ?></td>
            </tr>

        <?php endforeach; ?>

    </tbody>
</table>
```

</section>

<?php include("includes/footer.php"); ?>

</body>
</html>
