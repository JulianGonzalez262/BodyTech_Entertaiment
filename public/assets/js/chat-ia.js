(function () {

    const wrap = document.getElementById('chatIaWrap');
    const ventana = document.getElementById('chatIaWindow');
    const burbuja = document.getElementById('chatIaBubble');
    const cerrar = document.getElementById('chatIaClose');
    const form = document.getElementById('chatIaForm');
    const input = document.getElementById('chatIaInput');
    const mensajes = document.getElementById('chatIaMessages');

    if (!ventana || !burbuja) return;

    let animando = false;

    /* ==========================================
       Convertir **texto** en <strong>texto</strong>
       y respetar saltos de línea
    =========================================== */
    function formatearTexto(texto) {

        const escapado = texto
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        const conNegritas = escapado.replace(
            /\*\*(.+?)\*\*/g,
            '<strong>$1</strong>'
        );

        return conNegritas.replace(/\n/g, '<br>');

    }

    /* ==========================================
       Abrir / cerrar con animación 3D
    =========================================== */
    function abrirChat() {

        wrap.classList.add('open');

        ventana.classList.add('open');
        ventana.classList.remove('anim-out');
        ventana.classList.add('anim-in');

        burbuja.classList.add('flip');
        burbuja.classList.add('spin-paused');
        setTimeout(function () { burbuja.classList.remove('flip'); }, 600);

        input.focus();

    }

    function cerrarChat() {

        if (animando) return;
        animando = true;

        ventana.classList.remove('anim-in');
        ventana.classList.add('anim-out');

        burbuja.classList.add('flip');
        setTimeout(function () { burbuja.classList.remove('flip'); }, 600);

        setTimeout(function () {

            ventana.classList.remove('open');
            ventana.classList.remove('anim-out');
            wrap.classList.remove('open');
            burbuja.classList.remove('spin-paused');
            animando = false;

        }, 350);

    }

    burbuja.addEventListener('click', function () {

        if (ventana.classList.contains('open')) {
            cerrarChat();
        } else {
            abrirChat();
        }

    });

    cerrar.addEventListener('click', cerrarChat);

    /* ==========================================
       Agregar un mensaje a la ventana
    =========================================== */
    function agregarMensaje(texto, tipo) {

        const burbujaMsg = document.createElement('div');

        burbujaMsg.className = 'chat-ia-msg ' +
            (tipo === 'user' ? 'chat-ia-msg-user' : 'chat-ia-msg-bot');

        if (tipo === 'user') {
            burbujaMsg.textContent = texto;
        } else {
            burbujaMsg.innerHTML = formatearTexto(texto);
        }

        mensajes.appendChild(burbujaMsg);

        mensajes.scrollTop = mensajes.scrollHeight;

        return burbujaMsg;

    }

    /* ==========================================
       Enviar mensaje
    =========================================== */
    form.addEventListener('submit', function (e) {

        e.preventDefault();

        const texto = input.value.trim();

        if (!texto) return;

        agregarMensaje(texto, 'user');
        input.value = '';

        const cargando = agregarMensaje('Escribiendo...', 'bot');

        fetch('/bodytech/public/chat_ia.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mensaje: texto })
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {

                cargando.remove();
                agregarMensaje(data.respuesta || 'No pude responder, intenta de nuevo.', 'bot');

            })
            .catch(function () {

                cargando.remove();
                agregarMensaje('Hubo un error de conexión. Intenta de nuevo.', 'bot');

            });

    });

})();