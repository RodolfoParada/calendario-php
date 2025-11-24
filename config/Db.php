<?php
// config/Db.php

final class Db {
    // Propiedad estática para almacenar la ÚNICA instancia (Singleton)
    private static ?Db $instance = null;
    private PDO $pdo;

    // 🚀 CONFIGURACIÓN DE LA BASE DE DATOS INTEGRADA
    // MODIFICA ESTOS VALORES SEGÚN TU ENTORNO LOCAL
    private const DB_HOST    = '127.0.0.1';
    private const DB_PORT    = '3306';
    private const DB_NAME    = 'calendario'; // <-- ¡CAMBIAR!
    private const DB_USER    = 'root'; // <-- ¡CAMBIAR!
    private const DB_PASS    = 'admin'; // <-- ¡CAMBIAR!
    private const DB_CHARSET = 'utf8mb4';

    // El constructor debe ser PRIVADO para el patrón Singleton.
    private function __construct() {
        $dsn = 'mysql:host=' . self::DB_HOST . ';port=' . self::DB_PORT . ';dbname=' . self::DB_NAME . ';charset=' . self::DB_CHARSET;
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            // Intenta la conexión
            $this->pdo = new PDO($dsn, self::DB_USER, self::DB_PASS, $opts);
        } catch (\PDOException $e) {
            // Lanza una excepción controlada
            throw new Exception("Error de Conexión a la Base de Datos. Detalles: " . $e->getMessage());
        }
    }

    // Método ESTÁTICO: Única forma de obtener la instancia de Db.
    public static function getInstance(): Db {
        if (self::$instance === null) {
            self::$instance = new Db();
        }
        return self::$instance;
    }

    // Método para obtener el objeto PDO (la conexión real)
    public function pdo(): PDO {
        return $this->pdo;
    }
}