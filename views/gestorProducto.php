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
  <title>Gestor de Productos</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="../assets/node_modules/bootstrap/dist/css/bootstrap.min.css" />
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="../assets/node_modules/bootstrap/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <!-- DataTables CSS SOLO LOCAL, elimina los CDN -->
  <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"> -->
  <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"> -->
  <link rel="stylesheet" href="../assets/css/gestorCliente.css">
  <link rel="stylesheet" href="../assets/css/gestorCliente.css">
  <link rel="stylesheet" href="../assets/DataTables/datatables.min.css"> <!-- DataTables local -->

  <!-- jQuery local: debe ir ANTES de DataTables y de tu JS personalizado -->
  <script src="../assets/DataTables/jQuery/dist/jQuery.js"></script>
  <!-- Bootstrap JS: necesario para que funcionen los modales desde JS -->
  <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables JS local -->
  <script src="../assets/DataTables/datatables.min.js"></script>
  <!-- SweetAlert2: necesario para que funcione Swal -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<!-- Navbar igual a gestorpedido.php -->
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
      <h2>Agregar nuevo producto</h2>
      <form id="formCrearProducto">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="number" name="precio_base" placeholder="Precio Base" step="0.01" min="0" required oninput="validarNumeroPositivo(this)">
        <input type="number" name="precio_venta" placeholder="Precio Venta" step="0.01" min="0" required oninput="validarNumeroPositivo(this)">
        <input type="number" name="descuento" placeholder="Descuento" step="0.01" min="0" required oninput="validarNumeroPositivo(this)">
        <input type="number" name="stock" placeholder="Stock" min="0" required oninput="validarNumeroPositivo(this)">
        <button type="submit">Registrar</button>
      </form>
    </div>
    <div class="tabla-clientes">
      <h2>Productos registrados</h2>
      <table id="tablaProductos" class="display" style="width:100%">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Precio Base</th>
            <th>Precio Venta</th>
            <th>Descuento</th>
            <th>Stock</th>
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

<!-- Modal Editar Producto -->
<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content formulario">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarProductoLabel">Editar Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formEditarProducto">
          <input type="hidden" id="editar_id" name="id">
          <div class="mb-3">
            <label for="editar_nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="editar_nombre" name="nombre" required>
          </div>
          <div class="mb-3">
            <label for="editar_precio_base" class="form-label">Precio Base</label>
            <input type="number" class="form-control" id="editar_precio_base" name="precio_base" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="editar_precio_venta" class="form-label">Precio Venta</label>
            <input type="number" class="form-control" id="editar_precio_venta" name="precio_venta" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="editar_descuento" class="form-label">Descuento</label>
            <input type="number" class="form-control" id="editar_descuento" name="descuento" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="editar_stock" class="form-label">Stock</label>
            <input type="number" class="form-control" id="editar_stock" name="stock" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="formEditarProducto" class="btn btn-primary">Guardar Cambios</button>
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


<!-- Tu JS personalizado debe ir DESPUÉS de jQuery, Bootstrap, DataTables y SweetAlert2 -->
<script src="../assets/js/gestorProducto.js"></script>
<script>
function validarNumeroPositivo(input) {
    // Remover caracteres no numéricos excepto punto decimal
    input.value = input.value.replace(/[^0-9.]/g, '');
    
    // Asegurar que solo haya un punto decimal
    const parts = input.value.split('.');
    if (parts.length > 2) {
        input.value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Convertir a número y validar
    const valor = parseFloat(input.value);
    if (isNaN(valor) || valor < 0) {
        input.value = '';
        Swal.fire({
            icon: 'error',
            title: 'Valor inválido',
            text: 'Por favor ingrese un número positivo'
        });
    }
}

// Funciones de validación para productos
function validarPrecio(precio) {
    // Validar que el precio sea un número positivo
    return !isNaN(precio) && parseFloat(precio) > 0;
}

function validarStock(stock) {
    // Validar que el stock sea un número entero no negativo
    return !isNaN(stock) && Number.isInteger(parseFloat(stock)) && parseFloat(stock) >= 0;
}

function validarDescuento(descuento) {
    // Validar que el descuento esté entre 0 y 100
    return !isNaN(descuento) && parseFloat(descuento) >= 0 && parseFloat(descuento) <= 100;
}

function validarNombreProducto(nombre) {
    // Validar que el nombre no esté vacío y tenga una longitud razonable
    return nombre.trim().length > 0 && nombre.trim().length <= 100;
}

// Maneja el envío del formulario para crear producto
$('#formCrearProducto').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Validaciones
    if (!validarNombreProducto(data.nombre)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el nombre',
            text: 'El nombre del producto no puede estar vacío y debe tener máximo 100 caracteres',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    if (!validarPrecio(data.precio_base)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el precio base',
            text: 'El precio base debe ser un número positivo',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    if (!validarPrecio(data.precio_venta)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el precio de venta',
            text: 'El precio de venta debe ser un número positivo',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    if (!validarDescuento(data.descuento)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el descuento',
            text: 'El descuento debe ser un número entre 0 y 100',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    if (!validarStock(data.stock)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el stock',
            text: 'El stock debe ser un número entero no negativo',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }

    // Si todas las validaciones pasan, enviar el formulario
    fetch('../controllers/productoController.php?action=crear', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Producto registrado correctamente!',
                showConfirmButton: false,
                timer: 1500
            });
            this.reset();
            tabla.ajax.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resp.error || 'Error al registrar el producto',
                confirmButtonColor: '#5D54A4'
            });
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
    if (!validarNombreProducto(this.value)) {
        this.setCustomValidity('El nombre del producto no puede estar vacío y debe tener máximo 100 caracteres');
    } else {
        this.setCustomValidity('');
    }
});

$('#precio_base').on('input', function() {
    if (!validarPrecio(this.value)) {
        this.setCustomValidity('El precio base debe ser un número positivo');
    } else {
        this.setCustomValidity('');
    }
});

$('#precio_venta').on('input', function() {
    if (!validarPrecio(this.value)) {
        this.setCustomValidity('El precio de venta debe ser un número positivo');
    } else {
        this.setCustomValidity('');
    }
});

$('#descuento').on('input', function() {
    if (!validarDescuento(this.value)) {
        this.setCustomValidity('El descuento debe ser un número entre 0 y 100');
    } else {
        this.setCustomValidity('');
    }
});

$('#stock').on('input', function() {
    if (!validarStock(this.value)) {
        this.setCustomValidity('El stock debe ser un número entero no negativo');
    } else {
        this.setCustomValidity('');
    }
});
</script>
</body>
</html>
