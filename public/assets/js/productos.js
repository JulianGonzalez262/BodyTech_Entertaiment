document.addEventListener('DOMContentLoaded', () => {

    'use strict';


    /* ==========================================================
       ELEMENTOS PRINCIPALES
    ========================================================== */

    const searchForm = document.querySelector(
        '.products-search-form'
    );

    const searchInput = document.querySelector(
        '.products-search-box input[name="buscar"]'
    );

    const modeLinks = document.querySelectorAll(
        '.products-mode'
    );

    const filterCheckboxes = document.querySelectorAll(
        '.filter-option input[type="checkbox"]'
    );

    const clearFiltersButton = document.querySelector(
        '.clear-filters-button'
    );

    const filterGroups = document.querySelectorAll(
        '.filter-group'
    );

    const sortSelect = document.querySelector(
        '#sortProducts'
    );

    const productsGrid = document.querySelector(
        '.products-grid'
    );


    /* ==========================================================
       URL ACTUAL
    ========================================================== */

    const getCurrentUrl = () => {

        return new URL(
            window.location.href
        );

    };


    /* ==========================================================
       MODO ACTUAL
    ========================================================== */

    const getCurrentMode = () => {

        const url = getCurrentUrl();

        return url.searchParams.get('modo') || 'productos';

    };


    /* ==========================================================
       FILTROS PERMITIDOS
       
       PRODUCTOS:
       - precio
       - categoria
       - marca
       - stock
       - estado

       GIMNASIOS:
       - ciudad

       IMPORTANTE:
       No inventamos filtros adicionales.
    ========================================================== */

    const PRODUCT_FILTERS = [
        'precio',
        'categoria',
        'marca',
        'stock',
        'estado'
    ];

    const GYM_FILTERS = [
        'ciudad'
    ];


    /* ==========================================================
       OBTENER FILTROS DEL MODO ACTUAL
    ========================================================== */

    const getAllowedFilters = () => {

        const mode = getCurrentMode();

        if (mode === 'gimnasios') {

            return GYM_FILTERS;

        }

        return PRODUCT_FILTERS;

    };


    /* ==========================================================
       CONSTRUIR URL CON FILTROS
       
       Esta función NO reemplaza PHP.
       
       Simplemente toma lo que el usuario seleccionó
       y lo envía mediante GET.
    ========================================================== */

    const buildFilterUrl = () => {

        const url = getCurrentUrl();

        const mode = getCurrentMode();


        /* ------------------------------------------------------
           MODO
        ------------------------------------------------------ */

        url.searchParams.set(
            'modo',
            mode
        );


        /* ------------------------------------------------------
           BUSCADOR GENERAL
        ------------------------------------------------------ */

        if (searchInput) {

            const searchValue =
                searchInput.value.trim();


            if (searchValue !== '') {

                url.searchParams.set(
                    'buscar',
                    searchValue
                );

            } else {

                url.searchParams.delete(
                    'buscar'
                );

            }

        }


        /* ------------------------------------------------------
           ELIMINAR TODOS LOS FILTROS CONOCIDOS
           
           Primero limpiamos los parámetros de filtros.
           Después agregamos únicamente los seleccionados.
        ------------------------------------------------------ */

        [
            ...PRODUCT_FILTERS,
            ...GYM_FILTERS
        ].forEach(name => {

            url.searchParams.delete(name);

        });


        /* ------------------------------------------------------
           CHECKBOXES SELECCIONADOS
        ------------------------------------------------------ */

        filterCheckboxes.forEach(checkbox => {

            if (!checkbox.checked) {

                return;

            }


            const cleanName =
                checkbox.name.replace(
                    '[]',
                    ''
                );


            /*
             * Si por alguna razón aparece un checkbox
             * que no pertenece al modo actual,
             * simplemente lo ignoramos.
             */

            if (
                !getAllowedFilters().includes(
                    cleanName
                )
            ) {

                return;

            }


            url.searchParams.append(
                cleanName,
                checkbox.value
            );

        });


        return url;

    };


    /* ==========================================================
       APLICAR FILTROS
       
       Productos:
       precio / categoria / marca / stock / estado

       Gimnasios:
       ciudad

       Todos funcionan mediante GET.
    ========================================================== */

    filterCheckboxes.forEach(checkbox => {

        checkbox.addEventListener(
            'change',
            () => {

                const url =
                    buildFilterUrl();


                window.location.href =
                    url.toString();

            }
        );

    });


    /* ==========================================================
       FORMULARIO DE BÚSQUEDA
    ========================================================== */

    if (searchForm) {

        searchForm.addEventListener(
            'submit',
            () => {

                if (searchInput) {

                    searchInput.value =
                        searchInput.value.trim();

                }

            }
        );

    }


    /* ==========================================================
       ENTER EN EL BUSCADOR
    ========================================================== */

    if (searchInput) {

        searchInput.addEventListener(
            'keydown',
            event => {

                if (
                    event.key === 'Enter'
                ) {

                    event.preventDefault();


                    if (searchForm) {

                        searchForm.requestSubmit();

                    }

                }

            }
        );

    }


    /* ==========================================================
   BUSCADOR DINÁMICO CON NODE.JS

   IMPORTANTE:
   - No reemplaza la búsqueda PHP.
   - ENTER continúa utilizando el formulario tradicional.
   - Node.js solamente proporciona sugerencias dinámicas.
   - Funciona para productos y gimnasios.
========================================================== */

if (
    searchInput &&
    (
        getCurrentMode() === 'productos' ||
        getCurrentMode() === 'gimnasios'
    )
) {

    /* ------------------------------------------------------
       MODO ACTUAL
    ------------------------------------------------------ */

    const currentMode =
        getCurrentMode();


    /* ------------------------------------------------------
       CONTENEDOR DE SUGERENCIAS
    ------------------------------------------------------ */

    const searchBox =
        searchInput.closest(
            '.products-search-box'
        );


    if (searchBox) {

        const suggestions =
            document.createElement('div');


        suggestions.className =
            'node-product-search-results';


        suggestions.setAttribute(
            'aria-live',
            'polite'
        );


        searchBox.appendChild(
            suggestions
        );


        let searchTimeout =
            null;


        let currentRequest = 0;


        /* --------------------------------------------------
           OCULTAR SUGERENCIAS
        -------------------------------------------------- */

        const hideSuggestions = () => {

            suggestions.innerHTML = '';

            suggestions.classList.remove(
                'is-visible'
            );

        };


        /* --------------------------------------------------
           MOSTRAR MENSAJE
        -------------------------------------------------- */

        const showMessage = message => {

            suggestions.innerHTML = `
                <div class="node-product-search-message">
                    ${message}
                </div>
            `;


            suggestions.classList.add(
                'is-visible'
            );

        };


        /* --------------------------------------------------
           MOSTRAR PRODUCTOS
        -------------------------------------------------- */

        const showProducts = productos => {

            if (!productos.length) {

                showMessage(
                    'No encontramos productos con esa búsqueda.'
                );

                return;

            }


            suggestions.innerHTML =
                productos
                    .slice(0, 8)
                    .map(producto => {

                        const precio =
                            Number(
                                producto.precio
                            );


                        const precioFormateado =
                            Number.isFinite(precio)
                                ? precio.toLocaleString(
                                    'es-CO',
                                    {
                                        style: 'currency',
                                        currency: 'COP',
                                        maximumFractionDigits: 0
                                    }
                                )
                                : '';


                        const imagen =
                            producto.imagen
                                ? `
                                    <div class="node-product-search-thumb">

                                        <img
                                            src="/bodytech/public/uploads/productos/${escapeHtml(producto.imagen)}"
                                            alt="${escapeHtml(producto.nombre)}"
                                        >

                                    </div>
                                `
                                : `
                                    <div class="node-product-search-thumb">

                                        <i class="fa-regular fa-image"></i>

                                    </div>
                                `;


                        return `
                            <a
                                href="/bodytech/public/producto.php?id=${encodeURIComponent(producto.id)}"
                                class="node-product-search-item"
                            >

                                <div class="node-product-search-main">

                                    ${imagen}

                                    <div class="node-product-search-info">

                                        <strong>
                                            ${escapeHtml(producto.nombre)}
                                        </strong>

                                        <span>
                                            ${escapeHtml(producto.categoria || 'Producto')}
                                        </span>

                                    </div>

                                </div>

                                <span class="node-product-search-price">
                                    ${precioFormateado}
                                </span>

                            </a>
                        `;

                    })
                    .join('');


            suggestions.classList.add(
                'is-visible'
            );

        };


        /* --------------------------------------------------
           MOSTRAR GIMNASIOS
        -------------------------------------------------- */

        const showGyms = gimnasios => {

            if (!gimnasios.length) {

                showMessage(
                    'No encontramos gimnasios con esa búsqueda.'
                );

                return;

            }


            suggestions.innerHTML =
                gimnasios
                    .slice(0, 8)
                    .map(gym => {

                        const imagen =
                            gym.imagen
                                ? `
                                    <div class="node-product-search-thumb">

                                        <img
                                            src="/bodytech/public/uploads/gimnasios/${escapeHtml(gym.imagen)}"
                                            alt="${escapeHtml(gym.nombre)}"
                                        >

                                    </div>
                                `
                                : `
                                    <div class="node-product-search-thumb">

                                        <i class="fa-solid fa-dumbbell"></i>

                                    </div>
                                `;


                        return `
                            <a
                                href="/bodytech/public/detalle_gimnasio.php?id=${encodeURIComponent(gym.id)}"
                                class="node-product-search-item"
                            >

                                <div class="node-product-search-main">

                                    ${imagen}

                                    <div class="node-product-search-info">

                                        <strong>
                                            ${escapeHtml(gym.nombre)}
                                        </strong>

                                        <span>
                                            ${escapeHtml(gym.ciudad || 'Gimnasio')}
                                        </span>

                                    </div>

                                </div>

                                <span class="node-product-search-price">
                                    Ver gimnasio
                                </span>

                            </a>
                        `;

                    })
                    .join('');


            suggestions.classList.add(
                'is-visible'
            );

        };


        /* --------------------------------------------------
           ESCAPAR HTML

           Evita insertar directamente texto proveniente
           de MySQL dentro del HTML.
        -------------------------------------------------- */

        const escapeHtml = value => {

            return String(value ?? '')
                .replace(
                    /&/g,
                    '&amp;'
                )
                .replace(
                    /</g,
                    '&lt;'
                )
                .replace(
                    />/g,
                    '&gt;'
                )
                .replace(
                    /"/g,
                    '&quot;'
                )
                .replace(
                    /'/g,
                    '&#039;'
                );

        };


        /* --------------------------------------------------
           CONSULTAR NODE.JS
        -------------------------------------------------- */

        const searchWithNode = async value => {

            const requestId =
                ++currentRequest;


            try {

                const endpoint =
                    currentMode === 'gimnasios'
                        ? 'http://localhost:3000/api/gimnasios/buscar'
                        : 'http://localhost:3000/api/productos/buscar';


                const response =
                    await fetch(
                        `${endpoint}?q=${encodeURIComponent(value)}`
                    );


                if (!response.ok) {

                    throw new Error(
                        'Error en la respuesta del servidor Node.js'
                    );

                }


                const resultados =
                    await response.json();


                /* ------------------------------------------------
                   Si ya existe una búsqueda más reciente,
                   ignoramos esta respuesta.
                ------------------------------------------------ */

                if (
                    requestId !== currentRequest
                ) {

                    return;

                }


                if (
                    currentMode === 'gimnasios'
                ) {

                    showGyms(
                        resultados
                    );

                } else {

                    showProducts(
                        resultados
                    );

                }

            } catch (error) {

                console.error(
                    'Error en búsqueda dinámica:',
                    error
                );


                hideSuggestions();

            }

        };


        /* --------------------------------------------------
           DETECTAR ESCRITURA
        -------------------------------------------------- */

        searchInput.addEventListener(
            'input',
            () => {

                const value =
                    searchInput.value.trim();


                clearTimeout(
                    searchTimeout
                );


                /* ------------------------------------------------
                   Menos de 2 caracteres:
                   no consultamos Node.js.
                ------------------------------------------------ */

                if (
                    value.length < 2
                ) {

                    hideSuggestions();

                    return;

                }


                showMessage(
                    currentMode === 'gimnasios'
                        ? 'Buscando gimnasios...'
                        : 'Buscando productos...'
                );


                /* ------------------------------------------------
                   Pequeño debounce.
                ------------------------------------------------ */

                searchTimeout =
                    setTimeout(
                        () => {

                            searchWithNode(
                                value
                            );

                        },
                        300
                    );

            }
        );


        /* --------------------------------------------------
           CERRAR AL HACER CLICK FUERA
        -------------------------------------------------- */

        document.addEventListener(
            'click',
            event => {

                if (
                    !searchBox.contains(
                        event.target
                    )
                ) {

                    hideSuggestions();

                }

            }
        );


        /* --------------------------------------------------
           ESC PARA CERRAR
        -------------------------------------------------- */

        searchInput.addEventListener(
            'keydown',
            event => {

                if (
                    event.key === 'Escape'
                ) {

                    hideSuggestions();

                }

            }
        );


        /* --------------------------------------------------
           AL ENVIAR FORMULARIO

           Ocultamos las sugerencias porque la búsqueda
           tradicional PHP tomará el control.
        -------------------------------------------------- */

        if (searchForm) {

            searchForm.addEventListener(
                'submit',
                () => {

                    hideSuggestions();

                }
            );

        }

    }

}
    /* ==========================================================
       LIMPIAR FILTROS
       
       PRODUCTOS:
       elimina precio, categoria, marca, stock y estado.

       GIMNASIOS:
       elimina ciudad.

       CONSERVA:
       - modo
       - búsqueda
    ========================================================== */

    if (clearFiltersButton) {

        clearFiltersButton.addEventListener(
            'click',
            () => {

                const url =
                    getCurrentUrl();


                [
                    ...PRODUCT_FILTERS,
                    ...GYM_FILTERS
                ].forEach(name => {

                    url.searchParams.delete(
                        name
                    );

                });


                window.location.href =
                    url.toString();

            }
        );

    }


    /* ==========================================================
       GRUPOS DE FILTROS
       
       Permite abrir/cerrar:
       
       PRODUCTOS:
       - Precio
       - Categoría
       - Marca
       - Disponibilidad
       - Estado

       GIMNASIOS:
       - Ciudad
       
       No importa cuántas ciudades existan.
       El JS detecta automáticamente el .filter-group.
    ========================================================== */

    filterGroups.forEach(group => {

        const title =
            group.querySelector('h3');


        if (!title) {

            return;

        }


        title.setAttribute(
            'role',
            'button'
        );


        title.setAttribute(
            'tabindex',
            '0'
        );


        const options =
            group.querySelectorAll(
                '.filter-option'
            );


        let collapsed = false;


        const toggleGroup = () => {

            collapsed =
                !collapsed;


            options.forEach(option => {

                option.style.display =
                    collapsed
                        ? 'none'
                        : '';

            });


            const icon =
                title.querySelector('i');


            if (icon) {

                icon.style.transform =
                    collapsed
                        ? 'rotate(-90deg)'
                        : 'rotate(0deg)';

            }

        };


        title.addEventListener(
            'click',
            toggleGroup
        );


        title.addEventListener(
            'keydown',
            event => {

                if (
                    event.key === 'Enter' ||
                    event.key === ' '
                ) {

                    event.preventDefault();

                    toggleGroup();

                }

            }
        );

    });


    /* ==========================================================
       ORDENAMIENTO DE PRODUCTOS
       
       Solo existe en modo Productos.
       
       No afecta Gimnasios.
    ========================================================== */

    if (
        sortSelect &&
        productsGrid &&
        getCurrentMode() === 'productos'
    ) {

        sortSelect.addEventListener(
            'change',
            () => {

                const cards =
                    Array.from(
                        productsGrid.querySelectorAll(
                            '.product-card'
                        )
                    );


                if (!cards.length) {

                    return;

                }


                const selected =
                    sortSelect.value;


                /* ------------------------------------------------
                   MÁS RECIENTES
                ------------------------------------------------ */

                if (
                    selected === 'recent'
                ) {

                    cards.sort(
                        (a, b) => {

                            return (
                                Number(
                                    a.dataset.originalIndex
                                ) -
                                Number(
                                    b.dataset.originalIndex
                                )
                            );

                        }
                    );

                }


                /* ------------------------------------------------
                   PRECIO MENOR -> MAYOR
                ------------------------------------------------ */

                if (
                    selected === 'price-low'
                ) {

                    cards.sort(
                        (a, b) => {

                            return (
                                getCardPrice(a) -
                                getCardPrice(b)
                            );

                        }
                    );

                }


                /* ------------------------------------------------
                   PRECIO MAYOR -> MENOR
                ------------------------------------------------ */

                if (
                    selected === 'price-high'
                ) {

                    cards.sort(
                        (a, b) => {

                            return (
                                getCardPrice(b) -
                                getCardPrice(a)
                            );

                        }
                    );

                }


                /* ------------------------------------------------
                   MEJOR VALORADOS
                ------------------------------------------------ */

                if (
                    selected === 'rating'
                ) {

                    cards.sort(
                        (a, b) => {

                            return (
                                getCardRating(b) -
                                getCardRating(a)
                            );

                        }
                    );

                }


                cards.forEach(card => {

                    productsGrid.appendChild(
                        card
                    );

                });

            }
        );


        /* ------------------------------------------------------
           GUARDAR ORDEN ORIGINAL
        ------------------------------------------------------ */

        const cards =
            productsGrid.querySelectorAll(
                '.product-card'
            );


        cards.forEach(
            (card, index) => {

                card.dataset.originalIndex =
                    index;

            }
        );

    }


    /* ==========================================================
       PRECIO DE TARJETA
    ========================================================== */

    function getCardPrice(card) {

        const priceElement =
            card.querySelector(
                '.product-card-price'
            );


        if (!priceElement) {

            return 0;

        }


        const text =
            priceElement.textContent
                .replace(/\$/g, '')
                .replace(/\./g, '')
                .replace(/,/g, '.')
                .trim();


        const price =
            parseFloat(text);


        return Number.isFinite(price)
            ? price
            : 0;

    }


    /* ==========================================================
       RATING DE TARJETA
    ========================================================== */

    function getCardRating(card) {

        const ratingElement =
            card.querySelector(
                '.product-rating span'
            );


        if (!ratingElement) {

            return 0;

        }


        const text =
            ratingElement.textContent
                .trim();


        const match =
            text.match(
                /(\d+(?:[.,]\d+)?)/
            );


        if (!match) {

            return 0;

        }


        const rating =
            parseFloat(
                match[1].replace(',', '.')
            );


        return Number.isFinite(rating)
            ? rating
            : 0;

    }


    /* ==========================================================
       ESTADO VISUAL DE LOS MODOS
       
       El <a> hace realmente la navegación.
       JS solamente refuerza la clase active.
    ========================================================== */

    modeLinks.forEach(link => {

        link.addEventListener(
            'click',
            () => {

                modeLinks.forEach(
                    item => {

                        item.classList.remove(
                            'active'
                        );

                    }
                );


                link.classList.add(
                    'active'
                );

            }
        );

    });


    /* ==========================================================
       EVITAR DOBLE NAVEGACIÓN DE FILTROS
    ========================================================== */

    let filterNavigationStarted =
        false;


    filterCheckboxes.forEach(
        checkbox => {

            checkbox.addEventListener(
                'change',
                () => {

                    if (
                        filterNavigationStarted
                    ) {

                        return;

                    }


                    filterNavigationStarted =
                        true;

                }
            );

        }
    );


    /* ==========================================================
       ACCESIBILIDAD DE CHECKBOXES
    ========================================================== */

    filterCheckboxes.forEach(
        checkbox => {

            const label =
                checkbox.closest(
                    '.filter-option'
                );


            if (!label) {

                return;

            }


            label.addEventListener(
                'keydown',
                event => {

                    if (
                        event.key === 'Enter'
                    ) {

                        event.preventDefault();


                        checkbox.checked =
                            !checkbox.checked;


                        checkbox.dispatchEvent(
                            new Event(
                                'change',
                                {
                                    bubbles: true
                                }
                            )
                        );

                    }

                }
            );

        }
    );


});