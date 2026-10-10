import * as THREE from "https://unpkg.com/three@0.158.0/build/three.module.js";

/* =========================
   EASING CINEMATOGRÁFICO
========================= */
function easeInOutCubic(t) {
    return t < 0.5
        ? 4 * t * t * t
        : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

/* =========================
   GRÁFICO CIRCULAR 3D
========================= */
function crearGraficoCircular3D(datos, colores, animable = false) {
    const grupo = new THREE.Group();
    grupo.userData.segmentos = [];

    let anguloInicio = 0;

    datos.forEach((valor, i) => {
        const angulo = (valor / 100) * Math.PI * 2;

        const shape = new THREE.Shape();
        shape.moveTo(0, 0);
        shape.absarc(0, 0, 1, anguloInicio, anguloInicio + angulo);
        shape.lineTo(0, 0);

        const geometry = new THREE.ExtrudeGeometry(shape, {
            depth: 0.35,
            bevelEnabled: false
        });

        const material = new THREE.MeshStandardMaterial({
            color: colores[i],
            metalness: 0.45,
            roughness: 0.28
        });

        const mesh = new THREE.Mesh(geometry, material);
        mesh.rotation.x = -Math.PI / 2;

        if (animable) {
            mesh.scale.z = 0;
            mesh.position.y = -0.6;
            grupo.userData.segmentos.push(mesh);
        }

        grupo.add(mesh);
        anguloInicio += angulo;
    });

    return grupo;
}

/* =========================
   PANEL (IDLE)
========================= */
const contenedor = document.getElementById("afiliadooo-3d-container");

const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(
    45,
    contenedor.clientWidth / contenedor.clientHeight,
    0.1,
    100
);
camera.position.z = 4;

const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
renderer.setSize(contenedor.clientWidth, contenedor.clientHeight);
renderer.setPixelRatio(window.devicePixelRatio);
contenedor.appendChild(renderer.domElement);

scene.add(new THREE.AmbientLight(0xffffff, 0.7));

const light = new THREE.DirectionalLight(0xffffff, 1);
light.position.set(4, 4, 4);
scene.add(light);

const datos = [65, 25, 10];
const colores = [0x2ecc71, 0xf1c40f, 0xe74c3c];

const grafico = crearGraficoCircular3D(datos, colores);
grafico.rotation.x = Math.PI * 0.35;
grafico.rotation.y = Math.PI * 0.28;
scene.add(grafico);

// 🔽 Reducir tamaño del gráfico circular
grafico.scale.set(0.75, 0.75, 0.75);

/* IDLE */
let idleT = 0;
function animate() {
    requestAnimationFrame(animate);
    idleT += 0.008;
    grafico.rotation.y = Math.PI * 0.28 + Math.sin(idleT) * 0.1;
    renderer.render(scene, camera);
}
animate();

/* =========================
   OVERLAY CINEMATOGRÁFICO
========================= */
contenedor.addEventListener("click", abrirOverlay);

function abrirOverlay() {
    const overlay = document.getElementById("overlay-3d");
    const zoomContainer = document.getElementById("afiliadooo-3d-zoom");
    const cerrarBtn = document.getElementById("cerrar-overlay");

    overlay.classList.add("activo");
    zoomContainer.innerHTML = "";

    const sceneZ = new THREE.Scene();

    const cameraZ = new THREE.PerspectiveCamera(
        45,
        zoomContainer.clientWidth / zoomContainer.clientHeight,
        0.1,
        100
    );
    cameraZ.position.z = 6.2;

    const rendererZ = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    rendererZ.setSize(zoomContainer.clientWidth, zoomContainer.clientHeight);
    rendererZ.setPixelRatio(window.devicePixelRatio);
    zoomContainer.appendChild(rendererZ.domElement);

    sceneZ.add(new THREE.AmbientLight(0xffffff, 0.35));

    const spot = new THREE.SpotLight(0xffffff, 1.6, 25, Math.PI / 5, 0.35, 1);
    spot.position.set(3, 7, 6);
    spot.target.position.set(0, 0, 0);
    sceneZ.add(spot, spot.target);

    const halo = new THREE.DirectionalLight(0xffffff, 0.35);
    halo.position.set(-5, 3, -6);
    sceneZ.add(halo);

    const graficoZ = crearGraficoCircular3D(datos, colores, true);
    graficoZ.scale.set(1.35, 1.35, 1.35);
    graficoZ.rotation.x = Math.PI * 0.35;
    graficoZ.rotation.y = Math.PI * 0.28;
    sceneZ.add(graficoZ);
    graficoZ.scale.set(0.7, 0.7, 0.7);


    /* ===== AURA ===== */
    const glowCanvas = document.createElement("canvas");
    glowCanvas.width = 256;
    glowCanvas.height = 256;
    const ctx = glowCanvas.getContext("2d");

    const gradient = ctx.createRadialGradient(128, 128, 0, 128, 128, 128);
    gradient.addColorStop(0, "rgba(255,216,168,0.9)");
    gradient.addColorStop(0.4, "rgba(255,200,120,0.45)");
    gradient.addColorStop(1, "rgba(255,160,60,0)");

    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, 256, 256);

    const glowSprite = new THREE.Sprite(
        new THREE.SpriteMaterial({
            map: new THREE.CanvasTexture(glowCanvas),
            transparent: true,
            blending: THREE.AdditiveBlending,
            depthWrite: false
        })
    );

    glowSprite.scale.set(1.7, 1.2, 1);
    glowSprite.position.set(0, -0.2, 0);
    sceneZ.add(glowSprite);

    /* ===== MICRO SEGMENTOS ===== */
    const segmentos = graficoZ.userData.segmentos;
    let start = null;
    let terminado = false;
      
    const rotacionFinalY = Math.PI * 0.28 + Math.PI * 4;

    function animar(t) {
        if (!start) start = t;
        const p = Math.min((t - start) / 1900, 1);
        const e = easeInOutCubic(p);

        segmentos.forEach((seg, i) => {
            const d = Math.max(Math.min(p - i * 0.15, 1), 0);
            seg.scale.z = easeInOutCubic(d);
            seg.position.y = -0.6 + easeInOutCubic(d) * 0.6;
        });

        graficoZ.rotation.y = Math.PI * 0.28 + e * Math.PI * 4;
        cameraZ.position.z = 6.2 - e * 2.2;

        rendererZ.render(sceneZ, cameraZ);
        if (p < 1) { requestAnimationFrame(animar);
        } else {
            graficoZ.rotation.y = rotacionFinalY; // 🔒 se queda EXACTAMENTE ahí
            terminado = true;
            rendererZ.render(sceneZ, cameraZ);    // render final limpio
        }

    }
    requestAnimationFrame(animar);

    /* ===== INTERACCIÓN VIVA ===== */
    let tx = 0, ty = 0, cx = 0, cy = 0, drift = 0;

    function iniciarInteraccion() {
        terminado = true;

        zoomContainer.addEventListener("mousemove", e => {
            tx = (e.clientX / window.innerWidth - 0.5) * 0.6;
            ty = (e.clientY / window.innerHeight - 0.5) * 0.6;
        });

        function loop() {
            requestAnimationFrame(loop);

            drift += 0.002;
            const autoX = Math.sin(drift) * 0.15;
            const autoY = Math.cos(drift * 0.9) * 0.12;

            cx += (tx - cx) * 0.08;
            cy += (ty - cy) * 0.08;

            graficoZ.rotation.y += ((Math.PI * 0.28 + cx + autoX) - graficoZ.rotation.y) * 0.07;
            graficoZ.rotation.x += ((Math.PI * 0.35 - cy + autoY) - graficoZ.rotation.x) * 0.07;

            graficoZ.position.x = cx * 0.4;
            graficoZ.position.y = -cy * 0.3;

            rendererZ.render(sceneZ, cameraZ);
        }
        loop();
    }

    cerrarBtn.onclick = () => {
        overlay.classList.remove("activo");
        zoomContainer.innerHTML = "";
    };
}
