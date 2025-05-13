<?php
require_once 'BaseModel.php';

class PedidoModel extends BaseModel {
    public function __construct() {
        parent::__construct('pedidos');
    }

    //Obtenemos los detalles del pedido, recibiendo el parametro de estado para poder filtrar
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
            $sql = "SELECT p.*, c.nombre AS cliente_nombre,c.telefono, c.cedula, c.direccion
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
    public function crearPedidoCompleto($cliente_id, $productos, $adicionales, $descuento = 0,$fecha) {
        try {
            error_log("[PEDIDO] Iniciando creación de pedido");
            $this->conn->beginTransaction();

            // 1. Validar stock de cada producto
            foreach ($productos as $producto) {
                $id = (int)$producto['id'];
                $cantidad = (int)$producto['cantidad'];
                error_log("[PEDIDO] Validando stock para producto ID: " . $id . ", cantidad: " . $cantidad);
                $sql = "SELECT stock, nombre FROM productos WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $prod = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$prod) {
                    $this->conn->rollBack();
                    return [
                        'success' => false,
                        'error' => "Producto no encontrado.",
                        'producto_id' => $id
                    ];
                }

                if ($cantidad > $prod['stock']) {
                    $this->conn->rollBack();
                    return [
                        'success' => false,
                        'error' => "No hay suficiente stock para el producto '{$prod['nombre']}'.",
                        'producto_id' => $id,
                        'stock_disponible' => $prod['stock'],
                        'nombre' => $prod['nombre']
                    ];
                }
            }

            // 2. Calcular total del pedido
            $total = 0;
            foreach ($productos as $producto) {
                $subtotal = $producto['precio_unitario'] * $producto['cantidad'] - ($producto['descuento'] ?? 0);
                $total += $subtotal;
            }
            error_log("[PEDIDO] Total calculado: $total");

            // 3. Insertar el pedido (estado SIEMPRE 1)
            $pedido_data = [
                'cliente_id' => $cliente_id,
                'total' => $total - $descuento,
                'fecha' => $fecha,
                'estado' => 1
            ];
            error_log("[PEDIDO] Datos del pedido: " . print_r($pedido_data, true));

            if (!$this->create($pedido_data)) {
                error_log("[PEDIDO][ERROR] Error al insertar pedido en la base de datos.");
                throw new Exception("Error al insertar pedido en la base de datos.");
            }
            $pedido_id = $this->conn->lastInsertId();
            error_log("[PEDIDO] Pedido creado con ID: $pedido_id");

            // 4. Insertar detalles y restar stock
            foreach ($productos as $producto) {
                $detalle_data = [
                    'pedido_id' => $pedido_id,
                    'producto_id' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'],
                    'descuento' => $producto['descuento'] ?? 0,
                    'subtotal' => ($producto['precio_unitario'] * $producto['cantidad']) - ($producto['descuento'] ?? 0)
                ];
                error_log("[PEDIDO] Insertando detalle: " . print_r($detalle_data, true));

                $query = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad, precio_unitario, descuento, subtotal)
                          VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario, :descuento, :subtotal)";
                $stmt = $this->conn->prepare($query);
                if (!$stmt->execute($detalle_data)) {
                    $error = $stmt->errorInfo();
                    error_log("[PEDIDO][ERROR] Error al insertar detalle de pedido: " . print_r($error, true));
                    throw new Exception("Error al insertar detalle de pedido: " . print_r($error, true));
                }

                // Obtener el ID del detalle insertado
                $detalle_id = $this->conn->lastInsertId();

                // Restar stock
                $query = "UPDATE productos SET stock = stock - :cantidad WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':cantidad', $producto['cantidad']);
                $stmt->bindParam(':id', $producto['id']);
                $stmt->execute();
                error_log("[PEDIDO] Stock actualizado para producto ID: " . $producto['id']);

