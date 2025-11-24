<?php
require_once __DIR__ . '/../config/Db.php';

if (empty($_POST['id_visita'])) {
    header("Location: /calendario/public/index.php?error=missing_id");
    exit;
}

$id = $_POST['id_visita'];

try {
    $db = Db::getInstance()->pdo();
    $sql = "DELETE FROM Visita WHERE Id_Visita = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);

    header("Location: /calendario/public/index.php?success=delete_ok");
    exit;

} catch (Exception $e) {
    header("Location: /calendario/public/index.php?error=delete_fail");
    exit;
}
