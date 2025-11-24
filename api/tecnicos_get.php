<?php
require_once __DIR__ . '/../models/Visita.php';

header('Content-Type: application/json');

$start = $_GET['start'] ?? null;
$end   = $_GET['end'] ?? null;

$visitaModel = new Visita();

if ($start && $end) {
    $data = $visitaModel->getByRange($start, $end);
} else {
    $data = $visitaModel->getAll();
}

echo json_encode($data);
