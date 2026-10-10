var containerProductos = document.getElementById("lista-productos");

if (containerProductos) {

    var lastUpdateProductos = parseInt(containerProductos.getAttribute("data-last-update")) || 0;

    setInterval(function () {

        fetch("/bodytech/public/ajax/get_productos_actualizados.php?last=" + lastUpdateProductos)
        .then(function(res){ return res.json(); })
        .then(function(data){

            if (!data || data.length === 0) return;

            data.forEach(function(prod){

                var existing = document.getElementById("prod-" + prod.id);

                if (prod.activo == 0) {
                    if (existing) existing.remove();
                    return;
                }

                if (existing) existing.remove();

                var article = document.createElement("article");
                article.id = "prod-" + prod.id;
                article.style.border = "1px solid #ccc";
                article.style.padding = "15px";
                article.style.marginBottom = "20px";

                var html = "";
                html += "<h3>" + prod.nombre + "</h3>";
                html += "<p>" + prod.descripcion.replace(/\\n/g, "<br>") + "</p>";
                html += "<p><strong>Precio:</strong> $" + prod.precio + "</p>";
                html += "<p><strong>Stock:</strong> " + prod.stock + "</p>";
                html += "<p><strong>Categoría:</strong> " + prod.categoria + "</p>";

                if (prod.media && prod.media.length > 0) {
                    html += "<div style='margin-top:15px; display:flex; gap:15px; flex-wrap:wrap;'>";

                    prod.media.forEach(function(m){

                        if (m.media_type === "image") {
                            html += "<img src='/bodytech/public/uploads/productos/" + m.media_path + "' style='max-width:200px; border-radius:10px;'>";
                        }

                    
                        if (m.media_type === "video") {
                            html += "<video controls style='max-width:200px; border-radius:10px;'>" +
                                    "<source src='/bodytech/public/uploads/productos/" + m.media_path + "' type='video/mp4'>" +
                                    "</video>";
                        }

                    });

                    html += "</div>";
                }

                article.innerHTML = html;

                containerProductos.insertBefore(article, containerProductos.firstChild);

                lastUpdateProductos = prod.id;

            });

        });

    }, 5000);
}


var container = document.getElementById("lista-gimnasios");

if (container) {

    var lastUpdate = parseInt(container.getAttribute("data-last-update")) || 0;

    setInterval(function () {

        fetch("/bodytech/public/ajax/get_gimnasios_actualizados.php?last=" + lastUpdate)
        .then(function(res){ return res.json(); })
        .then(function(data){

            if (!data || data.length === 0) return;

            data.forEach(function(gym){

                var existing = document.getElementById("gym-" + gym.id);

                if (gym.activo == 0) {
                    if (existing) existing.remove();
                    return;
                }

                if (existing) existing.remove();

                var article = document.createElement("article");
                article.id = "gym-" + gym.id;
                article.style.border = "1px solid #ccc";
                article.style.padding = "15px";
                article.style.marginBottom = "20px";

                var html = "";
                html += "<h3>" + gym.nombre + "</h3>";
                html += "<p>" + gym.descripcion.replace(/\\n/g, "<br>") + "</p>";
                html += "<p><strong>Ciudad:</strong> " + gym.ciudad + "</p>";
                html += "<p><strong>Dirección:</strong> " + gym.direccion + "</p>";

                if (gym.media && gym.media.length > 0) {
                    html += "<div style='margin-top:15px; display:flex; gap:15px; flex-wrap:wrap;'>";

                    gym.media.forEach(function(m){

                        if (m.media_type === "image") {
                            html += "<img src='/bodytech/public/uploads/gimnasios/" + m.media_path + "' style='max-width:200px; border-radius:10px;'>";
                        }

                        if (m.media_type === "video") {
                            html += "<video controls style='max-width:200px; border-radius:10px;'>" +
                                    "<source src='/bodytech/public/uploads/gimnasios/" + m.media_path + "' type='video/mp4'>" +
                                    "</video>";
                        }

                    });

                    html += "</div>";
                }

                html += "<a href='/bodytech/public/register_afiliado.php?gym=" + gym.id + "'>Afiliarme a esta sede</a>";

                article.innerHTML = html;

                container.insertBefore(article, container.firstChild);

                lastUpdate = gym.id;

            });

        })
        .catch(function(err){
            console.error("Realtime error:", err);
        });

    }, 5000);
}


document.querySelectorAll('.gym-media-container').forEach(function(container){

    const slides = container.querySelectorAll('.gym-slide');
    const prevButton = container.querySelector('.gym-carousel-prev');
    const nextButton = container.querySelector('.gym-carousel-next');

    if(slides.length <= 1){
        return;
    }

    let current = 0;

    function showSlide(index){

        slides[current].classList.remove('active');

        current = index;

        if(current < 0){
            current = slides.length - 1;
        }

        if(current >= slides.length){
            current = 0;
        }

        slides[current].classList.add('active');
    }

    if(prevButton){

        prevButton.addEventListener('click', function(){

            showSlide(current - 1);

        });

    }

    if(nextButton){

        nextButton.addEventListener('click', function(){

            showSlide(current + 1);

        });

    }

    setInterval(function(){

        showSlide(current + 1);

    }, 5000);

});

document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================================
       1. EFECTO SCROLL DEL HERO
    ========================================================== */

    window.addEventListener('scroll', () => {

        const hero = document.querySelector('.hero-section');

        if (hero) {

            if (window.scrollY > 80) {
                hero.classList.add('hero-scrolling');
            } else {
                hero.classList.remove('hero-scrolling');
            }

        }

    });


    /* ==========================================================
       2. CARRUSEL GENERAL DE GIMNASIOS
    ========================================================== */

    const carousel =
        document.querySelector('.gyms-carousel-wrapper');

    const container =
        document.getElementById('lista-gimnasios');


    if (carousel && container) {

        const prevButton =
            carousel.querySelector('.gyms-carousel-prev');

        const nextButton =
            carousel.querySelector('.gyms-carousel-next');


        if (prevButton && nextButton) {


            function getScrollAmount() {

                const card =
                    container.querySelector('.gym-card');


                if (!card) {
                    return 300;
                }


                const styles =
                    window.getComputedStyle(container);


                const gap =
                    parseInt(styles.gap) || 0;


                return card.offsetWidth + gap;

            }


            nextButton.addEventListener('click', () => {

                container.scrollBy({

                    left: getScrollAmount(),

                    behavior: 'smooth'

                });

            });


            prevButton.addEventListener('click', () => {

                container.scrollBy({

                    left: -getScrollAmount(),

                    behavior: 'smooth'

                });

            });

        }

    }

});