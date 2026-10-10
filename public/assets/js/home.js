window.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.product-card').forEach(card => {

        const slides = card.querySelectorAll('.product-slide');

        if (slides.length < 2) return;

        let current = 0;
        let intervalId = null;

        card.addEventListener('mouseenter', () => {

            if (intervalId) return;

            const firstVideo = slides[current].querySelector('video');

            if (firstVideo) {
                firstVideo.play().catch(() => {});
            }

            intervalId = setInterval(() => {

                const oldVideo = slides[current].querySelector('video');

                if (oldVideo) {

                    oldVideo.pause();
                    oldVideo.currentTime = 0;

                }

                slides[current].classList.remove('active');

                current++;

                if (current >= slides.length) {
                    current = 0;
                }

                slides[current].classList.add('active');

                const newVideo = slides[current].querySelector('video');

                if (newVideo) {

                    newVideo.play().catch(() => {});

                }

            }, 1000);

        });

        card.addEventListener('mouseleave', () => {

            clearInterval(intervalId);

            intervalId = null;

            const currentVideo = slides[current].querySelector('video');

            if (currentVideo) {

                currentVideo.pause();
                currentVideo.currentTime = 0;

            }

            slides[current].classList.remove('active');

            current = 0;

            slides[0].classList.add('active');

            const firstVideo = slides[0].querySelector('video');

            if (firstVideo) {

                firstVideo.pause();
                firstVideo.currentTime = 0;

            }

        });

    });

});


/* ==========================================
   REVEAL TÍTULOS DE SECCIONES
========================================== */

const revealObserver = new IntersectionObserver(entries => {

    entries.forEach(entry => {

        if(entry.isIntersecting){

            entry.target
                .querySelectorAll('.reveal-item')
                .forEach(item => {

                    item.classList.add('visible');

                });

        }

    });

}, {
    threshold: 0.3
});


document
    .querySelectorAll('.reveal-section')
    .forEach(section => {

        revealObserver.observe(section);

    });

/* ==========================================
   REVEAL PRODUCTOS AL HACER SCROLL
========================================== */

window.addEventListener('DOMContentLoaded', () => {

    const carousel =
        document.querySelector('.products-carousel');

    if(!carousel) return;

    const cards =
        carousel.querySelectorAll('.product-card');

    cards.forEach(card => {

        card.classList.add(
            'product-hidden',
            'product-animate'
        );

    });

    const observer = new IntersectionObserver(entries => {

        entries.forEach(entry => {

            if(entry.isIntersecting){

                cards.forEach((card,index) => {

                    setTimeout(() => {

                        card.classList.remove('product-hidden');

                        card.classList.add('product-visible');

                    }, index * 140);

                });

                observer.disconnect();

            }

        });

    },{

        threshold:0.35

    });

    observer.observe(carousel);

});