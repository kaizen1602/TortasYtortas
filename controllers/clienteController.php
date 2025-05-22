<?php
require_once '../models/ClienteModel.php';

$clienteModel = new ClienteModel();

if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

// Crear nuevo cliente
if ($action === 'crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['error' => 'Datos no recibidos o formato incorrecto']);
        exit;
    }

    $nombre = $input['nombre'] ?? null;
    $cedula = $input['cedula'] ?? null;
    $direccion = $input['direccion'] ?? null;
    $telefono = $input['telefono'] ?? null;

    if (!$nombre || !$cedula || !$direccion || !$telefono) {
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        exit;
    }

    try {
        $cliente_id = $clienteModel->crearCliente($nombre, $cedula, $direccion, $telefono);
        if ($cliente_id) {
            echo json_encode(['success' => true, 'cliente_id' => $cliente_id]);
        } else {
            echo json_encode(['error' => 'No se pudo crear el cliente']);
        }
    } catch (Exception $e) {
        // Si el error es por cédula duplicada, devolvemos un mensaje personalizado
        if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'cedula') !== false) {
            echo json_encode(['error' => 'La cédula ya está registrada']);
        } else {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    exit;
}

// Obtener todos los clientes
if ($action === 'obtener' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    $estado = $_GET['estado'] ?? null;
    try {
        $clientes = $clienteModel->obtenerClientes($estado);
        echo json_encode(['clientes' => $clientes]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Obtener cliente por ID
if ($action === 'obtenerPorId' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['error' => 'ID no proporcionado']);
        exit;
    }
    
    try {
        $cliente = $clienteModel->obtenerClientePorId($id);
        if ($cliente) {
            echo json_encode(['success' => true, 'cliente' => $cliente]);
        } else {
            echo json_encode(['error' => 'Cliente no encontrado']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Actualizar cliente
if ($action === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['error' => 'Datos no recibidos o formato incorrecto']);
        exit;
    }

    $id = $input['id'] ?? null;
    $nombre = $input['nombre'] ?? null;
    $cedula = $input['cedula'] ?? null;
    $direccion = $input['direccion'] ?? null;
    $telefono = $input['telefono'] ?? null;

    if (!$id || !$nombre || !$cedula || !$direccion || !$telefono) {
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        exit;
    }

    try {
        $resultado = $clienteModel->actualizarCliente($id, $nombre, $cedula, $direccion, $telefono);
        if ($resultado) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'No se pudo actualizar el cliente']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Cambiar estado del cliente
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
        $resultado = $clienteModel->cambiarEstadoCliente($id, $estado);
        if ($resultado) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'No se pudo cambiar el estado del cliente']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Buscar clientes
if ($action === 'buscar' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $termino = $_GET['termino'] ?? '';
    
    try {
        $clientes = $clienteModel->buscarClientes($termino);
        echo json_encode(['success' => true, 'clientes' => $clientes]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}