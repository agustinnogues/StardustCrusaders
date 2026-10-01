let valor1 = 0;
let valor2 = 0;
const puntosGanados = 10; // Puntos fijos al acertar

function generarSuma() {
    valor1 = Math.floor(Math.random() * 20) + 1; // Número aleatorio entre 1 y 20
    valor2 = Math.floor(Math.random() * 20) + 1;
    
    document.getElementById("num1").textContent = valor1;
    document.getElementById("num2").textContent = valor2;
    document.getElementById("respuestaUsuario").value = "";
    document.getElementById("respuestaUsuario").focus();
}

function verificarRespuesta() {
    const respuestaInput = document.getElementById("respuestaUsuario").value;
    const mensaje = document.getElementById("mensaje");

    if (respuestaInput === "") {
        mensaje.textContent = "Por favor ingresa un número.";
        mensaje.style.color = "orange";
        return;
    }

    const respuestaNumerica = parseInt(respuestaInput);
    const resultadoCorrecto = valor1 + valor2;

    if (respuestaNumerica === resultadoCorrecto) {
        mensaje.textContent = "¡Correcto! Guardando puntos...";
        mensaje.style.color = "green";
        
        // Enviamos los puntos al servidor
        enviarPuntajeServidor(puntosGanados);
    } else {
        mensaje.textContent = `Incorrecto. El resultado era ${resultadoCorrecto}. Inténtalo de nuevo.`;
        mensaje.style.color = "red";
        setTimeout(generarSuma, 2000); // Genera otra suma tras 2 segundos
    }
}

function enviarPuntajeServidor(puntos) {
    const datosPartida = new URLSearchParams();
    datosPartida.append('id_juego', 1); // ⚠️ Asegúrate de que este ID coincida con el juego en tu tabla JUEGO
    datosPartida.append('puntos', puntos);

    // Ruta correcta: Sube de 'prueba' a 'Juegos' donde está guardarPuntaje.php
    fetch('../guardarPuntaje.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: datosPartida
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            mensaje.textContent = "¡Correcto! Puntos guardados con éxito. Redirigiendo al perfil...";
            setTimeout(() => {
                // Sube de 'prueba' -> 'Juegos' -> 'Pagina' para encontrar Perfil.php
                window.location.href = "../../Perfil.php";
            }, 1500);
        } else {
            mensaje.textContent = "Error al guardar: " + (data.mensaje || "Desconocido");
            mensaje.style.color = "red";
        }
    })
    .catch(error => {
        console.error('Error de red:', error);
        mensaje.textContent = "Error de conexión con el servidor.";
        mensaje.style.color = "red";
    });
}

// Permitir presionar "Enter" para enviar la respuesta
document.getElementById("respuestaUsuario").addEventListener("keypress", function(event) {
    if (event.key === "Enter") {
        verificarRespuesta();
    }
});

// Iniciar juego al cargar la página
generarSuma();