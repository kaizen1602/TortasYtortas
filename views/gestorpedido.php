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
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gestor de Pedidos</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../assets/node_modules/bootstrap/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../assets/css/gestorPedido.css">
  <link rel="stylesheet" href="../assets/css/gestorCliente.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
<br>

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
                    </div>
                </div>
            </div>
            <div class="mb-4 d-flex flex-column align-items-center justify-content-center">
              <div class="busqueda-modern-container mb-3">
                <input type="text" id="busquedaCliente" class="busqueda-modern-input" placeholder="Buscar por nombre de cliente..." aria-label="Buscar" autocomplete="off">
                <span class="busqueda-modern-icon">
                  <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                  </svg>
                </span>
              </div>
              <div id="resultadosPedidos" class="resultados-pedidos-simetrico w-100 d-flex flex-column align-items-center justify-content-center">
                <ul id="listaPedidos" class="list-group tabla-clientes mb-4" style="max-width: 420px; width: 100%;">
                  <!-- Los pedidos se llenarán dinámicamente aquí -->
                </ul>
                <!-- El div de paginación se inyecta aquí por JS -->
                <div id="paginacionPedidos" class="d-flex justify-content-center w-100"></div>
              </div>
            </div>
        </div>

        <!-- Panel derecho: detalle del pedido -->
        <div id="detalle-pedido" class="col-md-8 col-12 d-flex flex-column align-items-center justify-content-start">
            <div class="w-100 d-flex flex-column align-items-center mt-4 mb-4">
                <h3 class="mb-3 text-center" style="font-size:2rem;">Detalles del Pedido</h3>
                <div class="w-100 d-flex justify-content-end">
                  <button id="btnEditarPedido" class="btn btn-primary btn-sm d-none"><i class="bi bi-pencil-square"></i> Editar</button>
                </div>
            </div>
            <div id="pedidoDetalle" class="w-100">
                <!-- Aquí se actualizarán los detalles del pedido seleccionado -->
            </div>
            <div class="d-flex justify-content-end fixed-bottom pe-5 pb-4" style="z-index: 1050; pointer-events: none;">
              <button id="btnCrearPedido" class="btn btn-success btn-lg px-5" style="pointer-events: auto;">
                <i class="bi bi-plus-circle"></i> Crear Pedido
              </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear Pedido -->
<div class="modal fade" id="modalCrearPedido" tabindex="-1" aria-labelledby="modalCrearPedidoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content formulario">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCrearPedidoLabel">Crear Nuevo Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- Formulario completo para crear pedido -->
        <form id="formCrearPedido">
          <div class="mb-3 position-relative">
            <label for="crear_cliente_nombre" class="form-label">Cliente</label>
            <input type="text" class="form-control" id="crear_cliente_nombre" placeholder="Escribe el nombre del cliente" autocomplete="off" required>
            <input type="hidden" id="crear_cliente" name="cliente_id">
            <div id="sugerencias_clientes" class="list-group position-absolute w-100" style="z-index:1000;"></div>
          </div>
          <div id="crear_productos_container">
            <!-- Aquí se agregarán los productos y adicionales dinámicamente -->
          </div>
          <div class="mb-3">
            <label for="crear_total" class="form-label">Total</label>
            <input type="text" class="form-control" id="crear_total" name="total" required readonly>
          </div>
          <div class="mb-3">
            <label for="crear_total_pagado" class="form-label">Total que va a pagar</label>
            <input type="text" class="form-control" id="crear_total_pagado" name="total_pagado" value="" required>
          </div>
          <div class="mb-3">
            <label for="crear_descuento" class="form-label">Descuento aplicado</label>
            <input type="text" class="form-control" id="crear_descuento" name="descuento" value="" readonly>
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
    <div class="modal-content formulario">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarPedidoLabel">Editar Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- Formulario completo para editar pedido -->
        <form id="formEditarPedido">
          <input type="hidden" id="editar_pedido_id" name="pedido_id">
          <div class="mb-3 position-relative">
            <label for="editar_cliente_nombre" class="form-label">Cliente</label>
            <input type="text" class="form-control" id="editar_cliente_nombre" placeholder="Escribe el nombre del cliente" autocomplete="off" required>
            <input type="hidden" id="editar_cliente" name="cliente_id">
            <div id="sugerencias_clientes_editar" class="list-group position-absolute w-100" style="z-index:1000;"></div>
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
            <input type="text" class="form-control" id="editar_total" name="total" required readonly>
          </div>
          <div class="mb-3">
            <label for="editar_descuento" class="form-label">Descuento aplicado</label>
            <input type="text" class="form-control" id="editar_descuento" name="descuento" value="" readonly>
          </div>
          <div class="mb-3">
            <label for="editar_total_pagado" class="form-label">Total que va a pagar</label>
            <input type="text" class="form-control" id="editar_total_pagado" name="total_pagado" value="" required>
          </div>
          <div class="mb-3">
            <label for="editar_descuento_porcentaje" class="form-label">Porcentaje de descuento</label>
            <input type="number" class="form-control" id="editar_descuento_porcentaje" placeholder="Porcentaje de descuento" min="0" max="100" step="1" value="0">
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
 <!-- Footer con información de soporte -->
 <footer class="bg-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0 text-muted">© 2025 GestorPedidos - Sistema de administración de ventas</p>
        </div>
    </footer>


  <script src="../assets/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/gestorPedido.js"></script>
</body>
</html>
