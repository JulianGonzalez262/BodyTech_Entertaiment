const imageInput = document.getElementById('imagen');
const fileName = document.getElementById('file-name');

if (imageInput && fileName) {

    imageInput.addEventListener('change', function () {

        if (this.files.length > 0) {

            fileName.textContent = this.files[0].name;

        } else {

            fileName.textContent =
                'JPG, JPEG, PNG, WEBP o GIF';

        }

    });

}
