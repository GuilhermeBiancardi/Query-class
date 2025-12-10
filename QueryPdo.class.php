<?php

namespace Biancardi\Database;

class QueryPdo {

    private $conn;
    private static $instance = null;

    private $names = "utf8mb4";
    private $collation = "utf8mb4_0900_ai_ci";

    private function __construct(string $dsn = "", string $user = "", string $pass = "") {
        $dsn = $dsn ?: "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . $this->names;
        $user = $user ?: DB_USER;
        $pass = $pass ?: DB_PASS;

        try {
            $this->conn = new PDO($dsn, $user, $pass, [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $this->names . " COLLATE " . $this->collation,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $this->setCollation();
        } catch (PDOException $e) {
            $this->logError("Connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private function execute(string $sql, array $params = []): bool {
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    private function setCollation(): void {
        try {
            $this->conn->exec("SET NAMES " . $this->names . " COLLATE " . $this->collation);
            $this->conn->exec("SET SESSION character_set_client = " . $this->names);
            $this->conn->exec("SET SESSION character_set_results = " . $this->names);
            $this->conn->exec("SET SESSION character_set_connection = " . $this->names);
            $this->conn->exec("SET SESSION collation_connection = " . $this->collation);
        } catch (PDOException $e) {
            // Não impede a execução, só loga (diagnóstico)
            $this->logError("Failed to set " . $this->names . " session: " . $e->getMessage());
        }
    }

    private function logError(string $message): void {
        error_log("[QueryPdo Error] " . $message);
    }

    public static function getInstance(string $dsn = "", string $user = "", string $pass = ""): self {
        if (self::$instance === null) {
            self::$instance = new self($dsn, $user, $pass);
        }
        return self::$instance;
    }

    public function query(string $sql, array $params = []): array | false {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result ?: false;
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Select query failed: " . $e->getMessage());
        }
    }

    public function select(string $sql, array $params = []): array | false {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result ?: false;
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Select query failed: " . $e->getMessage());
        }
    }

    public function setCharset($name) {
        $this->names = $name;
    }

    public function setCollate($collation) {
        $this->collation = $collation;
    }

    public function insert(string $sql, array $params = []): int {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return (int) $this->conn->lastInsertId();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Insert query failed: " . $e->getMessage());
        }
    }

    public function update(string $sql, array $params = []): bool {
        return $this->execute($sql, $params);
    }

    public function delete(string $sql, array $params = []): bool {
        return $this->execute($sql, $params);
    }

    public function beginTransaction() {
        $this->conn->beginTransaction();
    }

    public function prepare(string $sql, array $params = []): bool {
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            throw new Exception("Prepare query failed: " . $e->getMessage());
        }
    }

    public function commit() {
        $this->conn->commit();
    }

    public function rollback() {
        $this->conn->rollBack();
    }

}

?>


