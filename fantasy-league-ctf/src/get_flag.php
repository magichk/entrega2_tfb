<?php
// Proxy interno - Solo accesible vía fetch() desde JavaScript
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// 1. Verificar que el usuario está autenticado
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Not authenticated']));
}

// 2. Validar que viene de un navegador (tiene referer de la misma app)
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';

if (empty($referer) || strpos($referer, $host) === false) {
    http_response_code(403);
    die(json_encode([
        'error' => 'Access denied',
        'message' => 'Must be called from application pages'
    ]));
}

// 3. Validar que NO es una petición directa del navegador
// (fetch desde JS no envía ciertos headers que sí envía navegación directa)
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
if (strpos($accept, 'text/html') !== false) {
    http_response_code(403);
    die(json_encode([
        'error' => 'Direct access not allowed',
        'message' => 'Must be called via JavaScript fetch()'
    ]));
}

// 4. Hacer petición INTERNA a la API (desde 127.0.0.1)
$api_url = 'http://127.0.0.1/api_flag.php?token=admin_access_2024';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_INTERFACE, '127.0.0.1');
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code(500);
    die(json_encode(['error' => 'Failed to retrieve flag']));
}

// Retornar respuesta
echo $response;
?>
