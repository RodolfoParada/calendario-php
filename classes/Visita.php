<?php
require_once __DIR__ . '/../config/Db.php';

class Visita
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Db::getInstance()->pdo();
    }

    // --------------------------
    // Obtener todas las visitas
    // --------------------------
    public function getAll(): array
    {
        $sql = "
            SELECT 
                v.Id_Visita,
                v.Id_Tecnico,
                v.Id_Cliente,
                v.fecha,
                v.hora,
                v.estado,
                t.nombre AS tecnico_nombre,
                c.nombre AS cliente_nombre
            FROM Visita v
            JOIN Tecnico t ON t.Id_Tecnico = v.Id_Tecnico
            JOIN Cliente c ON c.Id_Cliente = v.Id_Cliente
            ORDER BY v.fecha, v.hora
        ";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // --------------------------
    // Obtener visitas por semana
    // --------------------------
    public function getVisitasByWeek(string $start_date): array
    {
        $end_date = date('Y-m-d', strtotime('+6 days', strtotime($start_date)));

        $sql = "
            SELECT 
                v.Id_Visita,
                v.Id_Tecnico,
                v.Id_Cliente,
                v.fecha,
                v.hora,
                v.estado,
                t.nombre AS tecnico_nombre,
                c.nombre AS cliente_nombre
            FROM Visita v
            JOIN Tecnico t ON t.Id_Tecnico = v.Id_Tecnico
            JOIN Cliente c ON c.Id_Cliente = v.Id_Cliente
            WHERE v.fecha BETWEEN :start_date AND :end_date
            ORDER BY v.fecha, v.hora
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':start_date' => $start_date, ':end_date' => $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --------------------------
    // Crear nueva visita
    // --------------------------
    public function crear(array $data): int
    {
        $sql = "INSERT INTO Visita (Id_Tecnico, Id_Cliente, fecha, hora, estado)
                VALUES (:tec, :cli, :fecha, :hora, :estado)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tec'    => $data['id_tecnico'],
            ':cli'    => $data['id_cliente'],
            ':fecha'  => $data['fecha'],
            ':hora'   => $data['hora'],
            ':estado' => $data['estado'] ?? 'pendiente'
        ]);
        return (int)$this->db->lastInsertId();
    }

    // --------------------------
    // Actualizar visita
    // --------------------------
    public function actualizar(array $data): bool
    {
        $sql = "UPDATE Visita SET 
                    Id_Tecnico = :tec, 
                    Id_Cliente = :cli, 
                    fecha = :fecha, 
                    hora = :hora,
                    estado = :estado
                WHERE Id_Visita = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':tec'    => $data['id_tecnico'],
            ':cli'    => $data['id_cliente'],
            ':fecha'  => $data['fecha'],
            ':hora'   => $data['hora'],
            ':estado' => $data['estado'] ?? 'pendiente',
            ':id'     => $data['id']
        ]);
    }

    // --------------------------
    // Eliminar visita
    // --------------------------
    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM Visita WHERE Id_Visita = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // --------------------------
    // Obtener visita por ID
    // --------------------------
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM Visita WHERE Id_Visita = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // --------------------------
    // Cambiar estado a cancelada
    // --------------------------
    public function cancelar(int $id): bool
    {
        $sql = "UPDATE Visita SET estado = 'cancelada' WHERE Id_Visita = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Dentro de la clase Visita en Visita.php
public function getByRange($start, $end) {
    $sql = "SELECT * FROM visitas WHERE fecha BETWEEN :start AND :end";
    
    // Se agregan los nombres de los técnicos y clientes para el frontend
    $sql = "SELECT 
                v.*, 
                t.nombre AS tecnico_nombre, 
                c.nombre AS cliente_nombre 
            FROM visitas v
            JOIN tecnicos t ON v.id_tecnico = t.id
            JOIN clientes c ON v.id_cliente = c.id
            WHERE v.fecha BETWEEN :start AND :end 
            ORDER BY v.fecha, v.hora";

    // Suponiendo que tienes un método para ejecutar la consulta (ej. $this->db->query)
    // Debes vincular los parámetros :start y :end
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':start', $start);
    $stmt->bindParam(':end', $end);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
