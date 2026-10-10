(function () {

    const usuarioId = window.BODYTECH_USER_ID;

    if (!usuarioId) {
        return;
    }

    let lastId = window.BODYTECH_LAST_NOTIF_ID || 0;

    /* ==========================================
       Actualizar contador de la campana
    =========================================== */
    function actualizarBadge(count) {

        const campana = document.querySelector('.notifications-link');

        if (!campana) return;

        let badge = campana.querySelector('.notification-badge');

        if (count > 0) {

            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'notification-badge';
                campana.appendChild(badge);
            }

            badge.textContent = count;

        } else if (badge) {

            badge.remove();

        }

    }

    /* ==========================================
       Insertar una tarjeta nueva en la lista
       (solo aplica si estamos en Mis Notificaciones)
    =========================================== */
    function agregarTarjeta(n) {

        const lista = document.querySelector('.notifications-list');

        if (!lista) return;

        const vacio = lista.querySelector('.notifications-empty');
        if (vacio) vacio.remove();

        const tarjeta = document.createElement('a');

        tarjeta.href = n.link || '#';
        tarjeta.className = 'notification-card-link notification-card-new';

        tarjeta.innerHTML = `
            <article class="notification-card">
                <div class="notification-card-icon">
                    ${
                        n.imagen
                            ? `<img src="${n.imagen}" alt="${n.titulo}" class="notification-card-image">`
                            : `<i class="fa-solid fa-bell"></i>`
                    }
                </div>
                <div class="notification-content">
                    <div class="notification-top">
                        <h2>${n.titulo}</h2>
                        <span class="notification-status notification-status-new">
                            <i class="fa-solid fa-circle"></i>
                            Nueva
                        </span>
                    </div>
                    <p class="notification-message">${n.mensaje}</p>
                    <div class="notification-date">
                        <i class="fa-regular fa-clock"></i>
                        <span>${n.created_at}</span>
                    </div>
                </div>
            </article>
        `;

        lista.prepend(tarjeta);

    }

    /* ==========================================
       Preguntar al servidor si hay novedades
    =========================================== */
    function verificar() {

        fetch('/bodytech/public/notificaciones/check_notificaciones.php?last_id=' + lastId)
            .then(function (res) { return res.json(); })
            .then(function (data) {

                actualizarBadge(data.count);

                if (data.nuevas && data.nuevas.length > 0) {

                    data.nuevas.forEach(function (n) {

                        agregarTarjeta(n);

                        if (n.id > lastId) {
                            lastId = n.id;
                        }

                    });

                }

            })
            .catch(function () {
                // Silencioso: si falla una vez, se reintenta en el próximo ciclo
            });

    }

    // Preguntar cada 2 segundos
    setInterval(verificar, 2000);

})();