                // =============================
                // NUEVO: Insertar adicionales seleccionados en la tabla pivote pedido_adicionales
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
                // =============================
            }

            $this->conn->commit();
            error_log("[PEDIDO] Pedido creado y stock actualizado correctamente");
            return $pedido_id;

        } catch (Exception $e) {
            // Solo hacer rollback si la transacción sigue activa
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("[PEDIDO][ERROR][EXCEPTION] " . $e->getMessage());
            throw $e;
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
            
            // 1. Validar stock de cada producto considerando el stock actual y lo que ya estaba reservado en este pedido
            foreach ($productos as $producto) {
                // Obtener el stock actual del producto
                $sql = "SELECT stock, nombre FROM productos WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':id', $producto['id']);
                $stmt->execute();
                $prod = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$prod) {
                    $this->conn->rollBack();
                    return [
                        'success' => false,
                        'error' => "Producto no encontrado (ID: {$producto['id']})",
                        'producto_id' => $producto['id']
                    ];
                }

                // Obtener la cantidad que ya estaba reservada en este pedido para este producto
                $sql = "SELECT cantidad FROM pedido_productos WHERE pedido_id = :pedido_id AND producto_id = :producto_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':pedido_id', $pedido_id);
                $stmt->bindParam(':producto_id', $producto['id']);
                $stmt->execute();
                $detalle_anterior = $stmt->fetch(PDO::FETCH_ASSOC);
                $cantidad_anterior = $detalle_anterior ? (int)$detalle_anterior['cantidad'] : 0;

                // Calcular el stock disponible real sumando lo que estaba reservado antes
                $stock_disponible = $prod['stock'] + $cantidad_anterior;

                // Si la nueva cantidad supera el stock disponible, retornar error
                if ($producto['cantidad'] > $stock_disponible) {
                    $this->conn->rollBack();
                    return [
                        'success' => false,
                        'error' => "No hay suficiente stock para el producto '{$prod['nombre']}'. Solicitado: {$producto['cantidad']}, Disponible: $stock_disponible",
                        'producto_id' => $producto['id'],
                        'stock_disponible' => $stock_disponible,
                        'nombre' => $prod['nombre']
                    ];
                }
            }

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
            $stmt = $this->conn->prepare("SELECT producto_id, cantidad FROM pedido_productos WHERE pedido_id = :pedido_id");
            $stmt->execute(['pedido_id' => $pedido_id]);
            $detalles_anteriores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Guardar las cantidades anteriores por producto
            $cantidades_anteriores = [];
            foreach ($detalles_anteriores as $detalle) {
                $cantidades_anteriores[$detalle['producto_id']] = (int)$detalle['cantidad'];
            }
            // Eliminar adicionales y productos antiguos
            foreach ($detalles_anteriores as $detalle) {
                $this->conn->prepare("DELETE FROM pedido_adicionales WHERE pedido_producto_id = :id")->execute(['id' => $detalle['id']]);
            }
            $this->conn->prepare("DELETE FROM pedido_productos WHERE pedido_id = :pedido_id")->execute(['pedido_id' => $pedido_id]);

            // --- ACTUALIZAR STOCK DE CADA PRODUCTO ---
            foreach ($productos as $producto) {
                $id_producto = $producto['id'];
                $cantidad_nueva = (int)$producto['cantidad'];
                $cantidad_anterior = isset($cantidades_anteriores[$id_producto]) ? $cantidades_anteriores[$id_producto] : 0;
                // La diferencia es lo que realmente se debe restar del stock
                $diferencia = $cantidad_nueva - $cantidad_anterior;
                // Si la diferencia es positiva, se resta del stock; si es negativa, se suma (devolución)
                $sql = "UPDATE productos SET stock = stock - :diferencia WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':diferencia', $diferencia);
                $stmt->bindParam(':id', $id_producto);
                $stmt->execute();
            }
            // --- FIN ACTUALIZAR STOCK ---

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
            return ['success' => true];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }
}

?> 