<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Pedido</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/resumenCosto.css">

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
                        <a class="nav-link active text-white" href="../views/resumenCosto.php">
                            <i class="bi bi-gear me-1"></i> Resumen
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal con layout mejorado -->
    <div class="container mt-4">
        <div class="resumen-flex-container">
            <div class="resumen-tabla">
                <!-- Sección de tabla con diseño mejorado a pantalla completa -->
                <div class="table-container">
                    <!-- El título se insertará vía JS para mejor integración con DataTables -->
                    <div class="table-responsive">
                        <table id="tablaResumenCosto" class="table table-borderless display nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Costo Unit.</th>
                                    <th>Precio Venta</th>
                                    <th>Descuento</th>
                                    <th>Adicionales</th>
                                    <th>Total</th>
                                    <th>Ganancia</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llena por AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="resumen-cards">
                <!-- Sección de tarjetas de resumen con efectos 3D -->
                <div class="row" id="cardsResumen">
                    <!-- Las cards se generan dinámicamente por JS con efectos 3D -->
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