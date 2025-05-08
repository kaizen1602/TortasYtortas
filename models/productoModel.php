<?php
require_once 'BaseModel.php';
require_once '../models/PedidoModel.php';

class ProductoModel extends BaseModel {
    public function __construct() {
        parent::__construct('productos');
    }

    public function crearProducto($nombre, $precio_base, $descuento, $stock, $estado) {
        $query = "INSERT INTO productos (nombre, precio_base, descuento, stock, estado) 
                  VALUES (:nombre, :precio_base, :descuento, :stock, :estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':precio_base', $precio_base);
        $stmt->bindParam(':descuento', $descuento);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function obtenerProductos() {
        $query = "SELECT * FROM productos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProductoPorId($id) {
        $query = "SELECT * FROM productos WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarProducto($id, $nombre, $precio_base, $descuento, $stock, $estado) {
        $query = "UPDATE productos 
                  SET nombre = :nombre, precio_base = :precio_base, descuento = :descuento, stock = :stock, estado = :estado
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':precio_base', $precio_base);
        $stmt->bindParam(':descuento', $descuento);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':estado', $estado);
        return $stmt->execute();
    }

    public function cambiarEstadoProducto($id, $estado) {
        $query = "UPDATE productos SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':estado', $estado);
        return $stmt->execute();
    }

    public function obtenerAdicionalesPorProducto($producto_id) {
        try {
            $query = "SELECT a.* 
                     FROM adicionales a
                     JOIN producto_adicionales pa ON a.id = pa.adicional_id
                     WHERE pa.producto_id = :producto_id
                     AND a.estado = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':producto_id', $producto_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener adicionales del producto: " . $e->getMessage());
            throw $e;
        }
    }

    public function agregarAdicionalAProducto($producto_id, $adicional_id) {
        try {
            $query = "INSERT INTO producto_adicionales (producto_id, adicional_id)
                     VALUES (:producto_id, :adicional_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':producto_id', $producto_id);
            $stmt->bindParam(':adicional_id', $adicional_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al agregar adicional al producto: " . $e->getMessage());
            throw $e;
        }
    }

    public function eliminarAdicionalDeProducto($producto_id, $adicional_id) {
        try {
            $query = "DELETE FROM producto_adicionales 
                     WHERE producto_id = :producto_id 
                     AND adicional_id = :adicional_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':producto_id', $producto_id);
            $stmt->bindParam(':adicional_id', $adicional_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al eliminar adicional del producto: " . $e->getMessage());
            throw $e;
        }
    }
} 