<?php
// Iniciar sesión y proteger acceso solo para el usuario con ID 1 (administrador)
session_start();

// Depuración
error_log('Valores de sesión:');
error_log('$_SESSION[usuario]: ' . (isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'no definido'));
error_log('$_SESSION[id]: ' . (isset($_SESSION['id']) ? $_SESSION['id'] : 'no definido'));

if (!isset($_SESSION['usuario']) || !isset($_SESSION['id']) || $_SESSION['id'] != 1) {
    error_log('Redireccionando: No cumple con los requisitos de acceso');
    header('Location: http://localhost/TORTASYTORTAS/views/gestorpedido.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background: #f5f5f5; }
        .card { box-shadow: 0 2px 8px #ccc; border-radius: 16px; }
        .form-label { font-weight: 500; }
        .btn-primary { background: #5D54A4; border: none; }
        .btn-primary:hover { background: #4a418c; }
    </style>
</head>
<body>
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card p-4" style="min-width:350px; max-width:400px; width:100%;">
        <div class="text-center mb-3">
            <i class="bi bi-person-plus display-4 text-primary"></i>
            <h3 class="fw-bold mb-0">Crear Usuario</h3>
            <p class="text-muted mb-0">Solo el administrador puede crear nuevos usuarios</p>
        </div>
        <!-- Formulario para crear usuario -->
        <form id="adminForm">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario (email)</label>
                <input type="email" class="form-control" id="usuario" name="usuario" required autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary w-100">Registrar</button>
        </form>
    </div>
</div>
<script>
// Funciones de validación
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function validarPassword(password) {
    // Mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
    return regex.test(password);
}

function validarNombre(nombre) {
    // Solo letras, espacios y acentos
    const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/;
    return regex.test(nombre);
}

// Maneja el envío del formulario para crear usuario
const form = document.getElementById('adminForm');
form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const nombre = document.getElementById('nombre').value;
    const usuario = document.getElementById('usuario').value;
    const password = document.getElementById('password').value;
    
    // Validaciones
    if (!validarNombre(nombre)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el nombre',
            text: 'El nombre solo debe contener letras y espacios (2-50 caracteres)',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    if (!validarEmail(usuario)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el email',
            text: 'Por favor, ingrese un email válido',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    if (!validarPassword(password)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en la contraseña',
            text: 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    try {
        const res = await fetch('http://localhost/TORTASYTORTAS/controllers/loginController.php?action=registrarAdmin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, usuario, password })
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Usuario creado',
                text: 'El usuario fue registrado correctamente.',
                confirmButtonText: 'Ir al dashboard',
                confirmButtonColor: '#5D54A4'
            }).then(() => {
                window.location.href = 'gestorpedido.php';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error || 'Error desconocido',
                confirmButtonColor: '#5D54A4'
            });
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor',
            confirmButtonColor: '#5D54A4'
        });
    }
});

// Validación en tiempo real
document.getElementById('nombre').addEventListener('input', function(e) {
    if (!validarNombre(e.target.value)) {
        e.target.setCustomValidity('El nombre solo debe contener letras y espacios (2-50 caracteres)');
    } else {
        e.target.setCustomValidity('');
    }
});

document.getElementById('usuario').addEventListener('input', function(e) {
    if (!validarEmail(e.target.value)) {
        e.target.setCustomValidity('Por favor, ingrese un email válido');
    } else {
        e.target.setCustomValidity('');
    }
});

document.getElementById('password').addEventListener('input', function(e) {
    if (!validarPassword(e.target.value)) {
        e.target.setCustomValidity('La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número');
    } else {
        e.target.setCustomValidity('');
    }
});
</script>
</body>
</html> 