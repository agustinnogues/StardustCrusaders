<?php
// Incluimos la conexión a la base de datos (subiendo un nivel con ../ porque está en la raíz)
require_once '../Conexion.php';

// Importamos las clases necesarias de PHPMailer (buscando la carpeta vendor en la raíz)
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);

    try {
        // Obtenemos la conexión PDO usando tu clase Conexion
        $pdo = Conexion::conectar();

        // 1. Verificamos si el correo existe en tu tabla de usuarios
        $sql = "SELECT * FROM USUARIO WHERE Correo_Electronico = :correo";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['correo' => $correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // 2. Generamos un token seguro y único
            $token = bin2hex(random_bytes(32));

            // 3. Definimos la expiración (1 hora a partir de ahora)
            $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // 4. Guardamos el token en la tabla independiente RECUPERACION_PASSWORD
            $sqlInsert = "INSERT INTO RECUPERACION_PASSWORD (Correo_Electronico, Token, Expiracion) VALUES (:correo, :token, :expiracion)";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                'correo' => $correo,
                'token' => $token,
                'expiracion' => $expiracion
            ]);

            // 5. Configuramos y enviamos el correo con PHPMailer
            $mail = new PHPMailer(true);

            // Configuraciones del servidor SMTP de Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            // Tu correo configurado
            $mail->Username   = 'agustinnogues1@gmail.com'; 
            // Tu contraseña de aplicación de 16 caracteres
            $mail->Password   = 'cambiarDespues'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Remitente y destinatario
            $mail->setFrom('agustinnogues1@gmail.com', 'Soporte Stardust Crusaders');
            $mail->addAddress($correo); // El correo del usuario que solicitó recuperar

            // Contenido del correo
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Recuperación de contraseña';
            
            // Enlace que redirige al siguiente archivo pasando el token por la URL
            $enlace = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/RestablecerPassword.php?token=" . $token;

            $mail->Body    = '
                <div style="font-family: Arial, sans-serif; color: #333;">
                    <h2>Solicitud de Recuperación de Contraseña</h2>
                    <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente botón para continuar:</p>
                    <a href="' . $enlace . '" style="background-color: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Restablecer Contraseña</a>
                    <p style="margin-top: 20px; font-size: 12px; color: #666;">Si no solicitaste esto, puedes ignorar este mensaje. Este enlace expirará en 1 hora.</p>
                </div>';

            $mail->send();

            // Mensaje de éxito visual para el usuario
            echo "<div class='container mt-5'><div class='alert alert-success text-center'>¡Correo enviado con éxito! Revisa tu bandeja de entrada para restablecer tu contraseña. <br><a href='../Login.php' class='alert-link'>Volver al Login</a></div></div>";

        } else {
            echo "<div class='container mt-5'><div class='alert alert-danger text-center'>El correo electrónico no está registrado en el sistema. <br><a href='OlvidePassword.php' class='alert-link'>Intentar de nuevo</a></div></div>";
        }

    } catch (Exception $e) {
        echo "<div class='container mt-5'><div class='alert alert-danger text-center'>Hubo un error al enviar el correo: " . $e->getMessage() . "</div></div>";
    }
}