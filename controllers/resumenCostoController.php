<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
ini_set('display_errors', 1);
error_reporting(E_ALL);
// resumenCosto.php
// Controlador para manejar AJAX de resumen de ventas y totales

require_once '../models/resumenCostoModel.php';
require_once __DIR__ . '/../models/BaseModel.php';

// Instanciar el modelo de resumen
$resumenModel = new ResumenCostoModel();

// Registrar logs de entrada para depuración
error_log(print_r($_GET, true));
error_log(print_r($_POST, true));

// Detectar la acción solicitada por AJAX
try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    if ($action === 'totales') {
        header('Content-Type: application/json');
        // Obtener totales para las cards
        $totalClientes = $resumenModel->contarClientes();
        $totalPedidos = $resumenModel->contarPedidos();
        $totalVentas = $resumenModel->sumarVentas();
        
        echo json_encode([
            'clientes' => (int)$totalClientes,
            'pedidos' => (int)$totalPedidos,
            'ventas' => round((float)$totalVentas, 2)
        ]);
        exit;
    }

    if ($action === 'ventas') {
        if (headers_sent() === false) {
            header('Content-Type: application/json; charset=utf-8');
        }
        
        $ventas = $resumenModel->obtenerResumenVentas();
        
        // Los datos ya vienen formateados del modelo, así que los devolvemos directamente
        echo json_encode(['ventas' => $ventas]);
        exit;
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Función para contar clientes
function contarClientes($clienteModel) {
    $clientes = $clienteModel->getAll();
    return count($clientes);
}
// Función para contar pedidos
function contarPedidos($pedidoModel) {
    $query = "SELECT COUNT(*) as total FROM pedidos";
    $stmt = $pedidoModel->conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'] ?? 0;
}
// Función para sumar ventas
function sumarVentas($pedidoModel) {
    $query = "SELECT SUM(total) as total_ventas FROM pedidos WHERE estado = 1";
    $stmt = $pedidoModel->conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total_ventas'] ?? 0;
}
// Función para obtener el resumen de ventas usando el modelo
function obtenerResumenVentasDesdeModelo($pedidoModel) {
    try {
        $pedidos = $pedidoModel->getPedidosConCliente(1); // Solo pedidos activos
        $resumen = [];
        foreach ($pedidos as $pedido) {
            $detallePedido = $pedidoModel->getDetallesCompletos($pedido['id']);
            $productos = $detallePedido['detalles'] ?? [];
            $resumenTxt = [];
            $costoBase = 0;
            foreach ($productos as $prod) {
                $adicionales = $prod['adicionales'] ?? [];
                $adicionalesTxt = count($adicionales) > 0 ? ' + ' . implode(', ', array_column($adicionales, 'adicional_nombre')) : '';
                $resumenTxt[] = $prod['cantidad'] . 'x ' . $prod['producto_nombre'] . $adicionalesTxt;
                $costoBase += $prod['subtotal'];
            }
            $resumen[] = [
                'cliente' => htmlspecialchars($pedido['cliente_nombre']),
                'resumen' => implode(' | ', $resumenTxt),
                'costo' => floatval($pedido['total']),
                'ganancia' => floatval($pedido['total'])
            ];
        }
        return $resumen;
    } catch (Exception $e) {
        error_log('Error en obtenerResumenVentasDesdeModelo: ' . $e->getMessage());
        return [];
    }
}

class ResumenCostoModel extends BaseModel {
    public function __construct() {
        parent::__construct(); // Usa la conexión de BaseModel
    }

    public function obtenerResumenVentas() {
        try {
            $fecha = date('Y-m-d H:i:s');
            error_log("Obteniendo resumen de ventas para fecha: " . $fecha);
            
            $resumen = $this->getResumenVentas();
            return array_map(function($row) {
                // Validar y formatear valores numéricos
                $costoUnitario = max(0, floatval($row['costo_unitario'] ?? 0));
                $precioVenta = max(0, floatval($row['precio_venta_unitario'] ?? 0));
                $descuento = max(0, floatval($row['descuento'] ?? 0));
                $precioAdicionales = floatval($row['precio_adicionales'] ?? 0);
                
                // Calcular totales
                $cantidad = (int)($row['cantidad'] ?? 0);
                $subtotal = $costoUnitario * $cantidad;
                
                // Solo sumar adicionales si existen y son mayores a 0
                $totalAdicionales = $precioAdicionales > 0 ? $precioAdicionales * $cantidad : 0;
                
                $total = $subtotal + $totalAdicionales - $descuento;
                $ganancia = $total - $subtotal;

                error_log("Procesando venta - Cliente: {$row['cliente_nombre']}, Total: $total, Adicionales: $totalAdicionales");
                
                return [
                    'cliente' => htmlspecialchars($row['cliente_nombre'] ?? ''),
                    'producto' => htmlspecialchars($row['nombre_producto'] ?? ''),
                    'cantidad' => $cantidad,
                    'costo_unitario' => $costoUnitario,
                    'precio_venta_unitario' => $precioVenta,
                    'descuento' => $descuento,
                    'precio_adicionales' => $precioAdicionales,
                    'total' => $total,
                    'ganancia' => $ganancia,
                    'fecha' => $row['fecha'] ?? null
                ];
            }, $resumen);
        } catch (Exception $e) {
            error_log('Error en obtenerResumenVentas: ' . $e->getMessage());
            throw $e;
        }
    }

    // ... el resto de métodos igual, todos con try/catch ...
}

?>