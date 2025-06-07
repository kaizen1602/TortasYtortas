<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: http://localhost/TORTASYTORTAS/views/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Pedido</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/resumenCosto.css">
    <link rel="stylesheet" href="../assets/css/gestorPedido.css">

    <link rel="stylesheet" href="../assets/node_modules/bootstrap/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <!-- DataTables CSS SOLO LOCAL, elimina los CDN -->
  <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"> -->
  <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"> -->
  <link rel="stylesheet" href="../assets/css/resumenCostoDiseño.css">
  
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
  <!-- Navbar con estilo mejorado -->
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

    <!-- Contenedor principal con layout mejorado y responsive -->
    <div class="container-fluid mt-4"><!-- Cambiado a container-fluid para mejor responsividad -->
        <div class="row g-3"><!-- Usamos row de Bootstrap para mejor control -->
            <div class="col-12 col-lg-8"><!-- Tabla ocupa 12 columnas en móvil, 8 en escritorio -->
                <div class="table-container">
                    <div class="table-responsive"><!-- Asegura scroll horizontal en móvil -->
                        <table id="tablaResumenCosto" class="table table-borderless display nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Pedido #</th>
                                    <th>Cliente</th>
                                    <th>Total Venta</th>
                                    <th>Ganancia</th>
                                    <th>Fecha</th>
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
            <div class="col-12 col-lg-4"><!-- Cards ocupan 12 columnas en móvil, 4 en escritorio -->
                <div class="row g-2" id="cardsResumen"><!-- Las cards se apilan en móvil -->
                    <!-- Las cards se generan dinámicamente por JS con efectos 3D -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para detalles del pedido -->
    <div class="modal fade" id="detallesPedidoModal" tabindex="-1" aria-labelledby="detallesPedidoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detallesPedidoModalLabel">Detalles del Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="tablaDetallesPedido" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Costo Unit.</th>
                                    <th>Precio Venta</th>
                                    <th>Descuento</th>
                                    <th>Adicionales</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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

<link rel="stylesheet" href="../assets/DataTables/datatables.min.css">
<script src="../assets/DataTables/jQuery/dist/jQuery.js"></script>
<script src="../assets/DataTables/datatables.min.js"></script>
<script src="../assets/js/resumenCosto.js"></script>

</body>
</html>