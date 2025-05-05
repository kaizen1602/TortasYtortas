<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gestor de Pedidos</title>
  <link rel="stylesheet" href="../assets/node_modules/bootstrap/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../assets/css/gestorPedido.css">
</head>
<body>
<nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-white" href="#">GestorPedidos</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active text-white" href="#"><i class="bi bi-house-door"></i> Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#"><i class="bi bi-people"></i> Clientes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#"><i class="bi bi-box-seam"></i> Pedidos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#"><i class="bi bi-bar-chart-line"></i> Productos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#"><i class="bi bi-gear"></i> Resumen</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
    <div class="row align-items-start">
        <!-- Panel izquierdo: lista de pedidos -->
        <div id="lista-pedidos" class="col-md-4 col-12 mb-4 mb-md-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <label class="form-label fw-bold mb-0">Filtrar por estado:</label>
                    <div id="filtroEstadoPedidos" class="d-inline-block ms-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="estadoPedido" id="filtroTodos" value="todos" checked>
                            <label class="form-check-label" for="filtroTodos">Todos</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="estadoPedido" id="filtroActivos" value="1">
                            <label class="form-check-label" for="filtroActivos">Activos</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="estadoPedido" id="filtroInactivos" value="0">
                            <label class="form-check-label" for="filtroInactivos">Inactivos</label>
                        </div>
                    </div>
                </div>
                <button id="btnCrearPedido" class="btn btn-success btn-sm ms-2"><i class="bi bi-plus-circle"></i> Crear Pedido</button>
            </div>
            <ul id="listaPedidos" class="list-group">
                <!-- Los pedidos se llenarán dinámicamente aquí -->
            </ul>
        </div>

        <!-- Panel derecho: detalle del pedido -->
        <div id="detalle-pedido" class="col-md-8 col-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="mb-0">Detalles del Pedido</h3>
                <button id="btnEditarPedido" class="btn btn-primary btn-sm d-none"><i class="bi bi-pencil-square"></i> Editar</button>
            </div>
            <div id="pedidoDetalle">
                <!-- Aquí se actualizarán los detalles del pedido seleccionado -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Pedido -->
<div class="modal fade" id="modalCrearPedido" tabindex="-1" aria-labelledby="modalCrearPedidoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCrearPedidoLabel">Crear Nuevo Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- Formulario completo para crear pedido -->
        <form id="formCrearPedido">
          <div class="mb-3">
            <label for="crear_cliente" class="form-label">Cliente</label>
            <select class="form-control" id="crear_cliente" name="cliente" required></select>
          </div>
          <div class="mb-3">
            <label for="crear_estado" class="form-label">Estado</label>
            <select class="form-control" id="crear_estado" name="estado" required>
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <div id="crear_productos_container">
            <!-- Aquí se agregarán los productos y adicionales dinámicamente -->
          </div>
          <div class="mb-3">
            <label for="crear_total" class="form-label">Total</label>
            <input type="number" class="form-control" id="crear_total" name="total" required readonly>
          </div>
          <div class="mb-3">
            <label for="crear_fecha" class="form-label">Fecha</label>
            <input type="datetime-local" class="form-control" id="crear_fecha" name="fecha">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="formCrearPedido" class="btn btn-success">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Pedido -->
<div class="modal fade" id="modalEditarPedido" tabindex="-1" aria-labelledby="modalEditarPedidoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarPedidoLabel">Editar Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- Formulario completo para editar pedido -->
        <form id="formEditarPedido">
          <div class="mb-3">
            <label for="editar_cliente" class="form-label">Cliente</label>
            <select class="form-control" id="editar_cliente" name="cliente" required></select>
          </div>
          <div class="mb-3">
            <label for="editar_estado" class="form-label">Estado</label>
            <select class="form-control" id="editar_estado" name="estado" required>
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <div id="editar_productos_container">
            <!-- Aquí se agregarán los productos y adicionales dinámicamente -->
          </div>
          <div class="mb-3">
            <label for="editar_total" class="form-label">Total</label>
            <input type="number" class="form-control" id="editar_total" name="total" required readonly>
          </div>
          <div class="mb-3">
            <label for="editar_fecha" class="form-label">Fecha</label>
            <input type="datetime-local" class="form-control" id="editar_fecha" name="fecha">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="formEditarPedido" class="btn btn-primary">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>

  <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/gestorPedido.js"></script>
</body>
</html>
