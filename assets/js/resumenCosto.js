// JavaScript mejorado con efectos y optimización de tabla a pantalla completa
$(document).ready(function() {
    // Cargar las cards con efecto
    cargarCardsResumen();
    
    // Inicializar el DataTable mejorado a pantalla completa
    var tabla = $('#tablaResumenCosto').DataTable({
        responsive: true,
        scrollX: true,
        scrollCollapse: true,
        autoWidth: true, 
        paging: true,
        info: true,
        searching: true,
        ajax: {
            url: '../controllers/resumenCostoController.php?action=ventasAgrupadas',
            dataSrc: function(json) {
                if (json.ventas) {
                    return json.ventas;
                } else if (json.error) {
                    mostrarError(json.error);
                    return [];
                } else {
                    console.error('Respuesta inesperada:', json);
                    mostrarError('Respuesta inesperada del servidor.');
                    return [];
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar ventas:', error);
                mostrarError('Error al cargar el resumen de ventas.');
            }
        },
        columns: [
            { data: 'pedido_id', title: 'Pedido #' },
            { data: 'cliente', title: 'Cliente' },
            { 
                data: 'total_venta', 
                title: 'Total Venta',
                render: data => formatoCOP(Number(data)) 
            },
            { 
                data: 'ganancia', 
                title: 'Ganancia',
                render: function(data) {
                    const valor = parseFloat(data);
                    const claseColor = valor >= 0 ? 'ganancia-positiva' : 'ganancia-negativa';
                    return `<span class="${claseColor}">${formatoCOP(valor)}</span>`;
                }
            },
            { data: 'fecha', title: 'Fecha' },
            {
                data: null,
                title: 'Acciones',
                render: function(data) {
                    return `<button class="btn btn-primary btn-sm ver-detalles" data-pedido-id="${data.pedido_id}">
                        <i class="bi bi-eye"></i>
                    </button>`;
                }
            }
        ],
        language: {
            url: '../assets/DataTables/es-ES.json'
        },
        dom: '<"top d-flex justify-content-between align-items-center mb-3"<"dataTables_info_custom">f><"clear">rt<"bottom d-flex justify-content-between align-items-center mt-3"<"d-flex align-items-center"l<"ml-2"i>>p>',
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
        pageLength: 10,
        initComplete: function() {
            // Asegurar que la tabla se ajuste correctamente al 100% del ancho disponible
            $(window).trigger('resize');
            
            // Añadir título personalizado
            $("div.dataTables_info_custom").html('<h2 class="table-title">Resumen de Costos</h2>');
            
            // Hacer más atractivos los controles de la tabla
            $('.dataTables_filter input').attr('placeholder', 'Buscar...');
            $('.dataTables_filter input').addClass('shadow-sm');
            
            // Añadir iconos a los botones de paginación
            $('.dataTables_paginate .previous').html('<i class="bi bi-chevron-left"></i>');
            $('.dataTables_paginate .next').html('<i class="bi bi-chevron-right"></i>');
            
            // Al inicializar la tabla de resumen, dejar solo las clases de Bootstrap y DataTables
            $('#tablaResumenCosto').removeClass('table-custom').addClass('table table-striped table-hover table-bordered display');
        },
        drawCallback: function() {
            // Añadir clases para efectos visuales en cada redibujado de la tabla
            $(".paginate_button").addClass("shadow-sm");
            
            // Aplicar coloreo a ganancias
            aplicarColoresGanancias();
        }
    });
    
    // Ajustar la tabla cuando cambia el tamaño de la ventana
    $(window).resize(function() {
        if (tabla) {
            tabla.columns.adjust();
        }
    });
    
    // Animar entrada de elementos
    animarEntrada();

    // Manejar clic en botón de detalles
    $('#tablaResumenCosto').on('click', '.ver-detalles', function() {
        const pedidoId = $(this).data('pedido-id');
        cargarDetallesPedido(pedidoId);
    });
});

// Función para cargar las cards con efecto 3D
function cargarCardsResumen() {
    fetch('../controllers/resumenCostoController.php?action=totales')
        .then(response => response.json())
        .then(data => {
            if (typeof data.clientes !== 'undefined' && typeof data.pedidos !== 'undefined' && typeof data.ventas !== 'undefined') {
                // Crear cards con efecto 3D
                let html = '';
                
                html += `
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card-3d card-clientes float-animation" style="animation-delay: 0s;">
                        <div class="card-shape"></div>
                        <div class="card-shape-2"></div>
                        <div class="card-content">
                            <div>
                                <h5 class="card-title">Total Clientes</h5>
                                <p class="card-subtitle">Número de clientes registrados</p>
                            </div>
                            <div class="card-value">${data.clientes}</div>
                        </div>
                        <i class="bi bi-people card-icon"></i>
                    </div>
                </div>
                `;
                
                html += `
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card-3d card-pedidos float-animation" style="animation-delay: 0.2s;">
                        <div class="card-shape"></div>
                        <div class="card-shape-2"></div>
                        <div class="card-content">
                            <div>
                                <h5 class="card-title">Total Pedidos</h5>
                                <p class="card-subtitle">Pedidos procesados</p>
                            </div>
                            <div class="card-value">${data.pedidos}</div>
                        </div>
                        <i class="bi bi-box-seam card-icon"></i>
                    </div>
                </div>
                `;
                
                html += `
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="card-3d card-ventas float-animation" style="animation-delay: 0.4s;">
                        <div class="card-shape"></div>
                        <div class="card-shape-2"></div>
                        <div class="card-content">
                            <div>
                                <h5 class="card-title">Total Ventas</h5>
                                <p class="card-subtitle">Ingresos totales</p>
                            </div>
                            <div class="card-value">${formatoCOP(Number(data.ventas))}</div>
                        </div>
                        <i class="bi bi-currency-dollar card-icon"></i>
                    </div>
                </div>
                `;
                
                // Insertar las cards en el contenedor con efecto de aparición
                $('#cardsResumen').html(html);
                
                // Iniciar animaciones después de un pequeño retraso
                setTimeout(function() {
                    $('.card-3d').addClass('show');
                }, 200);
                
            } else if (data.error) {
                mostrarError(data.error);
            } else {
                console.error('Datos de resumen incompletos:', data);
                mostrarError('Datos de resumen incompletos.');
            }
        })
        .catch(error => {
            console.error('Error al cargar los totales:', error);
            mostrarError('Error al cargar los totales de resumen.');
        });
}

// Función para aplicar colores a las ganancias
function aplicarColoresGanancias() {
    // Esta función es llamada por drawCallback de DataTables
    $('#tablaResumenCosto tbody tr').each(function() {
        const gananciaTexto = $(this).find('td:nth-last-child(2)').text();
        const ganancia = parseFloat(gananciaTexto.replace('$', '').replace(',', '.'));
        
        if (ganancia < 0) {
            $(this).find('td:nth-last-child(2)').addClass('ganancia-negativa');
        } else {
            $(this).find('td:nth-last-child(2)').addClass('ganancia-positiva');
        }
    });
}

// Función para animar la entrada de elementos
function animarEntrada() {
    $('#cardsResumen .col-lg-4').each(function(index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(20px)'
        });
        setTimeout(() => {
            $(this).animate({
                'opacity': '1',
                'transform': 'translateY(0)'
            }, 500);
        }, index * 150);
    });
    
    $('.table-container').css({
        'opacity': '0',
        'transform': 'translateY(20px)'
    });
    setTimeout(() => {
        $('.table-container').animate({
            'opacity': '1',
            'transform': 'translateY(0)'
        }, 500);
    }, 400);
}

