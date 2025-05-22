<?php
// Cerrar sesión y redirigir al login
session_start();
session_unset(); // Elimina todas las variables de sesión
session_destroy(); // Destruye la sesión
header('Location: http://localhost/TORTASYTORTAS/views/login.php');
exit(); 