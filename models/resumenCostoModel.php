<?php
require_once 'BaseModel.php';
require_once '../models/PedidoModel.php';
/**
 * Modelo para obtener resúmenes y totales de clientes, pedidos y ventas.
 * Incluye métodos reutilizables para el controlador resumenCostoController.php
 */
class ResumenCostoModel extends BaseModel {
    public function __construct() {
        parent::__construct('log_ventas');
    }

    /**
     * Cuenta el total de clientes
     * @return int
     */
    public function contarClientes() {
        $query = "SELECT COUNT(*) as total FROM clientes";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    /**
     * Cuenta el total de pedidos
     * @return int
     */
    public function contarPedidos() {
        $query = "SELECT COUNT(*) as total FROM pedidos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    /**
     * Suma el total de ventas de pedidos activos
     * @return float
     */
    public function sumarVentas() {
        $query = "SELECT SUM(total_pagado) as total_ventas FROM pedidos WHERE estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($row['total_ventas'] ?? 0);
    }

    /**
     * Obtiene el resumen de ventas con cliente y productos, calculando la ganancia real
     * @return array
     */
    public function obtenerResumenVentas() {
        try {
            $query = "SELECT lv.*, c.nombre AS cliente_nombre
                      FROM log_ventas lv
                      JOIN pedidos p ON lv.pedido_id = p.id
                      JOIN clientes c ON p.cliente_id = c.id
                      ORDER BY lv.fecha DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $resumen = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Devolver los datos numéricos como float, no como string formateado
            return array_map(function($row) {
                return [
                    'cliente' => htmlspecialchars($row['cliente_nombre']),
                    'producto' => htmlspecialchars($row['nombre_producto']),
                    'cantidad' => (int)$row['cantidad'],
                    'costo_unitario' => floatval($row['costo_unitario'] ?? 0),
                    'precio_venta_unitario' => floatval($row['precio_venta_unitario'] ?? 0),
                    'descuento' => floatval($row['descuento'] ?? 0),
                    'precio_adicionales' => floatval($row['precio_adicionales'] ?? 0),
                    'total' => floatval($row['total'] ?? 0),
                    'ganancia' => floatval($row['ganancia'] ?? 0),
                    'diferencia' => floatval($row['diferencia'] ?? 0),
                    'fecha' => $row['fecha']
                ];
            }, $resumen);
        } catch (Exception $e) {
            error_log('Error en obtenerResumenVentas: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene el costo unitario de un producto por su nombre
     * @param string $nombreProducto
     * @return float
     */
    private function obtenerCostoProducto($nombreProducto) {
        $query = "SELECT costo_unitario FROM productos WHERE nombre = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$nombreProducto]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($row['costo_unitario']) ? floatval($row['costo_unitario']) : 0;
    }

    /**
     * Obtiene los detalles completos de un pedido, incluyendo productos y adicionales
     * @param int $pedidoId
     * @return array
     */
    public function getDetallesCompletos($pedidoId) {
        // Obtener productos del pedido
        $query = "SELECT dp.cantidad, pr.nombre AS producto_nombre, dp.subtotal, dp.id as detalle_id FROM detalle_pedido dp JOIN productos pr ON dp.producto_id = pr.id WHERE dp.pedido_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$pedidoId]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Obtener adicionales para cada producto
        foreach ($productos as &$prod) {
            $queryAdic = "SELECT a.nombre as adicional_nombre FROM adicionales_pedido ap JOIN adicionales a ON ap.adicional_id = a.id WHERE ap.detalle_id = ?";
            $stmtAdic = $this->conn->prepare($queryAdic);
            $stmtAdic->execute([$prod['detalle_id']]);
            $prod['adicionales'] = $stmtAdic->fetchAll(PDO::FETCH_ASSOC);
        }
        return ['detalles' => $productos];
    }

    /**
     * Obtiene las ventas agrupadas por pedido con información resumida
     * @return array
     */
    public function obtenerVentasAgrupadasPorPedido() {
        try {
            $query = "SELECT 
                lv.pedido_id,
                c.nombre as cliente_nombre,
                COUNT(lv.id) as total_productos,
                SUM(lv.cantidad) as total_cantidad,
                SUM(lv.total) as total_venta,
                SUM(lv.ganancia) as ganancia_total,
                MAX(lv.fecha) as fecha
            FROM log_ventas lv
            JOIN pedidos p ON lv.pedido_id = p.id
            JOIN clientes c ON p.cliente_id = c.id
            WHERE p.estado = 1
            GROUP BY lv.pedido_id
            ORDER BY lv.pedido_id ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    'pedido_id' => (int)$row['pedido_id'],
                    'cliente' => htmlspecialchars($row['cliente_nombre']),
                    'total_productos' => (int)$row['total_productos'],
                    'total_cantidad' => (int)$row['total_cantidad'],
                    'total_venta' => floatval($row['total_venta']),
                    'ganancia' => floatval($row['ganancia_total']),
                    'fecha' => $row['fecha']
                ];
            }, $resultados);
        } catch (Exception $e) {
            error_log('Error en obtenerVentasAgrupadasPorPedido: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los detalles de un pedido desde log_ventas
     * @param int $pedidoId
     * @return array
     */
    public function obtenerDetallesPorPedido($pedidoId) {
        try {
            $query = "SELECT 
                nombre_producto as producto,
                cantidad,
                costo_unitario,
                precio_venta_unitario,
                descuento,
                precio_adicionales,
                total,
                ganancia,
                diferencia
            FROM log_ventas
            WHERE pedido_id = ?
            ORDER BY id ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$pedidoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error en obtenerDetallesPorPedido: ' . $e->getMessage());
            return [];
        }
    }
}
