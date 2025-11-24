<?php
require_once __DIR__ . '/../config/Db.php';  // según tu estructura real

class VisitasModel {
    private PDO $pdo;

    public function __construct() {
        // 🚀 USAR TU SINGLETON
        $this->pdo = Db::getInstance()->pdo();
    }

    // obtiene las visitas entre dos fechas
    public function getByRange(string $start, string $end): array {
        $sql = "SELECT 
                    id AS id, 
                    cliente AS title, 
                    CONCAT(fecha,'T',hora) AS start 
                FROM visitas 
                WHERE fecha BETWEEN ? AND ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$start, $end]);
        return $stmt->fetchAll();
    }
}
