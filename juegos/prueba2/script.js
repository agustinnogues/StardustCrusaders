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
    const intensidad = Math.min(
        30 + numero,
        80
    );
    laberinto.style.borderColor =
        `rgb(${intensidad}, ${intensidad}, ${intensidad})`;
}
// ELEGIR CAMINO
function elegirCamino(eleccion) {
    if (!juegoActivo) {
        return;
    }
    botones.forEach(boton => {
        boton.disabled = true;
    });
    if (eleccion === caminoCorrecto) {
        puntos++;
        mensajeElemento.textContent =
            "Sientes una brisa refrescante y el olor pasto";
        mensajeElemento.className =
            "mensaje correcto";
    } else {
        const resultado = Math.random();
        if (resultado < 0.5) {
            mensajeElemento.textContent =
                "El olor a humedad inunda tu nariz";
            mensajeElemento.className =
                "mensaje neutral";
        } else {
            puntos--;
            mensajeElemento.textContent =
                "Un miasma te rodea y la oscuridad se sierne sobre ti";
            mensajeElemento.className =
                "mensaje error";
        }
    }
    if (puntos >= 5) {
        ganar();
        return;
    }
    if (puntos <= -5) {
        perder();
        return;
    }
    nivel++;
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

// Guarda una única fila con el puntaje final de la partida.
function guardarPuntajeFinal() {
    window.StardustPuntajes.guardar(puntos)
        .then(() => {
            mensajeElemento.textContent += " Puntaje guardado.";
        })
        .catch(error => {
            console.error("Error al guardar el puntaje:", error);
            mensajeElemento.textContent += " No se pudo guardar: " + error.message;
        });
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
    guardarPuntajeFinal();
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
    guardarPuntajeFinal();
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