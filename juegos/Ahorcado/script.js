"use strict";

const categorias = {
    animales: [
        "elefante", "jirafa", "mariposa", "cocodrilo", "pingüino",
        "murciélago", "tortuga", "delfín", "rinoceronte", "hipopótamo",
        "canguro", "ardilla", "camaleón", "flamenco", "leopardo",
        "carpincho", "ballena", "iguana", "gallina", "serpiente"
    ],
    paises: [
        "argentina", "brasil", "canadá", "colombia", "uruguay",
        "paraguay", "alemania", "portugal", "japón", "australia",
        "méxico", "ecuador", "venezuela", "italia", "francia",
        "sudáfrica", "noruega", "marruecos", "corea del sur", "nueva zelanda"
    ],
    colores: [
        "rojo", "azul", "amarillo", "verde", "violeta",
        "naranja", "turquesa", "magenta", "celeste", "marrón",
        "gris", "blanco", "negro", "dorado", "plateado",
        "bordó", "coral", "índigo", "beige", "lavanda"
    ],
    clubes: [
        "boca juniors", "river plate", "san lorenzo", "racing club", "independiente",
        "newells old boys", "rosario central", "estudiantes", "vélez sarsfield", "huracán",
        "real madrid", "barcelona", "atlético de madrid", "manchester city", "liverpool",
        "chelsea", "arsenal", "paris saint germain", "bayern múnich", "inter de milán"
    ],
    videojuegos: [
        "minecraft", "zelda", "tetris", "fortnite", "minecraft dungeons",
        "pacman", "sonic", "kirby", "overwatch", "stardew valley",
        "hollow knight", "celeste", "terraria", "undertale", "god of war",
        "animal crossing", "street fighter", "super mario", "portal", "crash bandicoot"
    ]
};

const alfabeto = "abcdefghijklmnñopqrstuvwxyzáéíóúü";
const maxErrores = 6;
const categoriaSelect = document.getElementById("categoria");
const palabraElemento = document.getElementById("palabra");
const mensajeElemento = document.getElementById("mensaje");
const tecladoElemento = document.getElementById("teclado");
const formulario = document.getElementById("formularioLetra");
const entradaLetra = document.getElementById("letra");
const contadorErrores = document.getElementById("contadorErrores");
const botonNuevoJuego = document.getElementById("nuevoJuego");
const expresionLetra = /^[a-záéíóúüñ]$/i;

let palabraSecreta = "";
let letrasAdivinadas = new Set();
let errores = 0;
let partidaActiva = false;
let numeroPartida = 0;
let puntajeMaximo = null;

function normalizarLetra(letra) {
    return letra.toLocaleLowerCase("es");
}

function construirTeclado() {
    tecladoElemento.replaceChildren();
    for (const letra of alfabeto) {
        const boton = document.createElement("button");
        boton.type = "button";
        boton.className = "tecla";
        boton.textContent = letra;
        boton.setAttribute("aria-label", `Probar letra ${letra}`);
        boton.addEventListener("click", () => probarLetra(letra));
        tecladoElemento.append(boton);
    }
}

function mostrarPalabra() {
    palabraElemento.replaceChildren();
    for (const caracter of palabraSecreta) {
        const casilla = document.createElement("span");
        if (caracter === " ") {
            casilla.className = "casilla espacio";
            casilla.setAttribute("aria-hidden", "true");
        } else {
            casilla.className = "casilla";
            casilla.textContent = letrasAdivinadas.has(caracter) || !expresionLetra.test(caracter)
                ? caracter
                : "_";
        }
        palabraElemento.append(casilla);
    }
    palabraElemento.setAttribute(
        "aria-label",
        Array.from(palabraSecreta, caracter =>
            caracter === " " ? "espacio" : (letrasAdivinadas.has(caracter) ? caracter : "letra oculta")
        ).join(", ")
    );
}

function actualizarTablero() {
    mostrarPalabra();
    contadorErrores.textContent = `Errores: ${errores} de ${maxErrores}`;
    document.querySelectorAll(".parte").forEach(parte => {
        parte.classList.toggle("visible", Number(parte.dataset.error) <= errores);
    });
    tecladoElemento.querySelectorAll(".tecla").forEach(boton => {
        const letra = normalizarLetra(boton.textContent);
        boton.disabled = !partidaActiva || letrasAdivinadas.has(letra);
        if (letrasAdivinadas.has(letra)) {
            boton.setAttribute("aria-pressed", "true");
        } else {
            boton.removeAttribute("aria-pressed");
        }
    });
    entradaLetra.disabled = !partidaActiva;
    formulario.querySelector("button[type='submit']").disabled = !partidaActiva;
}

