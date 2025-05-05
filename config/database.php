<?php
class Database {
    private $ipServidor = "localhost";
    private $usuarioBase = "root";
    private $contraseña = "Derlyocampo10";
    private $nombreBaseDatos = "tortasYtortas";
    private $conexion;

    public function conectar() {
        try {
            $this->conexion = new PDO(
                "mysql:unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname={$this->nombreBaseDatos}",
                $this->usuarioBase,
                $this->contraseña,
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    
                )
            );
            return $this->conexion;
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>
