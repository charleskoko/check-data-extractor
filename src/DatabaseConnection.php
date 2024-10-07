<?php

class DatabaseConnection
{
    private static $instance = null;
    private $conn;
    private string $db_host;
    private string $db_port;
    private string $db_user;
    private string $db_pass;
    private string $db_name;

    public function __construct(string $db_host, string $db_port, string $db_user, string $db_pass, string $db_name)
    {
        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->db_name = $db_name;
        $this->db_port = $db_port;

        try {
            $this->conn = new PDO("mysql:host={$this->db_host};port={$this->db_port};dbname={$this->db_name}", $this->db_user, $this->db_pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            throw new \RuntimeException("Failed to connect to the database: " . $e->getMessage());
        }
    }

    public static function getInstance(string $db_host, string $db_port, string $db_user, string $db_pass, string $db_name): DatabaseConnection
    {
        if (!self::$instance) {
            self::$instance = new DatabaseConnection($db_host, $db_port, $db_user, $db_pass, $db_name);
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}

