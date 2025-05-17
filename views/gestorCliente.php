<?php
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
  
  <!-- DataTables CSS SOLO LOCAL, elimina los CDN -->
  <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"> -->
  <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"> -->
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
                        <a class="nav-link active text-white" href="../views/resumenCosto.php">
                            <i class="bi bi-gear me-1"></i> Resumen
                        </a>
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
      <table id="tablaClientes" class="table table-striped">
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
                        <input type="text" class="form-control" id="editar_cedula" name="cedula" required>
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
<!-- Script AJAX para cargar y crear clientes -->
<!-- Tu JS personalizado debe ir DESPUÉS de jQuery, Bootstrap, DataTables y SweetAlert2 -->
<script src="../assets/js/gestorCliente.js"></script>
<script>
function validarTexto(input) {
    // Remover caracteres especiales y números
    input.value = input.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
}

function validarCedula(input) {
    // Solo permitir números y guiones
    input.value = input.value.replace(/[^0-9-]/g, '');
    
    // Validar formato de cédula (XX-XXXXXXX-X)
    const cedulaRegex = /^\d{2}-\d{7}-\d{1}$/;
    if (input.value && !cedulaRegex.test(input.value)) {
        input.setCustomValidity('Formato de cédula inválido. Use XX-XXXXXXX-X');
    } else {
        input.setCustomValidity('');
    }
}

function validarTelefono(input) {
    // Solo permitir números, paréntesis, guiones y espacios
    input.value = input.value.replace(/[^0-9()-\s]/g, '');
    
    // Validar formato de teléfono
    const telefonoRegex = /^\(\d{3}\)\s\d{3}-\d{4}$/;
    if (input.value && !telefonoRegex.test(input.value)) {
        input.setCustomValidity('Formato de teléfono inválido. Use (XXX) XXX-XXXX');
    } else {
        input.setCustomValidity('');
    }
}
</script>
</body>
</html>
