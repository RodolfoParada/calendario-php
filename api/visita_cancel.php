<?php
// src/api/visita_cancel.php
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Visita.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de visita requerido.']);
    exit;
}

try {
    $visita = new Visita();
    $ok = $visita->cancelar($id); // Llama al método cancelar de la clase Visita
    echo json_encode(['success' => (bool)$ok]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}