document.addEventListener('DOMContentLoaded', function () {

    const thumbnails = document.querySelectorAll('.product-thumbnail');
    const mediaItems = document.querySelectorAll('.product-media-item');

    thumbnails.forEach(function (thumbnail) {

        thumbnail.addEventListener('click', function () {

            const targetId = this.dataset.mediaTarget;

            /* ---------------------------------------------
               Quitar estado activo de miniaturas
            --------------------------------------------- */

            thumbnails.forEach(function (item) {

                item.classList.remove('active');

            });


            /* ---------------------------------------------
               Quitar estado activo de media
            --------------------------------------------- */

            mediaItems.forEach(function (item) {

                item.classList.remove('active');

                /* Pausar videos cuando cambiamos de media */

                const video = item.querySelector('video');

                if (video) {

                    video.pause();

                }

            });


            /* ---------------------------------------------
               Activar miniatura
            --------------------------------------------- */

            this.classList.add('active');


            /* ---------------------------------------------
               Activar media seleccionada
            --------------------------------------------- */

            const target = document.getElementById(targetId);

            if (target) {

                target.classList.add('active');

            }

        });

    });

});
/* ==========================================================
   ZOOM DE IMAGEN — ESTILO MARKETPLACE
========================================================== */

document.querySelectorAll('.product-image-zoom').forEach((zoomContainer) => {

    const image = zoomContainer.querySelector('img');

    if (!image) return;

    zoomContainer.addEventListener('mouseenter', () => {

        zoomContainer.classList.add('zoom-active');

    });

    zoomContainer.addEventListener('mousemove', (event) => {

        const rect = zoomContainer.getBoundingClientRect();

        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;

        const percentX = (x / rect.width) * 100;
        const percentY = (y / rect.height) * 100;

        image.style.transformOrigin =
            `${percentX}% ${percentY}%`;

    });

    zoomContainer.addEventListener('mouseleave', () => {

        zoomContainer.classList.remove('zoom-active');

        image.style.transformOrigin = 'center center';

    });

});


const selectorCantidad = document.getElementById('cantidad_producto');
const cantidadCarrito = document.getElementById('cantidad_carrito');
const cantidadCompra = document.getElementById('cantidad_compra');

if (selectorCantidad) {

    function actualizarCantidad() {

        const cantidad = selectorCantidad.value;

        cantidadCarrito.value = cantidad;
        cantidadCompra.value = cantidad;

    }

    selectorCantidad.addEventListener(
        'change',
        actualizarCantidad
    );

    actualizarCantidad();
}