// Valida el puntaje y lo envía al servidor junto con el ID del juego.
(function (global) {
    const script = document.currentScript;
    if (!script || !script.src) {
        throw new Error("No se pudo determinar la ruta de la biblioteca de puntajes.");
    }

    const endpoint = new URL("../juegos/guardarPuntaje.php", script.src).href;
    let solicitudPuntajeMaximo;

    function obtenerIdJuego() {
        const idJuego = new URLSearchParams(global.location.search).get("id_juego");
        if (!idJuego || !Number.isInteger(Number(idJuego)) || Number(idJuego) <= 0) {
            throw new Error("No se recibio un identificador de juego valido.");
        }
        return idJuego;
    }

    async function obtenerPuntajeMaximo() {
        if (!solicitudPuntajeMaximo) {
            const url = new URL(endpoint);
            url.searchParams.set("id_juego", obtenerIdJuego());
            solicitudPuntajeMaximo = fetch(url, {
                credentials: "same-origin",
                cache: "no-store"
            }).then(async respuesta => {
                let resultado;
                try {
                    resultado = await respuesta.json();
                } catch (error) {
                    throw new Error("El servidor devolvio una respuesta no valida.");
                }
                if (!respuesta.ok || resultado.status !== "success") {
                    throw new Error(resultado.mensaje || "No se pudo consultar el puntaje maximo.");
                }
                const puntajeMaximo = Number(resultado.puntaje_maximo);
                if (!Number.isInteger(puntajeMaximo) || puntajeMaximo < 0) {
                    throw new Error("El servidor devolvio un puntaje maximo no valido.");
                }
                return puntajeMaximo;
            });
        }
        return solicitudPuntajeMaximo;
    }

    async function guardar(puntos) {
        const idJuego = obtenerIdJuego();
        const puntaje = Number(puntos);

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

    global.StardustPuntajes = Object.freeze({ guardar, obtenerPuntajeMaximo });
})(window);
