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
  
  <!-- DataTables CSS SOLO LOCAL, elimina los CDN -->
  <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"> -->
  <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"> -->
  <link rel="stylesheet" href="../assets/css/gestorCliente.css">
  <link rel="stylesheet" href="../assets/DataTables/datatables.min.css"> <!-- DataTables local -->

  <!-- jQuery local: debe ir ANTES de DataTables y de tu JS personalizado -->
  <script src="../assets/DataTables/jQuery/dist/jQuery.js"></script>
  <!-- DataTables JS local -->
  <script src="../assets/DataTables/datatables.min.js"></script>
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
          <a class="nav-link text-white" href="#"><i class="bi bi-gear"></i> Resumen</a>
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
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="cedula" placeholder="Cédula" required>
        <input type="text" name="direccion" placeholder="Dirección" required>
        <input type="text" name="telefono" placeholder="Teléfono" required>
        <button type="submit">Registrar</button>
      </form>
    </div>
    <div class="tabla-clientes">
      <h2>Clientes registrados</h2>
      <table id="tablaClientes" class="display" style="width:100%">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Cédula</th>
            <th>Dirección</th>
            <th>Teléfono</th>
          </tr>
        </thead>
        <tbody>
          <!-- Se llena por AJAX -->
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- Script AJAX para cargar y crear clientes -->
<!-- Tu JS personalizado debe ir DESPUÉS de jQuery y DataTables -->
<script src="../assets/js/gestorCliente.js"></script>
</body>
</html>
