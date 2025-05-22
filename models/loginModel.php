<?php
require_once 'BaseModel.php';

class LoginModel extends BaseModel {
    public function __construct() {
        parent::__construct('usuarios');
    }

    public function crearUsuario($nombre, $usuario, $password) {
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO usuarios (nombre, usuario, password) VALUES (:nombre, :usuario, :password)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (Exception $e) {
            error_log('Error al crear usuario: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerUsuarioPorUsuario($usuario) {
        try {
            $query = "SELECT * FROM usuarios WHERE usuario = :usuario";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error al obtener usuario: ' . $e->getMessage());
            return false;
        }
    }
}
