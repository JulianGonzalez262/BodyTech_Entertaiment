const mediaInput = document.getElementById('media');
const mediaFileName = document.getElementById('media-file-name');

if (mediaInput && mediaFileName) {

    mediaInput.addEventListener('change', () => {

        const files = mediaInput.files;

        if (!files.length) {
            mediaFileName.textContent = 'Ningún archivo seleccionado';
            return;
        }

        if (files.length === 1) {
            mediaFileName.textContent = files[0].name;
            return;
        }

        mediaFileName.textContent =
            `${files.length} archivos seleccionados`;
    });
}
