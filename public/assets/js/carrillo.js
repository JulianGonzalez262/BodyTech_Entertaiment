document.addEventListener('DOMContentLoaded', () => {

    const cartPage = document.querySelector('.cart-page');

    if (!cartPage) return;

    const links = cartPage.querySelectorAll('a[href]');

    links.forEach(link => {

        link.addEventListener('click', function(event) {

            const href = this.getAttribute('href');

            if (
                !href ||
                href.startsWith('#') ||
                href.startsWith('javascript:')
            ) {
                return;
            }

            event.preventDefault();

            cartPage.classList.add('page-exit');

            setTimeout(() => {

                window.location.href = href;

            }, 450);

        });

    });

});
