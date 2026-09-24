let ultimoScroll = 0;
let temporizador;
// HEADER Y FOOTER
const header = document.getElementById("header");
const footer = document.getElementById("footer");
// Ocultar header al bajar y mostrar al subir
if (header) {
    window.addEventListener("scroll", () => {
        clearTimeout(temporizador);
        const actual = window.pageYOffset;
        if (actual > ultimoScroll) {
            header.classList.add("ocultarHeader");
            if (footer) {
                footer.classList.add("mostrarFooter");
            }
        } else {
            header.classList.remove("ocultarHeader");
            if (footer) {
                footer.classList.remove("mostrarFooter");
            }
        }
        ultimoScroll = actual;
        temporizador = setTimeout(() => {
            header.classList.remove("ocultarHeader");
            if (footer) {
                footer.classList.add("mostrarFooter");
            }
        }, 300);
    });
}
// MODO OSCURO
const botonTema = document.getElementById("modoOscuro");
if (botonTema) {
    if (localStorage.getItem("tema") === "oscuro") {
        document.body.classList.add("dark");
        botonTema.textContent = "☀️";
    }
    botonTema.addEventListener("click", () => {
        document.body.classList.toggle("dark");
        if (document.body.classList.contains("dark")) {
            localStorage.setItem("tema", "oscuro");
            botonTema.textContent = "☀️";
        } else {
            localStorage.setItem("tema", "claro");
            botonTema.textContent = "🌙";
        }
    });
}
// MENÚ PERFIL
const fotoPerfil = document.getElementById("fotoPerfil");
const menuPerfil = document.getElementById("menuPerfil");
if (fotoPerfil && menuPerfil) {
    fotoPerfil.addEventListener("click", (e) => {
        e.stopPropagation();
        menuPerfil.classList.toggle("activo");
    });
    document.addEventListener("click", () => {
        menuPerfil.classList.remove("activo");
    });
    menuPerfil.addEventListener("click", (e) => {
        e.stopPropagation();
    });
}