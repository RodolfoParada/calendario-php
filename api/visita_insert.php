<?php
// src/api/visitas_insert.php
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Visita.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron datos']);
    exit;
}

// Captura de datos JSON
$tecnico = isset($input['tecnico']) ? (int)$input['tecnico'] : null;
$cliente = isset($input['cliente']) ? (int)$input['cliente'] : null;
$fecha   = $input['fecha'] ?? null;
$hora    = $input['hora'] ?? null;

// Validación básica
if (!$tecnico || !$cliente || !$fecha || !$hora) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos de visita incompletos']);
    exit;
}

try {
    $visita = new Visita();

    // ✅ Corrección de la estructura: Asegura que las claves coincidan con Visita::crear()
    $data_to_insert = [
        'id_tecnico' => $tecnico,
        'id_cliente' => $cliente,
        'fecha'      => $fecha,
        'hora'       => $hora,
        // No es necesario 'estado' si el modelo lo establece por defecto[cite: 539].
    ];
    
    // ✅ La llamada al método Visita::crear() debe devolver el ID insertado.
    $id_insertado = $visita->crear($data_to_insert); // Retorna (int)$this->db->lastInsertId() 
    
    // Devolvemos el ID al cliente
    echo json_encode(['success' => true, 'id' => $id_insertado]);

} catch (Exception $e) {
    // Retorna error 400 (Bad Request)
    http_response_code(400); 
    echo json_encode(['success' => false, 'error' => "Error al insertar visita: " . $e->getMessage()]);
}