// Función para mostrar errores con SweetAlert2
function mostrarError(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        buttonsStyling: true,
        confirmButtonText: 'Entendido',
        customClass: {
            confirmButton: 'btn btn-primary shadow-sm'
        }
    });
}

// Función para formatear números grandes con comas
function formatearNumero(numero) {
    return parseFloat(numero).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Función para formatear a pesos colombianos (COP) sin decimales
function formatoCOP(valor) {
    // Si el valor es válido, lo formatea como moneda colombiana sin decimales
    return valor !== null && valor !== undefined
        ? valor.toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0, maximumFractionDigits: 0 })
        : '$ 0';
}

// Función para cargar los detalles del pedido y mostrarlos en el modal
function cargarDetallesPedido(pedidoId) {
    // Solicita los detalles del pedido al backend por AJAX
    fetch(`../controllers/resumenCostoController.php?action=ventas&pedido_id=${pedidoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.ventas) {
                const detalles = data.ventas;
                const tbody = $('#tablaDetallesPedido tbody');
                tbody.empty();

                // Eliminar resúmenes anteriores
                $('#tablaDetallesPedido').next('.alert-info').remove();

                // Recorre cada producto del pedido y lo agrega a la tabla del modal
                detalles.forEach(detalle => {
                    tbody.append(`
                        <tr>
                            <td>${detalle.producto}</td>
                            <td>${Number(detalle.cantidad)}</td>
                            <td>${formatoCOP(Number(detalle.costo_unitario))}</td>
                            <td>${formatoCOP(Number(detalle.precio_venta_unitario))}</td>
                            <td>${formatoCOP(Number(detalle.descuento))}</td>
                            <td>${formatoCOP(Number(detalle.precio_adicionales))}</td>
                            <td>${formatoCOP(Number(detalle.total))}</td>
                        </tr>
                    `);
                });

                // Calcular totales para el resumen
                let total = 0, totalPagado = 0, diferencia = 0;
                detalles.forEach(detalle => {
                    total += Number(detalle.total) || 0;
                    if (typeof detalle.diferencia !== 'undefined') {
                        diferencia += Number(detalle.diferencia) || 0;
                    }
                });
                // El total pagado es el total menos la diferencia (si existe)
                totalPagado = total - diferencia;

                // Agregar resumen debajo de la tabla
                const resumenHTML = `
                    <div class="alert alert-info mt-3" style="font-size:1.1em;">
                        <strong>Total que debía pagar:</strong> ${formatoCOP(total)}<br>
                        <strong>Total que pagó:</strong> ${formatoCOP(totalPagado)}
                        ${diferencia > 0 ? `<br><strong>Descuento aplicado:</strong> ${formatoCOP(diferencia)}` : ''}
                    </div>
                `;
                $('#tablaDetallesPedido').after(resumenHTML);

                // Muestra el modal con los detalles del pedido
                $('#detallesPedidoModal').modal('show');
            } else if (data.error) {
                mostrarError(data.error);
            }
        })
        .catch(error => {
            console.error('Error al cargar detalles:', error);
            mostrarError('Error al cargar los detalles del pedido.');
        });
}