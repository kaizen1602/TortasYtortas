document.addEventListener("DOMContentLoaded", function () {
    // Función para cargar los pedidos en el panel izquierdo
    function cargarPedidos(estado = 'todos') {
        let url = "../controllers/pedidoController.php?action=getPedidos";
        if (estado !== 'todos') {
            url += `&estado=${estado}`;
        }
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(pedidos => {
                pedidosGlobal = pedidos || [];
                paginaActual = 1;
                renderizarPedidosPaginados();
            })
            .catch(error => {
                console.error("Error al cargar los pedidos:", error);
                let listaPedidos = document.getElementById("listaPedidos");
                listaPedidos.innerHTML = '<li class="list-group-item text-danger">Error al cargar los pedidos. Por favor, intente nuevamente.</li>';
                mostrarBotonEditar(false);
                // Limpiar paginación si hay error
                let paginacionDiv = document.getElementById('paginacionPedidos');
                if (paginacionDiv) paginacionDiv.innerHTML = '';
            });
    }

    // Función para mostrar los detalles del pedido seleccionado
    function mostrarDetallesPedido(pedidoId) {
        fetch(`../controllers/pedidoController.php?action=getDetallesPedido&pedido_id=${pedidoId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(detalles => {
                let detalleDiv = document.getElementById("pedidoDetalle");
                if (!detalles || !detalles.detalles) {
                    detalleDiv.innerHTML = '<div class="alert alert-info">No se encontraron detalles para este pedido.</div>';
                    return;
                }
                // Construir el HTML del detalle con diseño de tarjeta
                let productosHTML = '';
                detalles.detalles.forEach(detalle => {
                    productosHTML += `
                    <div class="card card-producto mb-2 p-3">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="mb-2"><strong>Producto:</strong> ${detalle.producto_nombre}</div>
                                <div class="mb-2"><strong>Cantidad:</strong> ${detalle.cantidad}</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-2"><strong>Precio Unitario:</strong> ${formatoCOP(detalle.precio_unitario)}</div>
                                <div class="mb-2"><strong>Subtotal:</strong> ${formatoCOP(detalle.subtotal)}</div>
                            </div>
                        </div>
                        ${detalle.adicionales && detalle.adicionales.length > 0 ? `
                        <div class='mt-2'><strong>Adicionales:</strong>
                            <ul class='list-unstyled mb-0'>
                                ${detalle.adicionales.map(adic => `<li>${adic.adicional_nombre} (${formatoCOP(adic.precio_venta || adic.precio)})</li>`).join('')}
                            </ul>
                        </div>
                        ` : ''}
                    </div>
                    `;
                });
                detalleDiv.innerHTML = `
                <div class="card card-detalle-pedido shadow-sm p-4 mb-4">
                    <div class="row">
                        <div class="col-12">
                            <h2 class="mb-3 text-primary-emphasis">Pedido #${detalles.id}</h2>
                        </div>
                        <div class="col-12 col-md-6">
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><strong>Cliente:</strong> ${detalles.cliente_nombre}</li>
                                <li class="mb-2"><strong>Cédula:</strong> ${detalles.cedula || 'No especificada'}</li>
                                <li class="mb-2"><strong>Dirección:</strong> ${detalles.direccion || 'No especificada'}</li>
                            </ul>
                        </div>
                        <div class="col-12 col-md-6">
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><strong>Teléfono:</strong> ${detalles.telefono || 'No especificado'}</li>
                                <li class="mb-2"><strong>Total calculado:</strong> ${formatoCOP(detalles.total)}</li>
                                <li class="mb-2"><strong>Total pagado:</strong> ${formatoCOP(detalles.total_pagado)}</li>
                                <li class="mb-2"><strong>Descuento aplicado:</strong> ${formatoCOP(detalles.descuento)}</li>
                                <li class="mb-2"><strong>Fecha:</strong> ${detalles.fecha || 'No especificada'}</li>
                                <li class="mb-2"><strong>Estado:</strong> <span class="badge bg-secondary">${detalles.estado == 1 ? 'Activo' : 'Inactivo'}</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h4 class="mb-3 text-secondary">Productos:</h4>
                            ${productosHTML}
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info" style="font-size:1.1em;">
                                <strong>Total que debía pagar:</strong> ${formatoCOP(detalles.total)}<br>
                                <strong>Total que pagó:</strong> ${formatoCOP(detalles.total_pagado)}
                                ${(detalles.total && detalles.total_pagado && detalles.total > detalles.total_pagado) ? `<br><strong>Descuento aplicado:</strong> ${formatoCOP(detalles.total - detalles.total_pagado)}` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                `;
            })
            .catch(error => {
                console.error("Error al cargar los detalles del pedido:", error);
                let detalleDiv = document.getElementById("pedidoDetalle");
                detalleDiv.innerHTML = '<div class="alert alert-danger">Error al cargar los detalles del pedido. Por favor, intente nuevamente.</div>';
            });
    }

    // Evento para el filtro de estado
    document.getElementById('filtroEstadoPedidos').addEventListener('change', function(e) {
        const estado = document.querySelector('input[name="estadoPedido"]:checked').value;
        cargarPedidos(estado);
    });

    // Mostrar/Ocultar botón Editar según selección
    function mostrarBotonEditar(mostrar) {
        const btnEditar = document.getElementById('btnEditarPedido');
        if (btnEditar) {
            if (mostrar) {
                btnEditar.classList.remove('d-none');
            } else {
                btnEditar.classList.add('d-none');
            }
        }
    }

    // Variable para saber si los catálogos están listos
    let catalogosListos = false;

    // Modifica cargarCatalogos para marcar cuando estén listos
    function cargarCatalogos() {
        let clientesCargados = false, productosCargados = false, adicionalesCargados = false;
        // Clientes
        fetch('../controllers/pedidoController.php?action=getClientes')
            .then(res => res.json())
            .then(data => { catalogoClientes = data; clientesCargados = true; checkCatalogos(); });
        // Productos
        fetch('../controllers/pedidoController.php?action=getProductos')
            .then(res => res.json())
            .then(data => { catalogoProductos = data; productosCargados = true; checkCatalogos(); });
        // Adicionales
        fetch('../controllers/pedidoController.php?action=getAdicionales')
            .then(res => res.json())
            .then(data => { catalogoAdicionales = data; adicionalesCargados = true; checkCatalogos(); });
        function checkCatalogos() {
            if (clientesCargados && productosCargados && adicionalesCargados) {
                catalogosListos = true;
            }
        }
    }

    // Llamar al cargar la página
    cargarCatalogos();

    // Evento para el botón Editar Pedido
    const btnEditarPedido = document.getElementById('btnEditarPedido');
    if (btnEditarPedido) {
        btnEditarPedido.addEventListener('click', function() {
            // Obtener el ID del pedido seleccionado del div de detalles
            const detalleDiv = document.getElementById('pedidoDetalle');
            const pedidoId = detalleDiv.querySelector('h2')?.textContent?.match(/#(\d+)/)?.[1];
            if (!pedidoId) {
                console.error('No se pudo obtener el ID del pedido');
                return;
            }
            // Traer los datos del pedido
            fetch(`../controllers/pedidoController.php?action=getDetallesPedido&pedido_id=${pedidoId}`)
                .then(res => res.json())
                .then(pedido => {
                    pedidoActual = pedido;
                    llenarModalEditarPedido(pedido);
                    const modal = new bootstrap.Modal(document.getElementById('modalEditarPedido'));
                    modal.show();
                })
                .catch(err => {
                    console.error('Error al obtener detalles del pedido:', err);
                    alert('Error al cargar los detalles del pedido');
                });
        });
    }

    // Función para llenar el select de clientes (reutilizable)
    function llenarSelectClientes(selectId) {
        const select = document.getElementById(selectId);
        select.innerHTML = '';
        catalogoClientes.forEach(cliente => {
            const option = document.createElement('option');
            option.value = cliente.id;
            option.textContent = cliente.nombre;
            select.appendChild(option);
        });
    }

    // Función para actualizar todos los selects de productos evitando repetidos
    function actualizarSelectsProductos(contenedorId) {
        const selects = document.querySelectorAll(`#${contenedorId} select[name^='producto_']`);
        const productosSeleccionados = Array.from(selects).map(sel => sel.value).filter(val => val);
        selects.forEach(select => {
            const valorActual = select.value;
            // Limpiar opciones
            select.innerHTML = '<option value="">Seleccione...</option>';
            // Agregar solo los productos que no están seleccionados en otros selects, o el actual
            catalogoProductos.filter(prod => prod.stock > 0 && (!productosSeleccionados.includes(String(prod.id)) || String(prod.id) === valorActual)).forEach(prod => {
                const option = document.createElement('option');
                option.value = prod.id;
                option.textContent = prod.nombre;
                if (String(prod.id) === valorActual) option.selected = true;
                select.appendChild(option);
            });
        });
    }

    // Función para agregar un producto dinámicamente al formulario (crear o editar)
    function agregarProductoFormulario(contenedorId, idx, detalle = null) {
        const contenedor = document.getElementById(contenedorId);
        const row = document.createElement('div');
        row.className = 'row align-items-end mb-2 border p-2 producto-item-modal';

        // Estructura básica del producto
        row.innerHTML = `
            <div class="col-md-3">
                <label>Producto</label>
                <select class="form-control" name="producto_${idx}" data-idx="${idx}" required><option value="">Seleccione...</option></select>
            </div>
            <div class="col-md-2">
                <label>Cantidad</label>
                <input type="number" class="form-control" name="cantidad_${idx}" value="${detalle && detalle.cantidad !== undefined ? detalle.cantidad : ''}" min="1" required oninput="validarNumeroPositivo(this)">
            </div>
            <div class="col-md-2">
                <label>Precio Unitario</label>
                <input type="number" class="form-control" name="precio_${idx}" value="${detalle && detalle.precio_unitario !== undefined ? detalle.precio_unitario : ''}" min="0" step="1" readonly>
            </div><div class="col-md-2">
                <label>Descuento</label>
                <input type="number" class="form-control" name="descuento_${idx}" value="${detalle && detalle.descuento !== undefined ? detalle.descuento : ''}" min="0" step="1" oninput="validarNumeroPositivo(this)">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm btnQuitarProducto">Quitar</button>
            </div>
            <div class="col-12 adicionales_container mt-2"></div>
        `;
        contenedor.appendChild(row);
        //Este bloque de codigo es por si el cliente desea mejor aplicar un descuento
        // <div class="col-md-2">
        //         <label>Descuento</label>
        //         <input type="number" class="form-control" name="descuento_${idx}" value="${detalle && detalle.descuento !== undefined ? detalle.descuento : ''}" min="0" step="0.01" oninput="validarNumeroPositivo(this)">
        //     </div>

        // Llenar select de productos evitando repetidos
        const selectProd = row.querySelector('select');
        // Obtener productos ya seleccionados
        const productosSeleccionados = Array.from(document.querySelectorAll(`#${contenedorId} select[name^='producto_']`))
            .map(sel => sel.value).filter(val => val);
        const productosConStock = catalogoProductos.filter(prod => prod.stock > 0 && !productosSeleccionados.includes(String(prod.id)));
        productosConStock.forEach(prod => {
            const option = document.createElement('option');
            option.value = prod.id;
            option.textContent = prod.nombre;
            if (detalle && prod.id == detalle.producto_id) option.selected = true;
            selectProd.appendChild(option);
        });

        // Modifico el evento de cambio del select de producto para actualizar todos los selects
        selectProd.addEventListener('change', function() {
            const productosSeleccionadosAhora = Array.from(document.querySelectorAll(`#${contenedorId} select[name^='producto_']`))
                .map(sel => sel.value).filter(val => val);
            const repes = productosSeleccionadosAhora.filter((v, i, a) => a.indexOf(v) !== i && v !== '');
            if (repes.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Producto repetido',
                    text: 'Este producto ya fue seleccionado en este pedido. Por favor, elige otro.',
                });
                this.value = '';
                calcularTotalFormulario(contenedorId);
                actualizarSelectsProductos(contenedorId);
                return;
            }
            // Autollenar precio unitario si existe
            const prod = catalogoProductos.find(p => p.id == this.value);
            if (prod) {
                row.querySelector(`[name='precio_${idx}']`).value = prod.precio_venta;
            } else {
                row.querySelector(`[name='precio_${idx}']`).value = '';
            }
            renderAdicionales(this.value);
            calcularTotalFormulario(contenedorId);
            actualizarSelectsProductos(contenedorId);
        });

        // Llenar adicionales DESPUÉS de agregar el row al DOM
        function renderAdicionales(productoId, detalleLocal = detalle) {
            const adicCont = row.querySelector('.adicionales_container');
            adicCont.innerHTML = '';
            // Mostrar todos los adicionales para todos los productos
            const adicionalesDelProducto = catalogoAdicionales;
            if (adicionalesDelProducto.length > 0) {
                adicCont.innerHTML = '<div class="adicionales-titulo">Adicionales</div>';
                adicionalesDelProducto.forEach(adic => {
                    const idCheckbox = `adic_${idx}_${adic.id}`;
                    const checked = detalleLocal && detalleLocal.adicionales && detalleLocal.adicionales.some(a => a.adicional_id == adic.id) ? 'checked' : '';
                    adicCont.innerHTML += `
                      <div class='form-check'>
                        <input type='checkbox' id='${idCheckbox}' name='adicional_${idx}_${adic.id}' value='${adic.id}' ${checked}>
                        <label for='${idCheckbox}'>${adic.nombre} (${formatoCOP(adic.precio_venta)})</label>
                      </div>
                    `;
                });
            }
            // Asignar evento change a los nuevos checkboxes para recalcular el total en tiempo real
            setTimeout(() => {
                adicCont.querySelectorAll("input[type='checkbox']").forEach(chk => {
                    chk.addEventListener('change', function() {
                        calcularTotalFormulario(contenedorId);
                    });
                });
            }, 0);
        }
        renderAdicionales(selectProd.value);

        // Eventos para recalcular total
        row.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('change', function() {
                calcularTotalFormulario(contenedorId);
            });
        });
        // Botón quitar producto
        row.querySelector('.btnQuitarProducto').addEventListener('click', function() {
            row.remove();
            calcularTotalFormulario(contenedorId);
        });
    }

    // Función para calcular el total del formulario (crear o editar)
    function calcularTotalFormulario(contenedorId) {
        const contenedor = document.getElementById(contenedorId);
        let total = 0;
        contenedor.querySelectorAll('.producto-item-modal').forEach(divProd => {
            const cantidad = parseFloat(divProd.querySelector("[name^='cantidad_']").value) || 0;
            const precio = parseFloat(divProd.querySelector("[name^='precio_']").value) || 0;
            const descuento = parseFloat(divProd.querySelector("[name^='descuento_']")?.value || 0);

            // Sumar adicionales seleccionados
            let sumaAdicionales = 0;
            divProd.querySelectorAll(".adicionales_container input[type='checkbox']:checked").forEach(chk => {
                const adic = catalogoAdicionales.find(a => String(a.id) === String(chk.value));
                if (adic && !isNaN(Number(adic.precio_venta))) {
                    sumaAdicionales += Number(adic.precio_venta);
                }
            });

            // Nuevo cálculo: (precio + sumaAdicionales) * cantidad - descuento
            let subtotal = (precio + sumaAdicionales) * cantidad - descuento;

            // Log para depuración
            console.log('Subtotal producto:', precio, 'Suma adicionales:', sumaAdicionales, 'Cantidad:', cantidad, 'Descuento:', descuento, 'Subtotal final:', subtotal);

            total += subtotal;
        });
        // Asignar al input total correspondiente (formato COP)
        if (contenedorId === 'crear_productos_container') {
            document.getElementById('crear_total').value = formatoCOP(total);
        } else {
            document.getElementById('editar_total').value = formatoCOP(total);
        }
        actualizarDescuento();
    }

    // ========== Mejorar lógica para botón 'Agregar Producto' en crear y editar ==========

    // Función para crear o asegurar el botón 'Agregar Producto' en un contenedor
    function asegurarBotonAgregarProducto(contenedorId, modo) {
        const cont = document.getElementById(contenedorId);
        let btn = document.getElementById('btnAgregarProducto_' + contenedorId);
        if (!btn) {
            btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-secondary btn-sm mb-2';
            btn.id = 'btnAgregarProducto_' + contenedorId;
            btn.textContent = 'Agregar Producto';
            btn.onclick = function() {
                // Usar el número de productos actuales como índice único
                const idx = cont.querySelectorAll('.producto-item-modal').length;
                agregarProductoFormulario(contenedorId, idx);
            };
            // Insertar el botón después del contenedor de productos y antes del campo Total
            if (cont.parentNode) {
                const totalInput = cont.parentNode.querySelector('input[name="total"]');
                if (totalInput && totalInput.parentNode) {
                    totalInput.parentNode.parentNode.insertBefore(btn, totalInput.parentNode);
                } else {
                    cont.parentNode.appendChild(btn);
                }
            } else {
                cont.appendChild(btn);
            }
        }
    }

    // ========== CREAR PEDIDO ==========
    const btnCrearPedido = document.getElementById('btnCrearPedido');
    if (btnCrearPedido) {
        btnCrearPedido.addEventListener('click', function() {
            if (!catalogosListos) {
                Swal.fire({
                    icon: 'info',
                    title: 'Cargando datos...',
                    text: 'Por favor espera un momento mientras se cargan los productos y clientes.',
                    showConfirmButton: false,
                    timer: 1200
                });
                // Esperar y reintentar abrir el modal cuando estén listos
                const interval = setInterval(() => {
                    if (catalogosListos) {
                        clearInterval(interval);
                        abrirModalCrearPedido();
                    }
                }, 200);
            } else {
                abrirModalCrearPedido();
            }
        });
    }

    // Nueva función para abrir el modal de crear pedido
    function abrirModalCrearPedido() {
        const modal = new bootstrap.Modal(document.getElementById('modalCrearPedido'));
        modal.show();
        llenarSelectClientes('crear_cliente');
        // Limpiar productos
        const cont = document.getElementById('crear_productos_container');
        cont.innerHTML = '';
        // Agregar primer producto por defecto (vacío)
        agregarProductoFormulario('crear_productos_container', 0, { cantidad: '', precio_unitario: '', descuento: '', producto_id: '', adicionales: [] });
        // Asegurar botón para agregar más productos
        asegurarBotonAgregarProducto('crear_productos_container', 'crear');
        // Reset total y fecha
        document.getElementById('crear_total').value = '';
        document.getElementById('crear_fecha').value = new Date().toISOString().slice(0,16);
        // Limpiar cliente
        document.getElementById('crear_cliente').value = '';
        // Limpiar total pagado y descuento aplicado
        document.getElementById('crear_total_pagado').value = '';
        document.getElementById('crear_descuento').value = '';
        // Calcular total al abrir el modal
        calcularTotalFormulario('crear_productos_container');
    }

    // ========== FORMATO PESO COLOMBIANO ==========
    function formatoCOP(valor) {
        // Mostrar solo el valor entero, sin decimales ni ceros extra
        return valor !== null && valor !== undefined
            ? '$ ' + parseInt(valor).toLocaleString('es-CO')
            : '$ 0';
    }
    function limpiarCOP(valor) {
        // Elimina todo excepto números y coma/punto decimal
        return Number(String(valor).replace(/[^\d,\.]/g, '').replace(/\./g, '').replace(/,/g, '.')) || 0;
    }

    // ========== LÓGICA PARA DESCUENTO AUTOMÁTICO EN CREAR PEDIDO ==========
    const totalInput = document.getElementById('crear_total');
    const totalPagarInput = document.getElementById('crear_total_pagado');
    const descuentoInput = document.getElementById('crear_descuento');

    // Añadir nuevo input para el porcentaje de descuento
    const descuentoPorcentajeInput = document.createElement('input');
    descuentoPorcentajeInput.type = 'number';
    descuentoPorcentajeInput.className = 'form-control';
    descuentoPorcentajeInput.id = 'crear_descuento_porcentaje';
    descuentoPorcentajeInput.placeholder = 'Porcentaje de descuento';
    descuentoPorcentajeInput.min = '0';
    descuentoPorcentajeInput.max = '100';
    descuentoPorcentajeInput.step = '1';
    descuentoPorcentajeInput.value = '0';

    // Insertar el nuevo input antes del input de descuento
    descuentoInput.parentNode.insertBefore(descuentoPorcentajeInput, descuentoInput);

    // Función para actualizar el descuento y el total pagado
    // Si se ingresa un porcentaje de descuento, se calcula el descuento y se actualiza el total pagado
    // Si no se ingresa un porcentaje, se calcula el descuento basado en el total pagado
    function actualizarDescuento() {
        const total = limpiarCOP(totalInput.value);
        const totalPagar = limpiarCOP(totalPagarInput.value);
        const descuentoPorcentaje = parseFloat(descuentoPorcentajeInput.value) || 0;
        let descuento = 0;
        if (descuentoPorcentaje > 0) {
            descuento = total * (descuentoPorcentaje / 100);
            totalPagarInput.value = formatoCOP(total - descuento);
        } else if (totalPagar > 0) {
            // Si el total pagado es mayor al total, el descuento es 0 (no permitido, se validará al enviar)
            descuento = total - totalPagar;
            if (descuento < 0) descuento = 0;
        }
        descuentoInput.value = formatoCOP(descuento);
    }

    if (totalInput && totalPagarInput && descuentoInput) {
        totalInput.addEventListener('input', actualizarDescuento);
        totalPagarInput.addEventListener('input', actualizarDescuento);
        descuentoPorcentajeInput.addEventListener('input', actualizarDescuento);
        totalPagarInput.addEventListener('blur', function() {
            const valor = limpiarCOP(totalPagarInput.value);
            totalPagarInput.value = valor > 0 ? formatoCOP(valor) : '';
        });
        // Inicializar valores
        totalPagarInput.value = '';
        descuentoInput.value = '';
    }

    // ========== EDITAR PEDIDO ==========
    function llenarModalEditarPedido(pedido) {
        document.getElementById('editar_pedido_id').value = pedido.id;
        // Asignar el ID y el nombre del cliente
        document.getElementById('editar_cliente').value = pedido.cliente_id;
        setNombreClienteEditarPorId(pedido.cliente_id);
        // Llenar estado
        document.getElementById('editar_estado').value = pedido.estado;
        // Llenar productos
        const cont = document.getElementById('editar_productos_container');
        cont.innerHTML = '';
        pedido.detalles.forEach((detalle, idx) => {
            agregarProductoFormulario('editar_productos_container', idx, detalle);
        });
        asegurarBotonAgregarProducto('editar_productos_container', 'editar');
        document.getElementById('editar_total').value = formatoCOP(pedido.total);
        if (pedido.total_pagado !== undefined && pedido.total_pagado !== null) {
            document.getElementById('editar_total_pagado').value = formatoCOP(pedido.total_pagado);
        } else {
            document.getElementById('editar_total_pagado').value = '';
        }
        // === CAMBIO: Calcular el descuento como la diferencia entre total y total pagado ===
        let totalNum = Number(pedido.total) || 0;
        let pagadoNum = Number(pedido.total_pagado) || 0;
        let descuentoNum = 0;
        if (totalNum > 0 && pagadoNum > 0 && totalNum >= pagadoNum) {
            descuentoNum = totalNum - pagadoNum;
        }
        document.getElementById('editar_descuento').value = formatoCOP(descuentoNum);
        // === FIN CAMBIO ===
        actualizarDescuentoEditar();
        const fechaFormateada = pedido.fecha ? new Date(pedido.fecha).toISOString().slice(0, 16) : '';
        document.getElementById('editar_fecha').value = fechaFormateada;
        calcularTotalFormulario('editar_productos_container');
    }

    // ========== LÓGICA PARA DESCUENTO AUTOMÁTICO EN EDITAR PEDIDO (con porcentaje) ==========
    const editarTotalInput = document.getElementById('editar_total');
    const editarTotalPagarInput = document.getElementById('editar_total_pagado');
    const editarDescuentoInput = document.getElementById('editar_descuento');
    const editarDescuentoPorcentajeInput = document.getElementById('editar_descuento_porcentaje');

    function actualizarDescuentoEditar() {
        const total = limpiarCOP(editarTotalInput.value);
        const totalPagar = limpiarCOP(editarTotalPagarInput.value);
        const descuentoPorcentaje = parseFloat(editarDescuentoPorcentajeInput?.value) || 0;
        let diferencia = 0;
        if (descuentoPorcentaje > 0) {
            diferencia = total * (descuentoPorcentaje / 100);
            editarDescuentoInput.value = formatoCOP(diferencia);
            editarTotalPagarInput.value = formatoCOP(total - diferencia);
        } else if (totalPagar > 0) {
            diferencia = total - totalPagar;
            if (diferencia < 0) diferencia = 0;
            editarDescuentoInput.value = formatoCOP(diferencia);
        } else {
            editarDescuentoInput.value = '';
        }
    }

    if (editarTotalInput && editarTotalPagarInput && editarDescuentoInput && editarDescuentoPorcentajeInput) {
        editarTotalInput.addEventListener('input', actualizarDescuentoEditar);
        editarTotalPagarInput.addEventListener('input', actualizarDescuentoEditar);
        editarDescuentoPorcentajeInput.addEventListener('input', actualizarDescuentoEditar);
        editarTotalPagarInput.addEventListener('blur', function() {
            const valor = limpiarCOP(editarTotalPagarInput.value);
            editarTotalPagarInput.value = valor > 0 ? formatoCOP(valor) : '';
        });
        // Inicializar valores
        editarTotalPagarInput.value = '';
        editarDescuentoInput.value = '';
    }

    // ================= VALIDACIÓN ADICIONAL: TOTAL PAGADO NO PUEDE SER MAYOR AL TOTAL =====================
    // Validación para crear pedido
    const formCrear = document.getElementById('formCrearPedido');
    if (formCrear) {
        formCrear.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('¡Submit del formulario de crear pedido!');
            // Obtener los valores numéricos del total y total pagado
            const total = limpiarCOP(document.getElementById('crear_total').value);
            const totalPagado = limpiarCOP(document.getElementById('crear_total_pagado').value);
            // Si el total pagado es mayor al total, mostrar error y no enviar
            if (totalPagado > total) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el pago',
                    text: 'El total que va a pagar no puede ser mayor al total.',
                    confirmButtonColor: '#5D54A4'
                });
                return;
            }
            // Recopilar datos del formulario
            const cliente_id = document.getElementById('crear_cliente').value;
            const productos = [];
            const adicionales = {};

            document.querySelectorAll('#crear_productos_container > .producto-item-modal').forEach((divProd, idx) => {
                const producto_id = divProd.querySelector("[name^='producto_']").value;
                const cantidad = divProd.querySelector("[name^='cantidad_']").value;
                const precio_unitario = divProd.querySelector("[name^='precio_']").value;
                const descuento = divProd.querySelector("[name^='descuento_']")?.value || 0;

                if (producto_id && cantidad && precio_unitario) {
                    productos.push({
                        id: producto_id,
                        cantidad: cantidad,
                        precio_unitario: precio_unitario,
                        descuento: descuento
                    });
                }

                // Adicionales
                const adics = [];
                divProd.querySelectorAll(".adicionales_container input[type='checkbox']:checked").forEach(chk => {
                    adics.push({ id: Number(chk.value) });
                });
                if (adics.length > 0) {
                    adicionales[producto_id] = adics;
                }
            });

            // Obtener y formatear la fecha del input (datetime-local) a formato compatible con el backend
            let fechaInput = document.getElementById('crear_fecha').value; // Ejemplo: '2025-07-25T18:13'
            let fechaFormateada = '';
            if (fechaInput) {
                // Convertir a 'YYYY-MM-DD HH:mm:ss'
                const d = new Date(fechaInput);
                const pad = n => n.toString().padStart(2, '0');
                fechaFormateada = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:00`;
            }

            // Obtener el descuento como número (limpiando el formato COP)
            let descuentoStr = document.getElementById('crear_descuento').value;
            let descuentoNum = 0;
            if (descuentoStr) {
                descuentoNum = Number(descuentoStr.replace(/[^\d.-]/g, '')) || 0;
            }

            // Construir el objeto a enviar
            const data = {
                cliente_id: cliente_id,
                productos: productos,
                adicionales: adicionales,
                fecha: fechaFormateada, // Enviar la fecha en formato correcto
                descuento: descuentoNum,
                total: limpiarCOP(document.getElementById('crear_total').value),
                total_pagado: limpiarCOP(document.getElementById('crear_total_pagado').value)
            };

            // Enviar al backend
            fetch('../controllers/pedidoController.php?action=crearPedidoCompleto', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Pedido creado correctamente!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCrearPedido'));
                    modal.hide();
                    cargarPedidos();
                } else if (resp.error && resp.stock_disponible !== undefined) {
                    let cantidadSolicitada = '';
                    if (Array.isArray(productos) && productos.length > 0) {
                        const prod = productos.find(p => p.id == resp.producto_id);
                        cantidadSolicitada = prod ? prod.cantidad : '';
                    } else if (resp.cantidad_solicitada !== undefined) {
                        cantidadSolicitada = resp.cantidad_solicitada;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Stock insuficiente',
                        html: `No hay suficiente stock para <b>${resp.nombre}</b>.<br>
                               Intentaste pedir <b>${cantidadSolicitada}</b> y solo hay <b>${resp.stock_disponible}</b> unidades disponibles.`,
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    console.error('Error al crear pedido:', resp);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: resp.error || 'Ocurrió un error al crear el pedido.'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de red o servidor',
                    text: err.toString()
                });
            });
        });
    }

    // Validación para editar pedido
    const formEditar = document.getElementById('formEditarPedido');
    if (formEditar) {
        formEditar.addEventListener('submit', function(e) {
            // Obtener los valores numéricos del total y total pagado
            const total = limpiarCOP(document.getElementById('editar_total').value);
            const totalPagado = limpiarCOP(document.getElementById('editar_total_pagado').value);
            // Si el total pagado es mayor al total, mostrar error y no enviar
            if (totalPagado > total) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el pago',
                    text: 'El total que va a pagar no puede ser mayor al total.',
                    confirmButtonColor: '#5D54A4'
                });
                return;
            }
            // Recopilar datos del formulario editar
            const pedido_id = document.getElementById('editar_pedido_id') ? document.getElementById('editar_pedido_id').value : null;
            const cliente_id = document.getElementById('editar_cliente').value;
            const estado = document.getElementById('editar_estado').value;
            const productos = [];
            const adicionales = {};

            document.querySelectorAll('#editar_productos_container > .producto-item-modal').forEach((divProd, idx) => {
                const producto_id = divProd.querySelector("[name^='producto_']").value;
                const cantidad = divProd.querySelector("[name^='cantidad_']").value;
                const precio_unitario = divProd.querySelector("[name^='precio_']").value;
                const descuento = divProd.querySelector("[name^='descuento_']")?.value || 0;

                if (producto_id && cantidad && precio_unitario) {
                    productos.push({
                        id: producto_id,
                        cantidad: cantidad,
                        precio_unitario: precio_unitario,
                        descuento: descuento
                    });
                }

                // Adicionales
                const adics = [];
                divProd.querySelectorAll(".adicionales_container input[type='checkbox']:checked").forEach(chk => {
                    adics.push({ id: Number(chk.value) });
                });
                if (adics.length > 0) {
                    adicionales[producto_id] = adics;
                }
            });

            // Log de los selects de producto
            const selects = document.querySelectorAll('#editar_productos_container select[name^="producto_"]');
            selects.forEach((sel, i) => {
                console.log(`Select producto #${i}:`, sel.value, sel);
            });
            // Log del array de productos
            console.log('Array productos a enviar:', productos);

            // Construir el objeto a enviar
            const data = {
                pedido_id: pedido_id,
                cliente_id: cliente_id,
                productos: productos,
                adicionales: adicionales,
                estado: parseInt(estado),
                fecha: document.getElementById('editar_fecha').value,
                total: limpiarCOP(document.getElementById('editar_total').value),
                total_pagado: limpiarCOP(document.getElementById('editar_total_pagado').value)
            };

            // Enviar al backend
            fetch('../controllers/pedidoController.php?action=actualizarPedidoCompleto', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Pedido actualizado correctamente!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarPedido'));
                    modal.hide();
                    cargarPedidos();
                } else if (resp.error && resp.stock_disponible !== undefined) {
                    let cantidadSolicitada = '';
                    if (Array.isArray(productos) && productos.length > 0) {
                        const prod = productos.find(p => p.id == resp.producto_id);
                        cantidadSolicitada = prod ? prod.cantidad : '';
                    } else if (resp.cantidad_solicitada !== undefined) {
                        cantidadSolicitada = resp.cantidad_solicitada;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Stock insuficiente',
                        html: `No hay suficiente stock para <b>${resp.nombre}</b>.<br>
                               Intentaste pedir <b>${cantidadSolicitada}</b> y solo hay <b>${resp.stock_disponible}</b> unidades disponibles.`,
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: resp.error || 'Ocurrió un error al actualizar el pedido.'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de red o servidor',
                    text: err.toString()
                });
            });
        });
    }

    // ================= FIN NUEVA LÓGICA =================

    // ================= PAGINACIÓN DE PEDIDOS =================
    let pedidosGlobal = [];
    let paginaActual = 1;
    const pedidosPorPagina =3;

    // ================= BUSQUEDA POR NOMBRE DE CLIENTE =================
    let terminoBusqueda = "";
    // Escuchar cambios en el input de búsqueda
    document.getElementById('busquedaCliente').addEventListener('input', function(e) {
        terminoBusqueda = e.target.value.toLowerCase();
        paginaActual = 1; // Reiniciar a la primera página al buscar
        renderizarPedidosPaginados();
    });

    // Modifica renderizarPedidosPaginados para filtrar antes de paginar
    function renderizarPedidosPaginados() {
        let listaPedidos = document.getElementById("listaPedidos");
        listaPedidos.innerHTML = '';
        mostrarBotonEditar(false);

        // Filtrar por nombre de cliente si hay búsqueda
        let pedidosFiltrados = pedidosGlobal;
        if (terminoBusqueda) {
            pedidosFiltrados = pedidosGlobal.filter(p =>
                p.cliente_nombre && p.cliente_nombre.toLowerCase().includes(terminoBusqueda)
            );
        }

        const inicio = (paginaActual - 1) * pedidosPorPagina;
        const fin = inicio + pedidosPorPagina;
        const pedidosPagina = pedidosFiltrados.slice(inicio, fin);

        if (pedidosPagina.length > 0) {
            pedidosPagina.forEach(pedido => {
                let listItem = document.createElement("li");
                listItem.className = "list-group-item";
                listItem.dataset.pedidoId = pedido.id;
                listItem.innerHTML = `
                    <strong>Cliente:</strong> ${pedido.cliente_nombre} <br>
                    <strong>Pedido:</strong> #${pedido.id} <br>
                    <strong>Fecha:</strong> ${pedido.fecha ? pedido.fecha : 'No especificada'} <br>
                    <strong>Estado:</strong> ${pedido.estado}
                `;
                listItem.addEventListener("click", function () {
                    mostrarDetallesPedido(pedido.id);
                    mostrarBotonEditar(true);
                });
                listaPedidos.appendChild(listItem);
            });
        } else {
            listaPedidos.innerHTML = '<li class="list-group-item">No hay pedidos disponibles.</li>';
        }
        renderizarControlesPaginacion(pedidosFiltrados.length);
    }

    // Restaurar la función de paginación con botones simples
    function renderizarControlesPaginacion(totalFiltrados = null) {
        let paginacionDiv = document.getElementById('paginacionPedidos');
        if (!paginacionDiv) {
            paginacionDiv = document.createElement('div');
            paginacionDiv.id = 'paginacionPedidos';
            paginacionDiv.className = 'd-flex justify-content-center my-2';
            document.getElementById('listaPedidos').parentNode.appendChild(paginacionDiv);
        }
        paginacionDiv.innerHTML = '';
        const totalPaginas = Math.ceil((totalFiltrados !== null ? totalFiltrados : pedidosGlobal.length) / pedidosPorPagina);
        // Botón anterior
        const btnAnterior = document.createElement('button');
        btnAnterior.className = 'btn btn-outline-primary btn-sm mx-1';
        btnAnterior.textContent = 'Anterior';
        btnAnterior.disabled = paginaActual === 1;
        btnAnterior.onclick = function() {
            if (paginaActual > 1) {
                paginaActual--;
                renderizarPedidosPaginados();
            }
        };
        paginacionDiv.appendChild(btnAnterior);
        // Info de página
        const info = document.createElement('span');
        info.className = 'mx-2 align-self-center';
        info.textContent = `Página ${paginaActual} de ${totalPaginas}`;
        paginacionDiv.appendChild(info);
        // Botón siguiente
        const btnSiguiente = document.createElement('button');
        btnSiguiente.className = 'btn btn-outline-primary btn-sm mx-1';
        btnSiguiente.textContent = 'Siguiente';
        btnSiguiente.disabled = paginaActual === totalPaginas || totalPaginas === 0;
        btnSiguiente.onclick = function() {
            if (paginaActual < totalPaginas) {
                paginaActual++;
                renderizarPedidosPaginados();
            }
        };
        paginacionDiv.appendChild(btnSiguiente);
    }

    // Llamar la función para cargar los pedidos al cargar la página
    cargarPedidos();

    // =================== AUTOCOMPLETADO DE CLIENTES EN CREAR PEDIDO ===================
    const inputNombreCliente = document.getElementById('crear_cliente_nombre');
    const inputIdCliente = document.getElementById('crear_cliente');
    const sugerenciasDiv = document.getElementById('sugerencias_clientes');

    if (inputNombreCliente && inputIdCliente && sugerenciasDiv) {
        inputNombreCliente.addEventListener('input', function() {
            const texto = this.value.toLowerCase();
            sugerenciasDiv.innerHTML = '';
            if (texto.length < 2) {
                sugerenciasDiv.style.display = 'none';
                inputIdCliente.value = '';
                return;
            }
            // Filtrar clientes
            const resultados = catalogoClientes.filter(cliente => 
                cliente.nombre.toLowerCase().includes(texto)
            );
            if (resultados.length === 0) {
                sugerenciasDiv.style.display = 'none';
                inputIdCliente.value = '';
                return;
            }
            // Mostrar sugerencias
            resultados.forEach(cliente => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.textContent = cliente.nombre;
                item.onclick = function() {
                    inputNombreCliente.value = cliente.nombre;
                    inputIdCliente.value = cliente.id;
                    sugerenciasDiv.innerHTML = '';
                    sugerenciasDiv.style.display = 'none';
                };
                sugerenciasDiv.appendChild(item);
            });
            sugerenciasDiv.style.display = 'block';
        });
        // Ocultar sugerencias si se hace click fuera
        document.addEventListener('click', function(e) {
            if (!sugerenciasDiv.contains(e.target) && e.target !== inputNombreCliente) {
                sugerenciasDiv.innerHTML = '';
                sugerenciasDiv.style.display = 'none';
            }
        });
    }

    // =================== AUTOCOMPLETADO DE CLIENTES EN EDITAR PEDIDO ===================
    const inputNombreClienteEditar = document.getElementById('editar_cliente_nombre');
    const inputIdClienteEditar = document.getElementById('editar_cliente');
    const sugerenciasDivEditar = document.getElementById('sugerencias_clientes_editar');

    if (inputNombreClienteEditar && inputIdClienteEditar && sugerenciasDivEditar) {
        inputNombreClienteEditar.addEventListener('input', function() {
            const texto = this.value.toLowerCase();
            sugerenciasDivEditar.innerHTML = '';
            if (texto.length < 2) {
                sugerenciasDivEditar.style.display = 'none';
                inputIdClienteEditar.value = '';
                return;
            }
            // Filtrar clientes
            const resultados = catalogoClientes.filter(cliente => 
                cliente.nombre.toLowerCase().includes(texto)
            );
            if (resultados.length === 0) {
                sugerenciasDivEditar.style.display = 'none';
                inputIdClienteEditar.value = '';
                return;
            }
            // Mostrar sugerencias
            resultados.forEach(cliente => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.textContent = cliente.nombre;
                item.onclick = function() {
                    inputNombreClienteEditar.value = cliente.nombre;
                    inputIdClienteEditar.value = cliente.id;
                    sugerenciasDivEditar.innerHTML = '';
                    sugerenciasDivEditar.style.display = 'none';
                };
                sugerenciasDivEditar.appendChild(item);
            });
            sugerenciasDivEditar.style.display = 'block';
        });
        // Ocultar sugerencias si se hace click fuera
        document.addEventListener('click', function(e) {
            if (!sugerenciasDivEditar.contains(e.target) && e.target !== inputNombreClienteEditar) {
                sugerenciasDivEditar.innerHTML = '';
                sugerenciasDivEditar.style.display = 'none';
            }
        });
    }

    // Al abrir el modal de editar, reiniciar el porcentaje de descuento
    function setNombreClienteEditarPorId(clienteId) {
        if (!clienteId) {
            inputNombreClienteEditar.value = '';
            return;
        }
        const cliente = catalogoClientes.find(c => String(c.id) === String(clienteId));
        if (cliente) {
            inputNombreClienteEditar.value = cliente.nombre;
        } else {
            inputNombreClienteEditar.value = '';
        }
        if (editarDescuentoPorcentajeInput) editarDescuentoPorcentajeInput.value = '0';
    }

}); // Cierre del document.addEventListener('DOMContentLoaded', function() {