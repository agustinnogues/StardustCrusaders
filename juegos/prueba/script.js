let valor1 = 0;
let valor2 = 0;
const puntosGanados = 10; // Puntos fijos al acertar
let guardandoPuntaje = false;

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
        
        guardarPuntaje(puntosGanados);
    } else {
        mensaje.textContent = `Incorrecto. El resultado era ${resultadoCorrecto}. Inténtalo de nuevo.`;
        mensaje.style.color = "red";
        setTimeout(generarSuma, 2000); // Genera otra suma tras 2 segundos
    }
}

function guardarPuntaje(puntos) {
    if (guardandoPuntaje) {
        return;
    }
    guardandoPuntaje = true;

    window.StardustPuntajes.guardar(puntos)
    .then(() => {
            mensaje.textContent = "¡Correcto! Puntos guardados con éxito. Redirigiendo al perfil...";
            setTimeout(() => {
                // Sube a la raíz del proyecto para volver al perfil.
                window.location.href = "../../Gestionusuarios/Perfil.php";
            }, 1500);
    })
    .catch(error => {
        guardandoPuntaje = false;
        console.error("Error al guardar el puntaje:", error);
        mensaje.textContent = "Error al guardar: " + error.message;
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