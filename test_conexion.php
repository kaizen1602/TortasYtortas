<?php
require_once 'models/loginModel.php';

try {
    $loginModel = new LoginModel();
    
    // Verificar si la tabla existe
    $query = "SHOW TABLES LIKE 'usuarios'";
    $stmt = $loginModel->conn->prepare($query);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Crear la tabla si no existe
        $query = "CREATE TABLE usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            usuario VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $loginModel->conn->exec($query);
        echo "Tabla usuarios creada correctamente.<br>";
    }
    
    // Intentar insertar el usuario administrador
    $nombre = "Kevin david ocampo";
    $usuario = "kevin@gmail.com";
    $password = "12345";
    
    $resultado = $loginModel->crearUsuario($nombre, $usuario, $password);
    
    if ($resultado) {
        echo "Usuario administrador creado correctamente.<br>";
    } else {
        echo "Error al crear el usuario administrador.<br>";
    }
    
    // Verificar si el usuario se insertó
    $usuarioCreado = $loginModel->obtenerUsuarioPorUsuario($usuario);
    if ($usuarioCreado) {
        echo "Usuario encontrado en la base de datos:<br>";
        print_r($usuarioCreado);
    } else {
        echo "No se encontró el usuario en la base de datos.<br>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} 