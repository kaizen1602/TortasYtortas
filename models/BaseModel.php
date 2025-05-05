<?php
require_once '../config/database.php';

class BaseModel {
    protected $table;
    protected $db;
    protected $conn;

    public function __construct($table) {
        $this->table = $table;
        $database = new Database();
        $this->conn = $database->conectar();
    }

    // Obtener todos los registros
    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un registro por ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo registro
    public function create($data) {
        $columns = implode(", ", array_keys($data));
        $values = ":" . implode(", :", array_keys($data));
        
        $query = "INSERT INTO " . $this->table . " (" . $columns . ") VALUES (" . $values . ")";
        $stmt = $this->conn->prepare($query);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":" . $key, $value);
        }
        
        if (!$stmt->execute()) {
            $error = $stmt->errorInfo();
            error_log("Error al ejecutar la consulta de inserción: " . print_r($error, true));
            return false;
        }

        return true;
    }

    // Actualizar un registro
    public function update($id, $data) {
        $set = "";
        foreach ($data as $key => $value) {
            $set .= $key . "=:" . $key . ", ";
        }
        $set = rtrim($set, ", ");
        
        $query = "UPDATE " . $this->table . " SET " . $set . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":" . $key, $value);
        }
        $stmt->bindValue(":id", $id);
        
        return $stmt->execute();
    }

    // Eliminar un registro
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?> 