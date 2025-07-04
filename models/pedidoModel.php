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
    // Ahora recibe también el descuento total y el total pagado
    public function crearPedidoCompleto($cliente_id, $productos, $adicionales, $fecha, $descuento = 0, $total_pagado = 0) {
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
                    echo json_encode([
                        'success' => false,
                        'error' => "No hay suficiente stock para el producto '{$prod['nombre']}'.",
                        'producto_id' => $id,
                        'stock_disponible' => $prod['stock'],
                        'nombre' => $prod['nombre']
                    ]);
                    exit;
                }
            }

            // =================== CALCULAR TOTAL DEL PEDIDO (productos + adicionales) ===================
            $total = 0;
            foreach (
                // Recorremos cada producto del pedido
                $productos as $producto) {
                // Obtenemos el precio de venta del producto
                $sql = "SELECT precio_venta FROM productos WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':id', $producto['id']);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $precio_unitario = $row['precio_venta'];

                // =================== SUMAR ADICIONALES ===================
                // Inicializamos el precio total de adicionales para este producto
                $precio_adicionales = 0;
                // Si hay adicionales seleccionados para este producto
                if (isset($adicionales[$producto['id']])) {
                    foreach ($adicionales[$producto['id']] as $adicional) {
                        // Obtenemos el precio de venta del adicional
                        $sqlAdic = "SELECT precio_venta FROM adicionales WHERE id = :id";
                        $stmtAdic = $this->conn->prepare($sqlAdic);
                        $stmtAdic->bindParam(':id', $adicional['id']);
                        $stmtAdic->execute();
                        $rowAdic = $stmtAdic->fetch(PDO::FETCH_ASSOC);
                        // Sumamos el precio de venta del adicional (si existe)
                        $precio_adicionales += $rowAdic ? $rowAdic['precio_venta'] : 0;
                    }
                }
                // =================== FIN SUMA ADICIONALES ===================

                // Calculamos el subtotal: (precio producto + suma adicionales) * cantidad - descuento
                $subtotal = ($precio_unitario + $precio_adicionales) * $producto['cantidad'] - ($producto['descuento'] ?? 0);
                // Sumamos al total general del pedido
                $total += $subtotal;
            }
            // =================== FIN CALCULO TOTAL ===================
            error_log("[PEDIDO] Total calculado (sin descuento): $total");

            // 3. Insertar el pedido (estado SIEMPRE 1)
            if (!$fecha || $fecha == '0') {
                $fecha = date('Y-m-d H:i:s');
            }
            // Guardar el descuento total y el total pagado
            $pedido_data = [
                'cliente_id' => $cliente_id,
                'total' => $total,
                'descuento' => $descuento,
                'total_pagado' => $total_pagado,
                'fecha' => $fecha,
                'estado' => 1
            ];
            error_log("[PEDIDO] Datos del pedido: " . print_r($pedido_data, true));

            // Ajustar el insert para incluir los nuevos campos
            $query = "INSERT INTO pedidos (cliente_id, total, descuento, total_pagado, fecha, estado)
                      VALUES (:cliente_id, :total, :descuento, :total_pagado, :fecha, :estado)";
            $stmt = $this->conn->prepare($query);
            if (!$stmt->execute($pedido_data)) {
                error_log("[PEDIDO][ERROR] Error al insertar pedido en la base de datos.");
                throw new Exception("Error al insertar pedido en la base de datos.");
            }
            $pedido_id = $this->conn->lastInsertId();
            error_log("[PEDIDO] Pedido creado con ID: $pedido_id");

            // 4. Insertar detalles y restar stock
            foreach ($productos as $producto) {
                $sql = "SELECT precio_venta FROM productos WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':id', $producto['id']);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $precio_unitario = $row['precio_venta'];

                // Calcular precio de adicionales para este producto en este pedido
                $precio_adicionales = 0;
                if (isset($adicionales[$producto['id']])) {
                    foreach ($adicionales[$producto['id']] as $adicional) {
                        $sqlAdic = "SELECT precio FROM adicionales WHERE id = :id";
                        $stmtAdic = $this->conn->prepare($sqlAdic);
                        $stmtAdic->bindParam(':id', $adicional['id']);
                        $stmtAdic->execute();
                        $rowAdic = $stmtAdic->fetch(PDO::FETCH_ASSOC);
                        $precio_adicionales += $rowAdic ? $rowAdic['precio'] : 0;
                    }
                }

                // Calcular subtotal correctamente
                $subtotal = ($precio_unitario + $precio_adicionales) * $producto['cantidad'] - ($producto['descuento'] ?? 0);

                $detalle_data = [
                    'pedido_id' => $pedido_id,
                    'producto_id' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $precio_unitario,
                    'descuento' => $producto['descuento'] ?? 0,
                    'subtotal' => $subtotal
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

                // Obtener datos del producto
                $sql = "SELECT nombre, precio_base FROM productos WHERE id = :id";
                $stmtProd = $this->conn->prepare($sql);
                $stmtProd->bindParam(':id', $producto['id']);
                $stmtProd->execute();
                $prodInfo = $stmtProd->fetch(PDO::FETCH_ASSOC);

                $nombre_producto = $prodInfo['nombre'];
                $costo_unitario = $prodInfo['precio_base'];

                // Calcular total y ganancia
                $total = ($precio_unitario * $producto['cantidad']) + $precio_adicionales - ($producto['descuento'] ?? 0);
                $ganancia = (($precio_unitario - $costo_unitario + ($producto['cantidad'] > 0 ? $precio_adicionales / $producto['cantidad'] : 0) - (($producto['descuento'] ?? 0) / ($producto['cantidad'] > 0 ? $producto['cantidad'] : 1))) * $producto['cantidad']);

                // Insertar en log_ventas
                $queryLog = "INSERT INTO log_ventas 
                    (pedido_id, producto_id, nombre_producto, cantidad, costo_unitario, precio_venta_unitario, descuento, precio_adicionales, total, ganancia, diferencia)
                    VALUES (:pedido_id, :producto_id, :nombre_producto, :cantidad, :costo_unitario, :precio_venta_unitario, :descuento, :precio_adicionales, :total, :ganancia, :diferencia)";
                $stmtLog = $this->conn->prepare($queryLog);
                $stmtLog->execute([
                    'pedido_id' => $pedido_id,
                    'producto_id' => $producto['id'],
                    'nombre_producto' => $nombre_producto,
                    'cantidad' => $producto['cantidad'],
                    'costo_unitario' => $costo_unitario,
                    'precio_venta_unitario' => $precio_unitario,
                    'descuento' => $producto['descuento'] ?? 0,
                    'precio_adicionales' => $precio_adicionales,
                    'total' => $total,
                    'ganancia' => $ganancia,
                    'diferencia' => $total - $total_pagado
                ]);

                // =============================
                // NUEVO: Insertar adicionales seleccionados en la tabla pivote pedido_adicionales
                if (isset($adicionales[$producto['id']])) {
                    error_log('[PEDIDO][ADICIONALES] Para producto ' . $producto['id'] . ': ' . print_r($adicionales[$producto['id']], true));
                    foreach ($adicionales[$producto['id']] as $adicional) {
                        $adicional_data = [
                            'pedido_producto_id' => $detalle_id,
                            'adicional_id' => $adicional['id']
                        ];
                        error_log('[PEDIDO][ADICIONAL][INSERT] ' . print_r($adicional_data, true));
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
    public function actualizarPedidoCompleto($pedido_id, $cliente_id, $productos, $adicionales, $estado, $fecha, $total, $total_pagado = 0) {
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
            $query = "UPDATE pedidos SET cliente_id = :cliente_id, estado = :estado, total = :total, total_pagado = :total_pagado, fecha = :fecha WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'cliente_id' => $cliente_id,
                'estado' => $estado,
                'total' => $total,
                'total_pagado' => $total_pagado,
                'fecha' => $fecha,
                'id' => $pedido_id
            ]);

            // Obtener los IDs de los detalles actuales
            $stmt = $this->conn->prepare("SELECT id, producto_id, cantidad FROM pedido_productos WHERE pedido_id = :pedido_id");
            $stmt->execute(['pedido_id' => $pedido_id]);
            $detalles_anteriores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 1. Devolver el stock de los productos anteriores
            foreach ($detalles_anteriores as $detalle) {
                $this->conn->prepare("UPDATE productos SET stock = stock + :cantidad WHERE id = :id")
                    ->execute(['cantidad' => $detalle['cantidad'], 'id' => $detalle['producto_id']]);
                // Eliminar adicionales
                $this->conn->prepare("DELETE FROM pedido_adicionales WHERE pedido_producto_id = :id")->execute(['id' => $detalle['id']]);
            }
            $this->conn->prepare("DELETE FROM pedido_productos WHERE pedido_id = :pedido_id")->execute(['pedido_id' => $pedido_id]);

            // 2. Insertar nuevos productos y adicionales, y restar stock
            foreach ($productos as $producto) {
                $sql = "SELECT precio_venta FROM productos WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':id', $producto['id']);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $precio_unitario = $row['precio_venta'];

                // Calcular precio de adicionales para este producto en este pedido
                $precio_adicionales = 0;
                if (isset($adicionales[$producto['id']])) {
                    foreach ($adicionales[$producto['id']] as $adicional) {
                        $sqlAdic = "SELECT precio FROM adicionales WHERE id = :id";
                        $stmtAdic = $this->conn->prepare($sqlAdic);
                        $stmtAdic->bindParam(':id', $adicional['id']);
                        $stmtAdic->execute();
                        $rowAdic = $stmtAdic->fetch(PDO::FETCH_ASSOC);
                        $precio_adicionales += $rowAdic ? $rowAdic['precio'] : 0;
                    }
                }

                // Calcular subtotal correctamente
                $subtotal = ($precio_unitario + $precio_adicionales) * $producto['cantidad'] - ($producto['descuento'] ?? 0);

                $detalle_data = [
                    'pedido_id' => $pedido_id,
                    'producto_id' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $precio_unitario,
                    'descuento' => $producto['descuento'],
                    'subtotal' => $subtotal
                ];
                $query = "INSERT INTO pedido_productos (pedido_id, producto_id, cantidad, precio_unitario, descuento, subtotal)
                          VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario, :descuento, :subtotal)";
                $stmt = $this->conn->prepare($query);
                $stmt->execute($detalle_data);
                $detalle_id = $this->conn->lastInsertId();
                // Restar stock
                $this->conn->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :id")
                    ->execute(['cantidad' => $producto['cantidad'], 'id' => $producto['id']]);
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