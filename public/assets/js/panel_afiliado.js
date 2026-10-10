/* ==========================================================
   BODYTECH
   CARRUSEL — PANEL DE AFILIADO
========================================================== */

document.addEventListener('DOMContentLoaded', () => {

    const imagenes = document.querySelectorAll('.afiliado-gym-image');

    // Si no hay imágenes o solo existe una, no necesitamos carrusel
    if (imagenes.length <= 1) {
        return;
    }

    let indiceActual = 0;

    setInterval(() => {

        // Ocultar imagen actual
        imagenes[indiceActual].classList.remove('active');

        // Pasar a la siguiente
        indiceActual++;

        // Volver a la primera al llegar al final
        if (indiceActual >= imagenes.length) {
            indiceActual = 0;
        }

        // Mostrar nueva imagen
        imagenes[indiceActual].classList.add('active');

    }, 3000);

});