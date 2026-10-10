<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/ollama_config.php';

$input = json_decode(file_get_contents('php://input'), true);
$mensaje = trim($input['mensaje'] ?? '');

if ($mensaje === '') {
    echo json_encode([
        'respuesta' => 'Escribe tu pregunta sobre nutrición, entrenamiento, gimnasio o calistenia.'
    ]);
    exit;
}

/* =========================
   INSTRUCCIONES DEL ASISTENTE
   (esto es lo que limita el tema)
========================= */
$systemInstruction =
    "Eres el asistente de guía de BodyTech, un gimnasio. " .
    "SOLO puedes responder preguntas sobre nutrición, entrenamiento físico, " .
    "gimnasio y calistenia. " .
    "Si el usuario pregunta cualquier otra cosa que no tenga que ver con esos " .
    "temas (por ejemplo tecnología, tareas, otros temas generales), responde " .
    "amablemente que solo puedes ayudar con temas de fitness, nutrición y " .
    "entrenamiento, y no contestes la pregunta fuera de tema. " .
    "Responde siempre en español, de forma breve, clara y motivadora.";

$payload = json_encode([
    'model' => OLLAMA_MODEL,
    'messages' => [
        ['role' => 'system', 'content' => $systemInstruction],
        ['role' => 'user', 'content' => $mensaje]
    ],
    'stream' => false
]);

$ch = curl_init(OLLAMA_URL);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 180); // los modelos locales pueden tardar, sobre todo en respuestas largas

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlErrno = curl_errno($ch);
curl_close($ch);

if ($curlErrno === CURLE_OPERATION_TIMEDOUT) {

    echo json_encode([
        'respuesta' => 'El asistente está pensando demasiado y se agotó el tiempo de espera. Intenta con una pregunta más corta, o espera un momento y vuelve a intentar.'
    ]);
    exit;

}

if ($curlError) {

    // Típicamente significa que Ollama no está corriendo
    echo json_encode([
        'respuesta' => 'No pude conectar con el asistente local. Verifica que Ollama esté corriendo (ollama serve).'
    ]);
    exit;

}

$data = json_decode($response, true);
$textoRespuesta = $data['message']['content'] ?? null;

if ($httpCode !== 200 || empty($textoRespuesta)) {

    echo json_encode([
        'respuesta' => 'Lo siento, tuve un problema para responder. Intenta de nuevo en un momento.'
    ]);
    exit;

}

echo json_encode([
    'respuesta' => trim($textoRespuesta)
]);