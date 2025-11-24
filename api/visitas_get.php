<?php
// src/api/visitas_get.php
header('Content-Type: application/json; charset=utf-8');

// DEBUG MODE (dev only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// evitar salidas previas
ob_start();

require_once __DIR__ . '/../classes/Visita.php';

try {
    $visita = new Visita();

    // CAPTURA DE RANGO DE FECHAS
    $start = isset($_GET['start']) ? trim($_GET['start']) : null;
    $end   = isset($_GET['end'])   ? trim($_GET['end'])   : null;

    if ($start && $end) {
        // usar rango (si se envían start y end desde JS)
        $data = $visita->getByRange($start, $end);
    } else {
        // En caso de que no se envíe rango, se obtienen todas (Comportamiento por defecto)
        $data = $visita->getAll();
    }

    $events = [];
    foreach ($data as $v) {
        $hora_fin = date('H:i:s', strtotime($v['hora'] . ' +1 hour'));
        $events[] = [
            'id'       => (int)$v['Id_Visita'],
            'title'    => $v['tecnico_nombre'] . " ➜ " . $v['cliente_nombre'],
            'start'    => $v['fecha'] . 'T' . substr($v['hora'],0,8),
            'end'      => $v['fecha'] . 'T' . $hora_fin,
            'allDay'   => false,
            'editable' => true,
            'color'    => ($v['estado'] === 'realizada') ? '#28a745' :
                          (($v['estado'] === 'cancelada') ? '#dc3545' : '#0d6efd')
        ];
    }

    // limpiar cualquier salida anterior (evita BOM/HTML)
    ob_clean();
    // asegurar UTF-8 sin BOM
    echo json_encode($events, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}