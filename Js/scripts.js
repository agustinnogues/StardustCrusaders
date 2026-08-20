let ultimoScroll = 0;
let temporizador;
const header = document.getElementById("header");
const footer = document.getElementById("footer");
const botonTema = document.getElementById("modoOscuro");
// Ocultar con Scroll
window.addEventListener("scroll", () => {
    clearTimeout(temporizador);
    const actual = window.pageYOffset;
    if(actual > ultimoScroll){
        // Bajando
        header.classList.add("ocultarHeader");
        footer.classList.add("mostrarFooter");
    }else{
        // Subiendo
        header.classList.remove("ocultarHeader");
        footer.classList.remove("mostrarFooter");
    }
    ultimoScroll = actual;
    // Si deja de mover el scroll
    temporizador = setTimeout(() => {
        header.classList.remove("ocultarHeader");
        footer.classList.add("mostrarFooter");
    },300);
});
if(localStorage.getItem("tema")=="oscuro"){
    document.body.classList.add("dark");
    botonTema.textContent="☀️";
}
botonTema.addEventListener("click",()=>{
    document.body.classList.toggle("dark");
    if(document.body.classList.contains("dark")){
        localStorage.setItem("tema","oscuro");
        botonTema.textContent="☀️";
    }else{
        localStorage.setItem("tema","claro");
        botonTema.textContent="🌙";
    }
});
// MENÚ PERFIL
const fotoPerfil = document.getElementById("fotoPerfil");
const menuPerfil = document.getElementById("menuPerfil");
if(fotoPerfil && menuPerfil){
    fotoPerfil.addEventListener("click",(e)=>{
        e.stopPropagation();
        menuPerfil.classList.toggle("activo");
    });
    document.addEventListener("click",()=>{
        menuPerfil.classList.remove("activo");
    });
    menuPerfil.addEventListener("click",(e)=>{
        e.stopPropagation();
    });
}