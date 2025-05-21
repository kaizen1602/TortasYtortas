<?php
require_once '../models/PedidoModel.php';  
require_once '../models/ClienteModel.php';
require_once '../models/ProductoModel.php';
require_once '../models/AdicionalModel.php';

$pedidoModel = new PedidoModel();
$clienteModel = new ClienteModel();
$productoModel = new ProductoModel();
$adicionalModel = new AdicionalModel();

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'getPedidos':
            try {
                $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
                $pedidos = $pedidoModel->getPedidosConCliente($estado);
                echo json_encode($pedidos ?: []);
            } catch (Exception $e) {
                error_log("Error en getPedidos: " . $e->getMessage());
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        case 'getDetallesPedido':
            if (isset($_GET['pedido_id'])) {
                try {
                    $pedido_id = $_GET['pedido_id'];
                    $detalles = $pedidoModel->getDetallesCompletos($pedido_id);
                    echo json_encode($detalles ?: []);
                } catch (Exception $e) {
                    error_log("Error en getDetallesPedido: " . $e->getMessage());
                    echo json_encode(['error' => $e->getMessage()]);
                }
            }
            break;

        case 'getClientes':
            try {
                $clientes = $clienteModel->getAll();
                echo json_encode($clientes);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        case 'getProductos':
            try {
                $productos = $productoModel->getAll();
                echo json_encode($productos);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        case 'getAdicionales':
            try {
                $adicionales = $adicionalModel->getAll();
                echo json_encode($adicionales);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        case 'crearPedidoCompleto':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                error_log('DEBUG CREAR PEDIDO: ' . print_r($input, true));
                if (!$input) {
                    echo json_encode(['error' => 'Datos no recibidos o formato incorrecto']);
                    exit;
                }

                $cliente_id = $input['cliente_id'] ?? null;
                $productos = $input['productos'] ?? [];
                $adicionales = $input['adicionales'] ?? [];
                $fecha = $input['fecha'] ?? date('Y-m-d H:i:s');
                $descuento = $input['descuento'] ?? 0;
                $total_pagado = $input['total_pagado'] ?? 0;
                
                if (!$cliente_id || empty($productos)) {
                    echo json_encode(['error' => 'Faltan datos obligatorios']);
                    exit;
                }

                try {
                    $pedido_id = $pedidoModel->crearPedidoCompleto($cliente_id, $productos, $adicionales, $fecha, $descuento, $total_pagado);
                    if (is_array($pedido_id) && isset($pedido_id['success']) && $pedido_id['success'] === false) {
                        // Error específico, como stock insuficiente
                        error_log('Error de stock: ' . print_r($pedido_id, true));
                        echo json_encode($pedido_id);
                        exit;
                    } else if ($pedido_id) {
                        echo json_encode(['success' => true, 'pedido_id' => $pedido_id]);
                    } else {
                        echo json_encode(['error' => 'No se pudo crear el pedido']);
                    }
                } catch (Exception $e) {
                    error_log('Error al crear pedido: ' . $e->getMessage());
                    echo json_encode(['error' => $e->getMessage()]);
                }
            }
            break;

        case 'actualizarPedidoCompleto':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                if (!$input) {
                    echo json_encode(['error' => 'Datos no recibidos o formato incorrecto']);
                    exit;
                }

                $pedido_id = $input['pedido_id'] ?? null;
                $cliente_id = $input['cliente_id'] ?? null;
                $productos = $input['productos'] ?? [];
                $adicionales = $input['adicionales'] ?? [];
                $estado = $input['estado'] ?? 1;
                $fecha = $input['fecha'] ?? date('Y-m-d H:i:s');
                $total = $input['total'] ?? 0;
                $total_pagado = $input['total_pagado'] ?? 0;

                if (!$pedido_id || !$cliente_id || empty($productos)) {
                    echo json_encode(['error' => 'Faltan datos obligatorios']);
                    exit;
                }

                try {
                    $resultado = $pedidoModel->actualizarPedidoCompleto($pedido_id, $cliente_id, $productos, $adicionales, $estado, $fecha, $total, $total_pagado);
                    if (is_array($resultado) && isset($resultado['success']) && $resultado['success'] === false) {
                        echo json_encode($resultado);
                        exit;
                    }
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
            }
            break;

        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
}
?>
