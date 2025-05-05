<?php
require_once 'BaseModel.php';

class PedidoModel extends BaseModel {
    public function __construct() {
        parent::__construct('pedidos');
    }

    // Obtener pedidos con detalles del cliente
    public function getPedidosConCliente($estado = null) {
        $query = "SELECT p.*, c.nombre as cliente_nombre, c.cedula, c.direccion, 
                  CASE WHEN p.estado = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado
                  FROM pedidos p
                  JOIN clientes c ON p.cliente_id = c.id";
        
        if ($estado) {
            $query .= " WHERE p.estado = :estado";
        }
        
        $query .= " ORDER BY p.fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        if ($estado) {
            $stmt->bindParam(":estado", $estado);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    // Obtener detalles completos de un pedido
    public function getDetallesCompletos($pedido_id) {
        try {
            // Info básica del pedido y cliente
            $sql = "SELECT p.*, c.nombre AS cliente_nombre, c.cedula, c.direccion
                    FROM pedidos p
                    JOIN clientes c ON p.cliente_id = c.id
                    WHERE p.id = :pedido_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
            $stmt->execute();
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) return null;

            // Detalles de productos del pedido
            $sql = "SELECT pp.*, pr.nombre AS producto_nombre
                    FROM pedido_productos pp
                    JOIN productos pr ON pp.producto_id = pr.id
                    WHERE pp.pedido_id = :pedido_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
            $stmt->execute();
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Para cada producto, buscar sus adicionales
            foreach ($detalles as &$detalle) {
                $sql = "SELECT pa.*, a.nombre AS adicional_nombre, a.precio
                        FROM pedido_adicionales pa
                        JOIN adicionales a ON pa.adicional_id = a.id
                        WHERE pa.pedido_producto_id = :detalle_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':detalle_id', $detalle['id'], PDO::PARAM_INT);
                $stmt->execute();
                $detalle['adicionales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $pedido['detalles'] = $detalles;
            return $pedido;
        } catch (Exception $e) {
            error_log("Error en getDetallesCompletos: " . $e->getMessage());
            throw $e;
        }
    }
    

    // Crear un nuevo pedido con sus detalles
    public function crearPedidoCompleto($cliente_id, $productos, $adicionales, $descuento = 0) {
        try {
            // Iniciar transacción
            $this->conn->beginTransaction();

            // Calcular total del pedido
            $total = 0;
            foreach ($productos as $producto) {
                $subtotal = $producto['precio_unitario'] * $producto['cantidad'] - ($producto['descuento'] ?? 0);
                $total += $subtotal;
            }

            // Datos del pedido
            $pedido_data = [
                'cliente_id' => $cliente_id,
                'total' => $total - $descuento,  // Aplicar descuento si es necesario
                'fecha' => date('Y-m-d H:i:s')
            ];

            // Verificar que los datos del pedido sean correctos
            error_log("Datos para crear pedido: " . print_r($pedido_data, true));

            // Crear el pedido en la base de datos
            if (!$this->create($pedido_data)) {
                throw new Exception("Error al insertar pedido en la base de datos.");
            }
            $pedido_id = $this->conn->lastInsertId(); // Obtener ID del nuevo pedido

            // Verificar que el pedido_id se haya generado
            error_log("Pedido creado con ID: $pedido_id");

            // Insertar detalles de productos en la tabla pedido_productos
            foreach ($productos as $producto) {
                $detalle_data = [
                    'pedido_id' => $pedido_id,
                    'producto_id' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'],
                    'descuento' => $producto['descuento'] ?? 0,
                    'subtotal' => ($producto['precio_unitario'] * $producto['cantidad']) - ($producto['descuento'] ?? 0)
                ];

                // Verificar los datos de detalle antes de ejecutar
                error_log("Datos para insertar en pedido_productos: " . print_r($detalle_data, true));

                $query = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad, precio_unitario, descuento, subtotal)
                          VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario, :descuento, :subtotal)";
                $stmt = $this->conn->prepare($query);

                if (!$stmt->execute($detalle_data)) {
                    $error = $stmt->errorInfo();
                    error_log("Error al insertar detalle de pedido: " . print_r($error, true));
                    throw new Exception("Error al insertar detalle de pedido.");
                }

                $detalle_id = $this->conn->lastInsertId(); // Obtener ID del detalle insertado

                // Verificar que se haya insertado el detalle correctamente
                error_log("Detalle insertado con ID: $detalle_id");

                // Insertar adicionales si existen
                if (isset($adicionales[$producto['id']])) {
                    foreach ($adicionales[$producto['id']] as $adicional) {
                        $adicional_data = [
                            'pedido_producto_id' => $detalle_id,
                            'adicional_id' => $adicional['id']
                        ];

                        // Verificar los datos de adicional antes de ejecutar
                        error_log("Datos para insertar en pedido_adicionales: " . print_r($adicional_data, true));

                        $query = "INSERT INTO pedido_adicionales (pedido_producto_id, adicional_id)
                                  VALUES (:pedido_producto_id, :adicional_id)";
                        $stmt = $this->conn->prepare($query);

                        if (!$stmt->execute($adicional_data)) {
                            $error = $stmt->errorInfo();
                            error_log("Error al insertar adicional: " . print_r($error, true));
                            throw new Exception("Error al insertar adicional.");
                        }
                    }
                }
            }

            // Confirmar transacción
            $this->conn->commit();
            return $pedido_id; // Retorna el ID del pedido creado

        } catch (Exception $e) {
            // Si ocurre algún error, revertir la transacción
            $this->conn->rollBack();
            error_log("Error al crear pedido completo: " . $e->getMessage());  // Registrar el error
            throw $e;  // Lanzar nuevamente la excepción para manejarla externamente
        }
    }
    
    

    // Actualizar estado del pedido
    public function actualizarEstado($pedido_id, $estado) {
        $query = "UPDATE pedidos SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id", $pedido_id);
        return $stmt->execute();
    }

    // Actualizar un pedido completo (pedido, productos y adicionales)
    public function actualizarPedidoCompleto($pedido_id, $cliente_id, $productos, $adicionales, $estado, $fecha, $total) {
        try {
            $this->conn->beginTransaction();
            // Actualizar datos del pedido
            $query = "UPDATE pedidos SET cliente_id = :cliente_id, estado = :estado, total = :total, fecha = :fecha WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'cliente_id' => $cliente_id,
                'estado' => $estado,
                'total' => $total,
                'fecha' => $fecha,
                'id' => $pedido_id
            ]);
            // Eliminar productos y adicionales actuales
            $stmt = $this->conn->prepare("SELECT id FROM pedido_productos WHERE pedido_id = :pedido_id");
            $stmt->execute(['pedido_id' => $pedido_id]);
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($detalles as $detalle) {
                $this->conn->prepare("DELETE FROM pedido_adicionales WHERE pedido_producto_id = :id")->execute(['id' => $detalle['id']]);
            }
            $this->conn->prepare("DELETE FROM pedido_productos WHERE pedido_id = :pedido_id")->execute(['pedido_id' => $pedido_id]);
            // Insertar nuevos productos y adicionales
            foreach ($productos as $producto) {
                $detalle_data = [
                    'pedido_id' => $pedido_id,
                    'producto_id' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'],
                    'descuento' => $producto['descuento'],
                    'subtotal' => ($producto['precio_unitario'] * $producto['cantidad']) - $producto['descuento']
                ];
                $query = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad, precio_unitario, descuento, subtotal)
                          VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario, :descuento, :subtotal)";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($detalle_data);
                $detalle_id = $this->conn->lastInsertId();
                // Agregar adicionales si existen
                if (isset($adicionales[$producto['id']])) {
                    foreach ($adicionales[$producto['id']] as $adicional) {
                        $adicional_data = [
                            'pedido_producto_id' => $detalle_id,
                            'adicional_id' => $adicional['id']
                        ];
                        $query = "INSERT INTO pedido_adicionales (pedido_producto_id, adicional_id)
                                  VALUES (:pedido_producto_id, :adicional_id)";
                        $stmt = $this->conn->prepare($query);
                        $stmt->execute($adicional_data);
                    }
                }
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}

?> 