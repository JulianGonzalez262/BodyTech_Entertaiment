<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bodytech/app/config/ollama_config.php';

$payload = json_encode([
    'model' => OLLAMA_MODEL,
    'messages' => [
        ['role' => 'user', 'content' => 'hola']
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
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlErrno = curl_errno($ch);
curl_close($ch);

echo "<h3>URL usada:</h3>";
echo OLLAMA_URL;

echo "<h3>Código HTTP:</h3>";
echo $httpCode;

echo "<h3>Código de error cURL:</h3>";
echo $curlErrno;

echo "<h3>Mensaje de error cURL:</h3>";
echo $curlError ?: "(ninguno)";

echo "<h3>Respuesta:</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";