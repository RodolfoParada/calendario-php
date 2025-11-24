<?php
// actualizar_visitas.php

// Incluir la clase Visita
require_once __DIR__ . '/../classes/Visita.php';

// =========================================================
// 1. MANEJO DE FORMULARIO (UPDATE COMPLETO vía $_POST)
// =========================================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST)) {

    $id     = $_POST["id_visita_edit"] ?? null;
    $tec    = $_POST["id_tecnico"] ?? null;
    $cli    = $_POST["id_cliente"] ?? null;
    $fecha  = $_POST["fecha"] ?? null; // Fecha de la visita (CRÍTICO para redirección)
    $hora   = $_POST["hora"] ?? null;
    $estado = $_POST["estado"] ?? 'pendiente'; // Valor capturado del input oculto

    // Validación de datos obligatorios para la edición
    if (!$id || !$tec || !$cli || !$fecha || !$hora) {
        $redirect_fecha = $fecha ?? date('Y-m-d');
        // Redirigir con el mensaje de error y la fecha para no perder la vista
        header("Location: ../public/index.php?error=missing_data&fecha=" . $redirect_fecha);
        exit;
    }

    $visita = new Visita();

    // Ejecutar la actualización completa en la base de datos
    $ok = $visita->actualizar([
        'id'         => $id,
        'id_tecnico' => $tec,
        'id_cliente' => $cli,
        'fecha'      => $fecha,
        'hora'       => $hora,
        'estado'     => $estado // ✅ Se usa el estado capturado del formulario
    ]);

    if ($ok) {
        // ✅ Redirigir a index.php con la fecha de la visita actualizada
        header("Location: ../public/index.php?edit=1&fecha=" . $fecha);
        exit;
    }

    // Si la actualización falla por motivos de DB
    header("Location: ../public/index.php?error=update_fail&fecha=" . $fecha);
    exit;
}

// =========================================================
// 2. MANEJO DE AJAX (STATUS UPDATE vía JSON)
// (Este bloque está corregido para evitar errores NULL en la DB)
// =========================================================

$data = json_decode(file_get_contents('php://input'), true);

// Verificamos que al menos tengamos el ID y el nuevo estado
if(!empty($data['id']) && isset($data['estado'])){
    
    $visita = new Visita();
    $id_visita = (int)$data['id'];
    
    // 1. Obtener los datos existentes para preservar los campos NOT NULL (tec, cli, fecha, hora)
    $current_data = $visita->getById($id_visita); 

    if (!$current_data) {
        echo json_encode(['ok' => false, 'error' => 'Visita ID no encontrada para actualizar estado.']);
        exit;
    }
    
    // 2. ✅ CORRECCIÓN: Ejecutar la actualización completa, preservando los datos antiguos y solo cambiando el estado
    // Usamos el método actualizar() existente, pero le pasamos los datos antiguos y el estado nuevo.
    $ok = $visita->actualizar([
        'id'         => $id_visita,
        'id_tecnico' => $current_data['Id_Tecnico'],  // Preservar valor
        'id_cliente' => $current_data['Id_Cliente'], // Preservar valor
        'fecha'      => $current_data['fecha'],      // Preservar valor
        'hora'       => $current_data['hora'],       // Preservar valor
        'estado'     => $data['estado']              // Aplicar el nuevo estado
    ]);
    
    // Devolvemos la respuesta JSON al cliente
    echo json_encode(['ok' => $ok]);
    exit;
}

// Si la solicitud no cumple con ninguna de las condiciones anteriores
echo json_encode(['ok'=>false]);