<?php
// Este archivo solamente muestra el formulario.
// El procesamiento del ZIP lo hace instalar_juego.php
?>
<div class="tarjetaAdmin">
<h2>Agregar juego</h2>
<p>
    Desde aquí podés instalar un juego externo en la página.
    El juego debe entregarse en formato <strong>.ZIP</strong>.
</p>
<form action="instalar_juego.php" method="POST" enctype="multipart/form-data">
    <div class="campoAdmin">
        <label for="nombre">
            Nombre del juego
        </label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="100"
            required
            placeholder="Ej: Pokémon Battle"
        >
    </div>
    <div class="campoAdmin">
        <label for="carpeta">
            Nombre de la carpeta
        </label>
        <input
            type="text"
            id="carpeta"
            name="carpeta"
            maxlength="50"
            required
            placeholder="Ej: pokemon_battle"
        >
        <small>
            Usá solamente letras, números, guiones y guiones bajos.
        </small>
    </div>
    <div class="campoAdmin">
        <label for="descripcion">
            Descripción
        </label>
        <textarea
            id="descripcion"
            name="descripcion"
            maxlength="255"
            rows="4"
            placeholder="Descripción del juego..."
        ></textarea>
    </div>
    <div class="campoAdmin">
        <label for="puntos_maximos">
            Puntos máximos
        </label>
        <input
            type="number"
            id="puntos_maximos"
            name="puntos_maximos"
            min="0"
            required
            placeholder="Ej: 1000"
        >
    </div>
    <div class="campoAdmin">
        <label for="archivo_juego">
            Archivo del juego
        </label>
        <input
            type="file"
            id="archivo_juego"
            name="archivo_juego"
            accept=".zip"
            required
        >
        <small>
            El ZIP debe contener un <strong>index.html</strong>
            en la carpeta principal del juego.
        </small>
    </div>
    <div class="infoJuegoAdmin">
        <h3>¿Cómo debe ser el ZIP?</h3>
        <pre>juego.zip
│
├── index.html
├── game.js
├── style.css
├── images/
│   ├── fondo.png
│   └── personaje.png
└── sounds/
    └── musica.mp3</pre>
        <p>
            El juego debe funcionar mediante HTML, CSS y JavaScript.
            No se permiten archivos PHP dentro del juego.
        </p>
        <p>
            Para ajustar los puntos a la configuración del juego, consultá
            <code>await StardustPuntajes.obtenerPuntajeMaximo()</code> y usá ese
            valor como límite al calcular el resultado.
            Para guardar el resultado final, agregá antes de tu script:
            <code>&lt;script src="../../Js/puntajes.js"&gt;&lt;/script&gt;</code>.
            Cuando termine la partida, llamá
            <code>StardustPuntajes.guardar(puntosFinales)</code>.
            El servidor también rechazará puntajes por encima del máximo. El catálogo
            proporciona automáticamente el ID del juego; no lo fijes en el código.
        </p>
    </div>
    <button
        type="submit"
        class="btnAdmin btnAgregar"
    >
        Instalar juego
    </button>
</form>
</div>
