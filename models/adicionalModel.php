<?php
require_once 'BaseModel.php';
class AdicionalModel extends BaseModel {
    public function __construct() {
        parent::__construct('adicionales');
    }

    public function crearAdicional($producto_id, $nombre, $precio, $precio_venta, $stock) {
        try {
            // Validar que no exista un adicional con el mismo nombre para el mismo producto (case insensitive)
            $queryCheck = "SELECT id FROM adicionales WHERE LOWER(nombre) = LOWER(:nombre) AND producto_id = :producto_id";
            $stmtCheck = $this->conn->prepare($queryCheck);
            $stmtCheck->bindParam(':nombre', $nombre);
            $stmtCheck->bindParam(':producto_id', $producto_id);
            $stmtCheck->execute();
            if ($stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                // Ya existe un adicional con ese nombre para ese producto
                return false;
            }
            $query = "INSERT INTO adicionales (producto_id, nombre, precio, precio_venta, stock) VALUES (:producto_id, :nombre, :precio, :precio_venta, :stock)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':producto_id', $producto_id);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':precio_venta', $precio_venta);
            $stmt->bindParam(':stock', $stock);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al crear adicional: " . $e->getMessage());
            throw $e;
        }
    }

    public function obtenerAdicionales() {
        try {
            $query = "SELECT * FROM adicionales ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener adicionales: " . $e->getMessage());
            throw $e;
        }
    }

    public function obtenerAdicionalPorId($id) {
        try {
            $query = "SELECT * FROM adicionales WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener adicional por ID: " . $e->getMessage());
            throw $e;
        }
    }

    public function actualizarAdicional($id, $producto_id, $nombre, $precio, $precio_venta, $stock) {
        try {
            $query = "UPDATE adicionales SET producto_id = :producto_id, nombre = :nombre, precio = :precio, precio_venta = :precio_venta, stock = :stock WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':producto_id', $producto_id);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':precio_venta', $precio_venta);
            $stmt->bindParam(':stock', $stock);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar adicional: " . $e->getMessage());
            throw $e;
        }
    }

    public function eliminarAdicional($id) {
        try {
            $query = "DELETE FROM adicionales WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al eliminar adicional: " . $e->getMessage());
            throw $e;
        }
    }
} 