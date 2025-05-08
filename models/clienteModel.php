<?php
require_once 'BaseModel.php';
class ClienteModel extends BaseModel {
    public function __construct() {
        parent::__construct('clientes');
    }

    public function crearCliente($nombre, $cedula, $direccion, $telefono) {
        try {
            $query = "INSERT INTO clientes (nombre, cedula, direccion, telefono) VALUES (:nombre, :cedula, :direccion, :telefono)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al crear Cliente completo: " . $e->getMessage());
            throw $e;
        }
    }

    public function obtenerClientes($estado = null) {
        try {
            $query = "SELECT *, CASE WHEN estado = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado_texto FROM clientes";
            if ($estado !== null) {
                $query .= " WHERE estado = :estado";
            }
            $query .= " ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            if ($estado !== null) {
                $stmt->bindParam(':estado', $estado);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener clientes: " . $e->getMessage());
            throw $e;
        }
    }

    public function obtenerClientePorId($id) {
        try {
            $query = "SELECT * FROM clientes WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener cliente por ID: " . $e->getMessage());
            throw $e;
        }
    }

    public function actualizarCliente($id, $nombre, $cedula, $direccion, $telefono) {
        try {
            $query = "UPDATE clientes 
                     SET nombre = :nombre, 
                         cedula = :cedula, 
                         direccion = :direccion, 
                         telefono = :telefono 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':telefono', $telefono);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar cliente: " . $e->getMessage());
            throw $e;
        }
    }

    public function cambiarEstadoCliente($id, $estado) {
        try {
            $query = "UPDATE clientes SET estado = :estado WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':estado', $estado);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al cambiar estado del cliente: " . $e->getMessage());
            throw $e;
        }
    }

    public function buscarClientes($termino) {
        try {
            $query = "SELECT * FROM clientes 
                     WHERE nombre LIKE :termino 
                     OR cedula LIKE :termino 
                     OR telefono LIKE :termino";
            
            $stmt = $this->conn->prepare($query);
            $termino = "%$termino%";
            $stmt->bindParam(':termino', $termino);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al buscar clientes: " . $e->getMessage());
            throw $e;
        }
    }
} 