function palabraCompleta() {
    return Array.from(palabraSecreta).every(caracter =>
        caracter === " " || !expresionLetra.test(caracter) || letrasAdivinadas.has(caracter)
    );
}

// Envía el puntaje de la partida y muestra si el guardado tuvo éxito.
async function guardarPuntaje(puntos, partida) {
    try {
        await window.StardustPuntajes.guardar(puntos);
        if (partida === numeroPartida) {
            mensajeElemento.textContent += ` Puntaje guardado: ${puntos} puntos.`;
        }
    } catch (error) {
        if (partida === numeroPartida) {
            mensajeElemento.textContent += ` No se pudo guardar el puntaje: ${error.message}`;
        }
    }
}

function terminarPartida(gano) {
    partidaActiva = false;
    const puntos = gano ? puntajeMaximo : 0;
    mensajeElemento.className = gano ? "mensaje ganaste" : "mensaje perdiste";
    mensajeElemento.textContent = gano
        ? `¡Muy bien! Adivinaste: ${palabraSecreta}. Ganaste ${puntos} puntos. Guardando...`
        : `Se acabaron los intentos. La palabra era: ${palabraSecreta}. Puntaje: ${puntos}. Guardando...`;
    actualizarTablero();
    void guardarPuntaje(puntos, numeroPartida);
}

function probarLetra(valor) {
    if (!partidaActiva) {
        return;
    }

    const letra = normalizarLetra(valor.trim());
    if (!expresionLetra.test(letra)) {
        mensajeElemento.className = "mensaje error";
        mensajeElemento.textContent = "Ingresá una sola letra válida.";
        entradaLetra.focus();
        return;
    }
    if (letrasAdivinadas.has(letra)) {
        mensajeElemento.className = "mensaje";
        mensajeElemento.textContent = `Ya probaste la letra "${letra}". Elegí otra.`;
        entradaLetra.value = "";
        entradaLetra.focus();
        return;
    }

    letrasAdivinadas.add(letra);
    if (palabraSecreta.includes(letra)) {
        mensajeElemento.className = "mensaje correcto";
        mensajeElemento.textContent = `¡Bien! La letra "${letra}" está en la palabra.`;
        if (palabraCompleta()) {
            terminarPartida(true);
            return;
        }
    } else {
        errores += 1;
        mensajeElemento.className = "mensaje error";
        mensajeElemento.textContent = `La letra "${letra}" no está en la palabra.`;
        if (errores === maxErrores) {
            terminarPartida(false);
            return;
        }
    }

    actualizarTablero();
    entradaLetra.value = "";
    entradaLetra.focus();
}

function iniciarJuego() {
    if (puntajeMaximo === null) {
        return;
    }
    numeroPartida += 1;
    const palabras = categorias[categoriaSelect.value];
    palabraSecreta = palabras[Math.floor(Math.random() * palabras.length)];
    letrasAdivinadas = new Set();
    errores = 0;
    partidaActiva = true;
    mensajeElemento.className = "mensaje";
    mensajeElemento.textContent = "Elegí una letra para empezar.";
    entradaLetra.value = "";
    actualizarTablero();
}

formulario.addEventListener("submit", evento => {
    evento.preventDefault();
    probarLetra(entradaLetra.value);
    entradaLetra.value = "";
});

botonNuevoJuego.addEventListener("click", iniciarJuego);
categoriaSelect.addEventListener("change", iniciarJuego);

construirTeclado();
window.StardustPuntajes.obtenerPuntajeMaximo()
    .then(maximo => {
        puntajeMaximo = maximo;
        document.getElementById("notaPuntaje").textContent =
            `Adivinar la palabra otorga ${puntajeMaximo} puntos. Si se completa el dibujo, la partida vale 0 puntos.`;
        iniciarJuego();
    })
    .catch(error => {
        mensajeElemento.className = "mensaje error";
        mensajeElemento.textContent = `No se pudo cargar el máximo de puntos: ${error.message}`;
    });
