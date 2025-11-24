<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../classes/Cliente.php';

$cliente = new Cliente();
$data = $cliente->getAll();

echo json_encode($data);
