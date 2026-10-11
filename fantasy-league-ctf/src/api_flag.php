<?php
// API Endpoint - SOLO accesible desde 127.0.0.1 (localhost interno)
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Obtener IP real del cliente
$client_ip = $_SERVER['REMOTE_ADDR'] ?? '';

// Verificar si viene a través de proxy (X-Forwarded-For)
$forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
if (!empty($forwarded)) {
    // Si hay X-Forwarded-For, rechazar (intento de bypass)
    http_response_code(403);
    die(json_encode([
        'error' => 'Proxy detected',
        'message' => 'Direct localhost access only'
    ]));
}

// RESTRICCIÓN ESTRICTA: Solo 127.0.0.1 o ::1
if ($client_ip !== '127.0.0.1' && $client_ip !== '::1') {
    http_response_code(403);
    die(json_encode([
        'error' => 'Access denied',
        'message' => 'API only accessible from localhost',
        'your_ip' => $client_ip,
        'hint' => 'Use the proxy endpoint from the application'
    ]));
}

// Validar token
$token = $_GET['token'] ?? '';
if ($token !== 'admin_access_2024') {
    http_response_code(401);
    die(json_encode(['error' => 'Invalid token']));
}

// Retornar la flag
$flag = getenv('FLAG_CSP') ?: 'MCSEH{csp_1s_n0t_s0_s3cur3_4ft3r_all}';

echo json_encode([
    'success' => true,
    'flag' => $flag
]);
?>
