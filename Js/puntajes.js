(function (global) {
    const script = document.currentScript;
    if (!script || !script.src) {
        throw new Error("No se pudo determinar la ruta de la biblioteca de puntajes.");
    }

    const endpoint = new URL("../juegos/guardarPuntaje.php", script.src).href;

    async function guardar(puntos) {
        const idJuego = new URLSearchParams(global.location.search).get("id_juego");
        const puntaje = Number(puntos);

        if (!idJuego || !Number.isInteger(Number(idJuego)) || Number(idJuego) <= 0) {
            throw new Error("No se recibio un identificador de juego valido.");
        }
        if (!Number.isInteger(puntaje)) {
            throw new Error("El puntaje debe ser un numero entero.");
        }

        const datos = new URLSearchParams();
        datos.set("id_juego", idJuego);
        datos.set("puntos", String(puntaje));

        const respuesta = await fetch(endpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            credentials: "same-origin",
            body: datos
        });

        let resultado;
        try {
            resultado = await respuesta.json();
        } catch (error) {
            throw new Error("El servidor devolvio una respuesta no valida.");
        }

        if (!respuesta.ok || resultado.status !== "success") {
            throw new Error(resultado.mensaje || "No se pudo guardar el puntaje.");
        }

        return resultado;
    }

    global.StardustPuntajes = Object.freeze({ guardar });
})(window);
