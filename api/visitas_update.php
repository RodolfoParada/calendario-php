<?php
// src/api/visitas_update.php
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Visita.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron datos']);
    exit;
}

$id = isset($input['id']) ? (int)$input['id'] : null;
$fecha = $input['fecha'] ?? null;
$hora  = $input['hora'] ?? null;

if (!$id || !$fecha || !$hora) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

try {
    $visita = new Visita();
    $ok = $visita->updateDateTime($id, $fecha, $hora);
    echo json_encode(['success' => (bool)$ok]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
