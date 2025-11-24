<?php
// src/classes/Tecnico.php
require_once __DIR__ . '/../config/Db.php';

class Tecnico
{
    private PDO $db;

    public function __construct()
    {
        // Obtiene la instancia única de la conexión PDO
        $this->db = Db::getInstance()->pdo();
    }

    /**
     * Obtener todos los técnicos
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM Tecnico ORDER BY nombre";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener técnicos solo activos
     */
    public function listarActivos(): array
    {
        $sql = "SELECT * FROM Tecnico WHERE activo = 1 ORDER BY nombre";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener técnico por ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM Tecnico WHERE Id_Tecnico = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Crear un nuevo técnico
     */
    public function create(
        int $rut,
        string $nombre,
        ?string $telefono = null,
        ?string $correo = null,
        int $activo = 1
    ): int {
        if (empty($nombre)) {
            throw new Exception("El nombre del técnico es obligatorio.");
        }

        $sql = "INSERT INTO Tecnico (rut, nombre, telefono, correo, activo)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$rut, $nombre, $telefono, $correo, $activo]);

        return intval($this->db->lastInsertId());
    }

    /**
     * Actualizar técnico existente
     */
    public function update(
        int $id,
        int $rut,
        string $nombre,
        ?string $telefono = null,
        ?string $correo = null,
        int $activo = 1
    ): bool {
        $sql = "UPDATE Tecnico
                SET rut = ?, nombre = ?, telefono = ?, correo = ?, activo = ?
                WHERE Id_Tecnico = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$rut, $nombre, $telefono, $correo, $activo, $id]);
    }

    /**
     * Desactivar técnico
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE Tecnico SET activo = 0 WHERE Id_Tecnico = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
