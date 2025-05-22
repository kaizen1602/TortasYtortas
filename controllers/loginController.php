<?php
// Habilita la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluye el modelo de login
require_once '../models/loginModel.php';
$loginModel = new LoginModel();

// Verifica si se recibe una acción por parámetro GET
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    // Acción para registrar administrador
    if ($action === 'registrarAdmin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtiene los datos enviados en formato JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $nombre = $input['nombre'] ?? null;
        $usuario = $input['usuario'] ?? null;
        $password = $input['password'] ?? null;

        // Validaciones en el backend
        if (!$nombre || !$usuario || !$password) {
            echo json_encode(['error' => 'Faltan datos obligatorios']);
            exit;
        }

        // Validar nombre (solo letras, espacios y acentos)
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/', $nombre)) {
            echo json_encode(['error' => 'El nombre solo debe contener letras y espacios (2-50 caracteres)']);
            exit;
        }

        // Validar email
        if (!filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'El email no es válido']);
            exit;
        }

        // Validar contraseña (mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número)
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/', $password)) {
            echo json_encode(['error' => 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número']);
            exit;
        }

        // Verifica si el usuario ya existe
        $existe = $loginModel->obtenerUsuarioPorUsuario($usuario);
        if (!$existe) {
            // Crea el usuario administrador
            $id = $loginModel->crearUsuario($nombre, $usuario, $password);
            if ($id) {
                echo json_encode(['success' => true, 'id' => $id]);
            } else {
                echo json_encode(['error' => 'No se pudo crear el usuario']);
            }
        } else {
            echo json_encode(['error' => 'El usuario ya existe']);
        }
        exit;
    }
    // Acción para login
    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtiene los datos enviados en formato JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $usuario = $input['usuario'] ?? null;
        $password = $input['password'] ?? null;
        // Verifica que ambos campos estén presentes
        if ($usuario && $password) {
            // Busca el usuario en la base de datos
            $user = $loginModel->obtenerUsuarioPorUsuario($usuario);
            // Verifica que el usuario exista y la contraseña sea correcta
            if ($user && password_verify($password, $user['password'])) {
                // Inicia sesión y guarda datos en $_SESSION
                session_start();
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['id'] = $user['id'];
                // Redirige a la vista de pedidos (gestorpedido.php)
                echo json_encode(['success' => true, 'redirect' => 'http://localhost/TORTASYTORTAS/views/gestorpedido.php']);
            } else {
                // Usuario o contraseña incorrectos
                echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
            }
        } else {
            // Faltan datos
            echo json_encode(['error' => 'Faltan datos']);
        }
        exit;
    }
}
