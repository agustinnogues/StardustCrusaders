// VARIABLES DEL JUEGO
let puntos = 0;
let nivel = 1;
let juegoActivo = true;
// ELEMENTOS HTML
const mensajeElemento = document.getElementById("mensaje");
const botones = document.querySelectorAll(".opciones button");
// DIRECCIONES
const direcciones = [
    "izquierda",
    "frente",
    "derecha"
];
// CAMINO CORRECTO
let caminoCorrecto;
// GENERAR NUEVO CAMINO
function generarCamino() {
    const numeroAleatorio = Math.floor(
        Math.random() * direcciones.length
    );
    caminoCorrecto = direcciones[numeroAleatorio];
}
// GENERAR LABERINTO
// Esta función utiliza RECURSIVIDAD.
// Cada vez que se genera un nuevo tramo,
// la función puede volver a generar otro.
// La condición "nivel >= 30" funciona como
// condición de parada para la generación
// inicial del laberinto.
function generarLaberinto(nivelActual) {
    if (nivelActual >= 30) {
        return;
    }
    crearTramoVisual(nivelActual);
    generarLaberinto(nivelActual + 1);
}
// CREAR TRAMO VISUAL
function crearTramoVisual(numero) {
    const laberinto = document.getElementById("laberinto");
    /*
        Por ahora solamente modificamos
        ligeramente el aspecto del laberinto
        dependiendo del tramo.
    */
    const intensidad = Math.min(
        30 + numero,
        80
    );
    laberinto.style.borderColor =
        `rgb(${intensidad}, ${intensidad}, ${intensidad})`;
}
// ELEGIR CAMINO
function elegirCamino(eleccion) {
    // Si el juego terminó, no hacemos nada.
    if (!juegoActivo) {
        return;
    }
    // Desactivamos los botones mientras
    // mostramos el resultado.
    botones.forEach(boton => {
        boton.disabled = true;
    });
    // COMPROBAR ELECCIÓN
    if (eleccion === caminoCorrecto) {
        // CAMINO CORRECTO
        puntos++;
        mensajeElemento.textContent =
            "Sientes una brisa refrescante y el olor pasto";
        mensajeElemento.className =
            "mensaje correcto";
    } else {
        /*
            Si se equivocó, tenemos dos
            posibilidades:
            0 puntos
            o
            -1 punto
            Esto permite que cada elección
            tenga uno de los tres resultados:  
            +1
             0
            -1
        */
        const resultado = Math.random();
        if (resultado < 0.5) {
            // Resultado neutral
            mensajeElemento.textContent =
                "El olor a humedad inunda tu nariz";
            mensajeElemento.className =
                "mensaje neutral";
            // No modificamos puntos.
        } else {
            // Resultado negativo
            puntos--;
            mensajeElemento.textContent =
                "Un miasma te rodea y la oscuridad se sierne sobre ti";
            mensajeElemento.className =
                "mensaje error";
        }
    }
    // COMPROBAR SI TERMINÓ EL JUEGO
    if (puntos >= 5) {
        ganar();
        return;
    }
    if (puntos <= -5) {
        perder();
        return;
    }
    // SIGUIENTE TRAMO
    nivel++;
 // Esperamos antes de habilitar
// nuevamente los caminos 
    setTimeout(() => {
        generarCamino();
        mensajeElemento.textContent =
            "3 nuevos caminos se presentan ante ti, elige sabiamente";
        mensajeElemento.className =
            "mensaje";
        botones.forEach(boton => {
            boton.disabled = false;
        });
    }, 700);
}
// GANAR
function ganar() {
    juegoActivo = false;
    mensajeElemento.textContent =
        "Has escapado del laberinto";
    mensajeElemento.className =
        "mensaje ganaste";
    botones.forEach(boton => {
        boton.disabled = true;
    });
}
// PERDER
function perder() {
    juegoActivo = false;
    mensajeElemento.textContent =
        "Te has perdido en el laberinto";
    mensajeElemento.className =
        "mensaje perdiste";
    botones.forEach(boton => {
        boton.disabled = true;
    });
}
// REINICIAR
function reiniciarJuego() {
    puntos = 0;
    nivel = 1;
    juegoActivo = true;
    mensajeElemento.textContent =
        "Elige uno de los tres caminos...";
    mensajeElemento.className =
        "mensaje";
    botones.forEach(boton => {
        boton.disabled = false;
    });
    generarCamino();
}
// INICIAR JUEGO
generarCamino();
generarLaberinto(1);