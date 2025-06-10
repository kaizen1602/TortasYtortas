<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: http://localhost/TORTASYTORTAS/views/login.php');
    exit();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gestor de Clientes</title>
  <!-- Bootstrap CSS local (ajusta la ruta si es necesario) -->
  <link rel="stylesheet" href="../assets/node_modules/bootstrap/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/gestorCliente.css">
  <link rel="stylesheet" href="../assets/DataTables/datatables.min.css"> <!-- DataTables local -->
  <link rel="stylesheet" href="../assets/DataTables/Responsive/css/responsive.dataTables.min.css">
 

  <!-- jQuery local: debe ir ANTES de DataTables y de tu JS personalizado -->
  <script src="../assets/DataTables/jQuery/dist/jQuery.js"></script>
  <!-- Bootstrap JS: necesario para que funcionen los modales desde JS -->
  <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables JS local -->
  <script src="../assets/DataTables/datatables.min.js"></script>
  <script src="../assets/DataTables/Responsive/js/dataTables.responsive.min.js"></script>
  <!-- SweetAlert2: necesario para que funcione Swal -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="#">
                <i class="bi bi-box-seam me-2"></i>GestorPedidos
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../views/gestorpedido.php">
                            <i class="bi bi-house-door me-1"></i> Pedido
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../views/gestorCliente.php">
                            <i class="bi bi-people me-1"></i> Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../views/gestorProducto.php">
                            <i class="bi bi-bar-chart-line me-1"></i> Productos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="../views/gestorAdicionales.php">
                            <i class="bi bi-gear me-1"></i> Adicionales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="../views/resumenCosto.php">
                            <i class="bi bi-gear me-1"></i> Resumen
                        </a>
                    </li>
                    <!-- Usuario logueado -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Usuario'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                            <li><a class="dropdown-item" href="../views/registrar_admin.php"><i class="bi bi-person-plus me-1"></i> Crear usuario</a></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<div class="container mt-5">
  <div class="contenedor-flex">
    <div class="formulario">
      <h2>Agregar nuevo cliente</h2>
      <form id="formCrearCliente">
        <input type="text" name="nombre" placeholder="Nombre" required oninput="validarTexto(this)">
        <input type="text" name="cedula" placeholder="Cédula" required oninput="validarCedula(this)">
        <input type="text" name="direccion" placeholder="Dirección" required oninput="validarTexto(this)">
        <input type="text" name="telefono" placeholder="Teléfono" required oninput="validarTelefono(this)">
        <button type="submit">Registrar</button>
      </form>
    </div>
    <div class="tabla-clientes">
      <h2>Clientes registrados</h2>
      <table id="tablaClientes" class="display responsive nowrap" style="width:100%">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Documento</th>
            <th>Direccion</th>
            <th>Telefono</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <!-- Se llena por AJAX -->
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content formulario">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarClienteLabel">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarCliente">
                    <input type="hidden" id="editar_id" name="id">
                    <div class="mb-3">
                        <label for="editar_nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="editar_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="editar_cedula" class="form-label">Cédula</label>
                        <input type="text" class="form-control" id="editar_cedula" name="cedula" required readonly>
                    </div>
                    <div class="mb-3">
                        <label for="editar_direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="editar_direccion" name="direccion" required>
                    </div>
                    <div class="mb-3">
                        <label for="editar_telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="editar_telefono" name="telefono" required>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
 <!-- Footer con información de soporte -->
 <footer class="bg-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0 text-muted">© 2025 GestorPedidos - Sistema de administración de ventas</p>
        </div>
    </footer>

<!-- Script AJAX para cargar y crear clientes -->
<!-- Tu JS personalizado debe ir DESPUÉS de jQuery, Bootstrap, DataTables y SweetAlert2 -->
<script src="../assets/js/gestorCliente.js"></script>
<script>
function validarTexto(input) {
    // Remover caracteres especiales y números
    input.value = input.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
}

// Funciones de validación para clientes
function validarNombre(nombre) {
    // Validar que el nombre solo contenga letras, espacios y acentos
    const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/;
    return regex.test(nombre);
}

function validarTelefono(telefono) {
    // Validar que el teléfono tenga un formato válido (10 dígitos)
    const regex = /^\d{10}$/;
    return regex.test(telefono);
}

function validarDireccion(direccion) {
    // Validar que la dirección tenga una longitud razonable
    return direccion.trim().length >= 5 && direccion.trim().length <= 200;
}

// Maneja el envío del formulario para crear cliente
$('#formCrearCliente').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Validaciones
    if (!validarNombre(data.nombre)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el nombre',
            text: 'El nombre solo debe contener letras y espacios (2-50 caracteres)',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    if (!validarTelefono(data.telefono)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el teléfono',
            text: 'El teléfono debe tener 10 dígitos',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    if (!validarDireccion(data.direccion)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en la dirección',
            text: 'La dirección debe tener entre 5 y 200 caracteres',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }

    // Si todas las validaciones pasan, enviar el formulario
    fetch('../controllers/clienteController.php?action=crear', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Cliente registrado correctamente!',
                showConfirmButton: false,
                timer: 1500
            });
            this.reset();
            tabla.ajax.reload();
        } else {
            // Si el error es por cédula duplicada, mostrar mensaje personalizado
            if (resp.error && resp.error.includes('Duplicate entry') && resp.error.includes('cedula')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Cédula ya registrada',
                    text: 'La cédula ingresada ya existe en la base de datos. Por favor, verifica la información.',
                    confirmButtonColor: '#5D54A4'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: resp.error || 'Error al registrar el cliente',
                    confirmButtonColor: '#5D54A4'
                });
            }
        }
    })
    .catch(err => {
        console.error('Error:', err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al procesar la solicitud',
            confirmButtonColor: '#5D54A4'
        });
    });
});

// Validación en tiempo real
$('#nombre').on('input', function() {
    if (!validarNombre(this.value)) {
        this.setCustomValidity('El nombre solo debe contener letras y espacios (2-50 caracteres)');
    } else {
        this.setCustomValidity('');
    }
});

$('#telefono').on('input', function() {
    if (!validarTelefono(this.value)) {
        this.setCustomValidity('El teléfono debe tener 10 dígitos');
    } else {
        this.setCustomValidity('');
    }
});

$('#direccion').on('input', function() {
    if (!validarDireccion(this.value)) {
        this.setCustomValidity('La dirección debe tener entre 5 y 200 caracteres');
    } else {
        this.setCustomValidity('');
    }
});
</script>
</body>
</html>
