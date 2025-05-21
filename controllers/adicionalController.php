<?php
require_once '../models/adicionalModel.php';

$adicionalModel = new AdicionalModel();

$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Crear adicional
if ($action === 'crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['error' => 'Datos no recibidos o formato incorrecto']);
        exit;
    }
    $nombre = $input['nombre'] ?? null;
    $precio = $input['precio'] ?? null;
    $precio_venta = $input['precio_venta'] ?? null;
    $stock = $input['stock'] ?? 0;
    $producto_id = $input['producto_id'] ?? 1;

    if (!$nombre || $precio === null || $precio_venta === null) {
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        exit;
    }
    if (!is_numeric($precio) || $precio <= 0 || !is_numeric($precio_venta) || $precio_venta <= 0) {
        echo json_encode(['error' => 'El precio base y el precio de venta deben ser números positivos']);
        exit;
    }
    try {
        $adicional_id = $adicionalModel->crearAdicional($producto_id, $nombre, $precio, $precio_venta, $stock);
        if ($adicional_id) {
            echo json_encode(['success' => true, 'adicional_id' => $adicional_id]);
        } else {
            echo json_encode(['error' => 'No se pudo crear el adicional']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Obtener todos los adicionales
if ($action === 'obtener' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $adicionales = $adicionalModel->obtenerAdicionales();
        echo json_encode(['success' => true, 'adicionales' => $adicionales]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Obtener adicional por ID
if ($action === 'obtenerPorId' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['error' => 'ID no proporcionado']);
        exit;
    }
    try {
        $adicional = $adicionalModel->obtenerAdicionalPorId($id);
        if ($adicional) {
            echo json_encode(['success' => true, 'adicional' => $adicional]);
        } else {
            echo json_encode(['error' => 'Adicional no encontrado']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Actualizar adicional
if ($action === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['error' => 'Datos no recibidos o formato incorrecto']);
        exit;
    }
    $id = $input['id'] ?? null;
    $nombre = $input['nombre'] ?? null;
    $precio = $input['precio'] ?? null;
    $precio_venta = $input['precio_venta'] ?? null;
    $stock = $input['stock'] ?? 0;
    $producto_id = $input['producto_id'] ?? 1;

    if (!$id || !$nombre || $precio === null || $precio_venta === null) {
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        exit;
    }
    try {
        $resultado = $adicionalModel->actualizarAdicional($id, $producto_id, $nombre, $precio, $precio_venta, $stock);
        if ($resultado) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'No se pudo actualizar el adicional']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Eliminar adicional
if ($action === 'eliminar' && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    if (!$id) {
        echo json_encode(['error' => 'ID no proporcionado']);
        exit;
    }
    try {
        $resultado = $adicionalModel->eliminarAdicional($id);
        if ($resultado) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'No se pudo eliminar el adicional']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
