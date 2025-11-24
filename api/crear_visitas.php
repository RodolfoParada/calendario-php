<?php
// crear_visitas.php
// Este controlador maneja la CREACIÓN y la EDICIÓN (UPDATE) de visitas.

require_once __DIR__ . '/../config/Db.php';
require_once __DIR__ . '/../classes/Visita.php';
header("Content-Type: text/html; charset=UTF-8");

// Captura de datos
$id_edit = $_POST["id_visita_edit"] ?? null; // ID de la visita a editar
$id_tecnico = $_POST['id_tecnico'] ?? null;
$id_cliente = $_POST['id_cliente'] ?? null;
$fecha      = $_POST['fecha'] ?? null;
$hora       = $_POST['hora'] ?? null;
$estado     = $_POST['estado'] ?? 'pendiente'; // Capturado del input oculto en modo edición

// =========================================================
// 1. VALIDACIÓN DE DATOS OBLIGATORIOS
// =========================================================
if (
    empty($id_tecnico) ||
    empty($id_cliente) ||
    empty($fecha) ||
    empty($hora)
) {
    // Si faltan datos, redirigir con error.
    $redirect_fecha = $fecha ?? date('Y-m-d');
    header("Location: ../public/index.php?error=missing_data&fecha=" . $redirect_fecha);
    exit;
}

try {
    $db = Db::getInstance()->pdo();
    $visita = new Visita(); // Necesitamos el modelo Visita para métodos auxiliares

    // =========================================================
    // 2. PREVENCIÓN DE DUPLICADOS (Aplica a CREACIÓN y EDICIÓN)
    // =========================================================
    
    $sql_check = "SELECT Id_Visita FROM Visita 
                  WHERE fecha = :fecha AND hora = :hora 
                  AND (Id_Tecnico = :tecnico OR Id_Cliente = :cliente)";

    $params_check = [
        ':fecha' => $fecha,
        ':hora' => $hora,
        ':tecnico' => $id_tecnico,
        ':cliente' => $id_cliente
    ];

    // ✅ CORRECCIÓN CRÍTICA: Excluir el registro actual si estamos editando
    if (!empty($id_edit)) { 
        $sql_check .= " AND Id_Visita != :id_edit"; 
        $params_check[':id_edit'] = $id_edit;
    }

    $stmt_check = $db->prepare($sql_check);
    $stmt_check->execute($params_check);

    if ($stmt_check->fetch()) {
        // Si la consulta encuentra un registro (que no sea el actual), es un DUPLICADO.
        header("Location: ../public/index.php?error=duplicate&fecha=" . $fecha);
        exit;
    }

    // =========================================================
    // 3. CREACIÓN O ACTUALIZACIÓN
    // =========================================================

    if (!empty($id_edit)) {
        // LÓGICA DE ACTUALIZACIÓN (UPDATE)
        $msg_type = "edit=1";
        
        // Asumo que el método actualizar de Visita::actualizar() ya fue corregido para funcionar
        $visita->actualizar([
            'id'         => $id_edit,
            'id_tecnico' => $id_tecnico,
            'id_cliente' => $id_cliente,
            'fecha'      => $fecha,
            'hora'       => $hora,
            'estado'     => $estado // Usar el estado capturado (original o modificado)
        ]);
        
    } else {
        // LÓGICA DE CREACIÓN (INSERT)
        $msg_type = "success=1";
        
        // El método crear requiere un array asociativo
        $visita->crear([
            'id_tecnico' => $id_tecnico,
            'id_cliente' => $id_cliente,
            'fecha'      => $fecha,
            'hora'       => $hora,
            'estado'     => 'pendiente'
        ]);
    }

    // =========================================================
    // 4. REDIRECCIÓN FINAL DE ÉXITO
    // =========================================================
    // Redirigir a index.php con el mensaje y la fecha para que el calendario se refresque correctamente
    header("Location: ../public/index.php?" . $msg_type . "&fecha=" . $fecha);
    exit;

} catch (Exception $e) {
    // Manejo de errores de la base de datos
    die("Error al guardar o editar visita: " . $e->getMessage());
}