let puntos = 0;
let nivel = 1;
let juegoActivo = true;
const mensajeElemento = document.getElementById("mensaje");
const botones = document.querySelectorAll(".opciones button");
// Las direcciones
const direcciones = [
    "izquierda",
    "frente",
    "derecha"
];
// Camino correcto
let caminoCorrecto;
// Genera nuevo camino
function generarCamino() {
    const numeroAleatorio = Math.floor(
        Math.random() * direcciones.length
    );
    caminoCorrecto = direcciones[numeroAleatorio];
}
// Genera al laberinto
function generarLaberinto(nivelActual) {
    if (nivelActual >= 30) {
        return;
    }
    crearTramoVisual(nivelActual);
    generarLaberinto(nivelActual + 1);
}
// Crea el tramo visual
function crearTramoVisual(numero) {
    const laberinto = document.getElementById("laberinto");
    const intensidad = Math.min(
        30 + numero,
        80
    );
    laberinto.style.borderColor =
        `rgb(${intensidad}, ${intensidad}, ${intensidad})`;
}
// Elegir el camino
function elegirCamino(eleccion) {
    // Si el juego terminó, no hacemos nada.
    if (!juegoActivo) {
        return;
    }
    // Se desactivan los botones mientras se muestran los resultados
    botones.forEach(boton => {
        boton.disabled = true;
    });
    // Comprueba la eleccion
    if (eleccion === caminoCorrecto) {
        // Camino correcto
        puntos++;
        mensajeElemento.textContent =
            "Sientes una brisa refrescante y el olor pasto";
        mensajeElemento.className =
            "mensaje correcto";
    } else {
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
    // Comprobar si gano
    if (puntos >= 5) {
        ganar();
        return;
    }
    if (puntos <= -5) {
        perder();
        return;
    }
    // Siguente tramo
    nivel++;
 // Espera antes de habilitar nuevamente los caminos
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
// Ganar :D
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
// Perder :C
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
// Reiniciar Juego
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
// iniciar Juego
generarCamino();
generarLaberinto(1);