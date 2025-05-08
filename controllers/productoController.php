<?php
require_once '../models/ProductoModel.php';

$productoModel = new ProductoModel();

if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

// Crear nuevo producto
if (
    $action === 'crear' && $_SERVER['REQUEST_METHOD'] === 'POST'
) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['error' => 'Datos no recibidos o formato incorrecto']);
        exit;
    }

    $nombre = $input['nombre'] ?? null;
    $precio_base = $input['precio_base'] ?? null;
    $descuento = $input['descuento'] ?? 0;
    $stock = $input['stock'] ?? 0;
    // Estado por defecto: 1 (activo)
    $estado = isset($input['estado']) ? $input['estado'] : 1;

    if (!$nombre || $precio_base === null) {
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        exit;
    }

    try {
        $producto_id = $productoModel->crearProducto($nombre, $precio_base, $descuento, $stock, $estado);
        if ($producto_id) {
            echo json_encode(['success' => true, 'producto_id' => $producto_id]);
        } else {
            echo json_encode(['error' => 'No se pudo crear el producto']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Obtener todos los productos
if ($action === 'obtener' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $productos = $productoModel->obtenerProductos();
        echo json_encode(['success' => true, 'productos' => $productos]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Obtener producto por ID
if ($action === 'obtenerPorId' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['error' => 'ID no proporcionado']);
        exit;
    }
    
    try {
        $producto = $productoModel->obtenerProductoPorId($id);
        if ($producto) {
            echo json_encode(['success' => true, 'producto' => $producto]);
        } else {
            echo json_encode(['error' => 'Producto no encontrado']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Actualizar producto
if ($action === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['error' => 'Datos no recibidos o formato incorrecto']);
        exit;
    }

    $id = $input['id'] ?? null;
    $nombre = $input['nombre'] ?? null;
    $precio_base = $input['precio_base'] ?? null;
    $descuento = $input['descuento'] ?? 0;
    $stock = $input['stock'] ?? 0;
    $estado = isset($input['estado']) ? $input['estado'] : 1;

    if (!$id || !$nombre || $precio_base === null) {
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        exit;
    }

    try {
        $resultado = $productoModel->actualizarProducto($id, $nombre, $precio_base, $descuento, $stock, $estado);
        if ($resultado) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'No se pudo actualizar el producto']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Cambiar estado del producto
if ($action === 'cambiarEstado' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['error' => 'Datos no recibidos o formato incorrecto']);
        exit;
    }

    $id = $input['id'] ?? null;
    $estado = $input['estado'] ?? null;

    if (!$id || $estado === null) {
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        exit;
    }

    try {
        $resultado = $productoModel->cambiarEstadoProducto($id, $estado);
        if ($resultado) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'No se pudo cambiar el estado del producto']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Buscar productos
if ($action === 'buscar' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $termino = $_GET['termino'] ?? '';
    
    try {
        $productos = $productoModel->buscarProductos($termino);
        echo json_encode(['success' => true, 'productos' => $productos]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Obtener adicionales de un producto
if ($action === 'obtenerAdicionales' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $producto_id = $_GET['producto_id'] ?? null;
    
    if (!$producto_id) {
        echo json_encode(['error' => 'ID de producto no proporcionado']);
        exit;
    }
    
    try {
        $adicionales = $productoModel->obtenerAdicionalesPorProducto($producto_id);
        echo json_encode(['success' => true, 'adicionales' => $adicionales]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Agregar adicional a producto
if ($action === 'agregarAdicional' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['error' => 'Datos no recibidos o formato incorrecto']);
        exit;
    }

    $producto_id = $input['producto_id'] ?? null;
    $adicional_id = $input['adicional_id'] ?? null;

    if (!$producto_id || !$adicional_id) {
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        exit;
    }

    try {
        $resultado = $productoModel->agregarAdicionalAProducto($producto_id, $adicional_id);
        if ($resultado) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'No se pudo agregar el adicional al producto']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Eliminar adicional de producto
if ($action === 'eliminarAdicional' && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['error' => 'Datos no recibidos o formato incorrecto']);
        exit;
    }

    $producto_id = $input['producto_id'] ?? null;
    $adicional_id = $input['adicional_id'] ?? null;

    if (!$producto_id || !$adicional_id) {
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        exit;
    }

    try {
        $resultado = $productoModel->eliminarAdicionalDeProducto($producto_id, $adicional_id);
        if ($resultado) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'No se pudo eliminar el adicional del producto']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
