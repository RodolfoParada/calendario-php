<?php
// src/classes/Cliente.php
require_once __DIR__ . '/../config/Db.php';

class Cliente
{
    private PDO $db;

    public function __construct()
    {
        // Obtiene la instancia única de la conexión PDO
        $this->db = Db::getInstance()->pdo();
    }

    /**
     * Obtener todos los clientes
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM Cliente ORDER BY nombre";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener solo clientes activos
     */
    public function listarActivos(): array
    {
        $sql = "SELECT * FROM Cliente WHERE activo = 1 ORDER BY nombre";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener cliente por ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM Cliente WHERE Id_Cliente = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Crear un nuevo cliente
     */
    public function create(
        string $nombre,
        ?string $direccion = null,
        ?string $telefono = null,
        ?string $correo = null
    ): int {
        if (empty($nombre)) {
            throw new Exception("El nombre del cliente es obligatorio.");
        }

        $sql = "INSERT INTO Cliente (nombre, direccion, telefono, correo, activo)
                VALUES (?, ?, ?, ?, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre, $direccion, $telefono, $correo]);

        return intval($this->db->lastInsertId());
    }

    /**
     * Actualizar cliente existente
     */
    public function update(
        int $id,
        string $nombre,
        ?string $direccion,
        ?string $telefono,
        ?string $correo,
        int $activo
    ): bool {
        $sql = "UPDATE Cliente
                SET nombre = ?, direccion = ?, telefono = ?, correo = ?, activo = ?
                WHERE Id_Cliente = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre, $direccion, $telefono, $correo, $activo, $id]);
    }

    /**
     * Desactivar cliente
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE Cliente SET activo = 0 WHERE Id_Cliente = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
