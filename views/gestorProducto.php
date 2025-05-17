<?php
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
    <a class="navbar-brand fw-bold text-white" href="#">GestorPedidos</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active text-white" href="../views/gestorpedido.php"><i class="bi bi-house-door"></i> Pedido</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="../views/gestorCliente.php"><i class="bi bi-people"></i> Clientes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="../views/gestorProducto.php"><i class="bi bi-bar-chart-line"></i> Productos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="../views/resumenCosto.php"><i class="bi bi-gear"></i> Resumen</a>
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
        <input type="number" name="precio_base" placeholder="Precio Base" step="0.01" required>
        <input type="number" name="precio_venta" placeholder="Precio Venta" step="0.01" required>
        <input type="number" name="descuento" placeholder="Descuento" step="0.01" required>
        <input type="number" name="stock" placeholder="Stock" required>
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
            <th>Acciones</th>
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

<!-- Tu JS personalizado debe ir DESPUÉS de jQuery, Bootstrap, DataTables y SweetAlert2 -->
<script src="../assets/js/gestorProducto.js"></script>
</body>
</html>
