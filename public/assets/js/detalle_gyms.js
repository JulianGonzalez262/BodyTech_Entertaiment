document.addEventListener('DOMContentLoaded', function () {

    const thumbnails = document.querySelectorAll('.gym-thumbnail');
    const mediaItems = document.querySelectorAll('.gym-media-item');

    if (!thumbnails.length || !mediaItems.length) {
        return;
    }

    thumbnails.forEach((thumbnail, index) => {

        thumbnail.addEventListener('click', function (e) {

            e.preventDefault();

            /* Quitar activo de todos */
            thumbnails.forEach(item => {
                item.classList.remove('active');
            });

            mediaItems.forEach(item => {
                item.classList.remove('active');
            });

            /* Activar miniatura */
            thumbnail.classList.add('active');

            /* Mostrar imagen/video correspondiente */
            if (mediaItems[index]) {
                mediaItems[index].classList.add('active');
            }

        });

    });

});


document.addEventListener('DOMContentLoaded', function () {

    const mediaItems = Array.from(
        document.querySelectorAll('.gym-media-item')
    );

    const thumbnails = Array.from(
        document.querySelectorAll('.gym-thumbnail')
    );

    const lightbox = document.getElementById('gymLightbox');
    const lightboxImage = document.getElementById('gymLightboxImage');

    const closeButton = document.getElementById('gymLightboxClose');
    const prevButton = document.getElementById('gymLightboxPrev');
    const nextButton = document.getElementById('gymLightboxNext');

    let currentIndex = 0;


    /* ======================================================
       CAMBIAR MEDIA PRINCIPAL
    ====================================================== */

    function showMedia(index) {

        if (!mediaItems.length) return;

        if (index < 0) {
            index = mediaItems.length - 1;
        }

        if (index >= mediaItems.length) {
            index = 0;
        }

        currentIndex = index;


        mediaItems.forEach(function (item, i) {

            item.classList.toggle(
                'active',
                i === currentIndex
            );

        });


        thumbnails.forEach(function (thumb, i) {

            thumb.classList.toggle(
                'active',
                i === currentIndex
            );

        });

    }


    /* ======================================================
       MINIATURAS
    ====================================================== */

    thumbnails.forEach(function (thumbnail, index) {

        thumbnail.addEventListener('click', function (event) {

            event.preventDefault();
            event.stopPropagation();

            showMedia(index);

        });

    });


    /* ======================================================
       ABRIR LIGHTBOX
    ====================================================== */

    mediaItems.forEach(function (item, index) {

        const image = item.querySelector('img');

        if (!image) return;

        image.addEventListener('click', function () {

            currentIndex = index;

            lightboxImage.src = image.src;
            lightboxImage.alt = image.alt || 'Imagen del gimnasio';

            lightbox.classList.add('open');

            document.body.style.overflow = 'hidden';

        });

    });


    /* ======================================================
       ACTUALIZAR LIGHTBOX
    ====================================================== */

    function updateLightbox() {

        const item = mediaItems[currentIndex];

        if (!item) return;

        const image = item.querySelector('img');

        if (!image) return;

        lightboxImage.src = image.src;
        lightboxImage.alt = image.alt || 'Imagen del gimnasio';

    }


    /* ======================================================
       SIGUIENTE
    ====================================================== */

    nextButton.addEventListener('click', function (event) {

        event.stopPropagation();

        currentIndex++;

        if (currentIndex >= mediaItems.length) {
            currentIndex = 0;
        }

        updateLightbox();

        showMedia(currentIndex);

    });


    /* ======================================================
       ANTERIOR
    ====================================================== */

    prevButton.addEventListener('click', function (event) {

        event.stopPropagation();

        currentIndex--;

        if (currentIndex < 0) {
            currentIndex = mediaItems.length - 1;
        }

        updateLightbox();

        showMedia(currentIndex);

    });


    /* ======================================================
       CERRAR
    ====================================================== */

    function closeLightbox() {

        lightbox.classList.remove('open');

        document.body.style.overflow = '';

    }


    closeButton.addEventListener(
        'click',
        closeLightbox
    );


    /* ======================================================
       CLIC FUERA DE LA IMAGEN
    ====================================================== */

    lightbox.addEventListener('click', function (event) {

        if (event.target === lightbox) {
            closeLightbox();
        }

    });


    /* ======================================================
       TECLADO
    ====================================================== */

    document.addEventListener('keydown', function (event) {

        if (!lightbox.classList.contains('open')) {
            return;
        }

        if (event.key === 'Escape') {
            closeLightbox();
        }

        if (event.key === 'ArrowRight') {
            nextButton.click();
        }

        if (event.key === 'ArrowLeft') {
            prevButton.click();
        }

    });


    /* ======================================================
       INICIAL
    ====================================================== */

    showMedia(0);

});

