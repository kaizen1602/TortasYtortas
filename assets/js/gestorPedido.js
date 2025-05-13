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
                        <div><strong>Producto:</strong> ${detalle.producto_nombre}</div>
                        <div><strong>Cantidad:</strong> ${detalle.cantidad}</div>
                        <div><strong>Precio Unitario:</strong> $${detalle.precio_unitario}</div>
                        <div><strong>Subtotal:</strong> $${detalle.subtotal}</div>
                        <!-- Mostrar adicionales si existen -->
                        ${detalle.adicionales && detalle.adicionales.length > 0 ? `
                        <div class='mt-2'><strong>Adicionales:</strong>
                            <ul style='margin-bottom:0;'>
                                ${detalle.adicionales.map(adic => `<li>${adic.adicional_nombre} ($${adic.precio})</li>`).join('')}
                            </ul>
                        </div>
                        ` : ''}
                    </div>
                    `;
                });
                detalleDiv.innerHTML = `
                <div class="card card-detalle-pedido shadow-sm p-4 mb-4">
                  <h2 class="mb-3 text-primary-emphasis">Pedido #${detalles.id}</h2>
                  <ul class="list-unstyled mb-4">
                    <li><strong>Cliente:</strong> ${detalles.cliente_nombre}</li>
                    <li><strong>Cédula:</strong> ${detalles.cedula || 'No especificada'}</li>
                    <li><strong>Dirección:</strong> ${detalles.direccion || 'No especificada'}</li>
                    <li><strong>Teléfono:</strong> ${detalles.telefono || 'No especificado'}</li>
                    <li><strong>Total:</strong> $${detalles.total || '0.00'}</li>
                    <li><strong>Fecha:</strong> ${detalles.fecha || 'No especificada'}</li>
                    <li><strong>Estado:</strong> <span class="badge bg-secondary">${detalles.estado == 1 ? 'Activo' : 'Inactivo'}</span></li>
                  </ul>
                  <h4 class="mb-3 text-secondary">Productos:</h4>
                  ${productosHTML}
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

    // Evento para el botón Crear Pedido
    document.getElementById('btnCrearPedido').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalCrearPedido'));
        modal.show();
    });

    // Variables globales para almacenar datos de catálogo
    let catalogoClientes = [];
    let catalogoProductos = [];
    let catalogoAdicionales = [];
    let pedidoActual = null;

    // Función para cargar catálogos (clientes, productos, adicionales)
    function cargarCatalogos() {
        // Clientes
        fetch('../controllers/pedidoController.php?action=getClientes')
            .then(res => res.json())
            .then(data => { catalogoClientes = data; });
        // Productos
        fetch('../controllers/pedidoController.php?action=getProductos')
            .then(res => res.json())
            .then(data => { catalogoProductos = data; });
        // Adicionales
        fetch('../controllers/pedidoController.php?action=getAdicionales')
            .then(res => res.json())
            .then(data => { catalogoAdicionales = data; });
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

    // Función para agregar un producto dinámicamente al formulario (crear o editar)
    function agregarProductoFormulario(contenedorId, idx, detalle = null) {
        const contenedor = document.getElementById(contenedorId);
        const row = document.createElement('div');
        row.className = 'row align-items-end mb-2 border p-2 producto-item-modal';

        row.innerHTML = `
            <div class="col-md-3">
                <label>Producto</label>
                <select class="form-control" name="producto_${idx}" data-idx="${idx}" required></select>
            </div>
            <div class="col-md-2">
                <label>Cantidad</label>
                <input type="number" class="form-control" name="cantidad_${idx}" value="${detalle ? detalle.cantidad : 1}" min="1" required>
            </div>
            <div class="col-md-2">
                <label>Precio Unitario</label>
                <input type="number" class="form-control" name="precio_${idx}" value="${detalle ? detalle.precio_unitario : ''}" min="0" step="0.01" required>
            </div>
            <div class="col-md-2">
                <label>Descuento</label>
                <input type="number" class="form-control" name="descuento_${idx}" value="${detalle ? (detalle.descuento || 0) : 0}" min="0" step="0.01">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm btnQuitarProducto">Quitar</button>
            </div>
            <div class="col-12 adicionales_container mt-2"></div>
        `;
        // Llenar select de productos
        const selectProd = row.querySelector('select');
        catalogoProductos.forEach(prod => {
            const option = document.createElement('option');
            option.value = prod.id;
            option.textContent = prod.nombre;
            if (detalle && prod.id == detalle.producto_id) option.selected = true;
            selectProd.appendChild(option);
        });
        // Llenar adicionales
        function renderAdicionales(productoId) {
            const adicCont = row.querySelector('.adicionales_container');
            adicCont.innerHTML = '';
            // CAMBIO: Mostrar todos los adicionales para todos los productos (sin filtrar por producto_id)
            // const adicionalesDelProducto = catalogoAdicionales.filter(a => a.producto_id == productoId);
            const adicionalesDelProducto = catalogoAdicionales; // <-- Ahora se muestran todos
            if (adicionalesDelProducto.length > 0) {
                adicCont.innerHTML = '<div class="adicionales-titulo">Adicionales</div>';
                adicionalesDelProducto.forEach(adic => {
                    const idCheckbox = `adic_${idx}_${adic.id}`;
                    const checked = detalle && detalle.adicionales && detalle.adicionales.some(a => a.adicional_id == adic.id) ? 'checked' : '';
                    adicCont.innerHTML += `
                      <div class='form-check'>
                        <input type='checkbox' id='${idCheckbox}' name='adicional_${idx}_${adic.id}' value='${adic.id}' ${checked}>
                        <label for='${idCheckbox}'>${adic.nombre} ($${adic.precio})</label>
                      </div>
                    `;
                });
            }
        }
        // Inicializar adicionales
        renderAdicionales(selectProd.value);
        // Cambiar adicionales al cambiar producto
        selectProd.addEventListener('change', function() {
            renderAdicionales(this.value);
            // Autollenar precio unitario si existe
            const prod = catalogoProductos.find(p => p.id == this.value);
            if (prod) {
                row.querySelector(`[name='precio_${idx}']`).value = prod.precio_base;
            }
            calcularTotalFormulario(contenedorId);
        });
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
        contenedor.appendChild(row);
    }

    // Función para calcular el total del formulario (crear o editar)
    function calcularTotalFormulario(contenedorId) {
        const contenedor = document.getElementById(contenedorId);
        let total = 0;
        contenedor.querySelectorAll('.producto-item-modal').forEach(divProd => {
            const cantidad = parseFloat(divProd.querySelector("[name^='cantidad_']").value) || 0;
            const precio = parseFloat(divProd.querySelector("[name^='precio_']").value) || 0;
            const descuento = parseFloat(divProd.querySelector("[name^='descuento_']").value) || 0;
            let subtotal = (precio * cantidad) - descuento;
            // Sumar adicionales seleccionados
            divProd.querySelectorAll(".adicionales_container input[type='checkbox']:checked").forEach(chk => {
                const adic = catalogoAdicionales.find(a => a.id == chk.value);
                if (adic) subtotal += parseFloat(adic.precio);
            });
            total += subtotal;
        });
        // Asignar al input total correspondiente
        if (contenedorId === 'crear_productos_container') {
            document.getElementById('crear_total').value = total.toFixed(2);
        } else {
            document.getElementById('editar_total').value = total.toFixed(2);
        }
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
                agregarProductoFormulario(contenedorId, cont.querySelectorAll('div.mb-3').length);
            };
            cont.parentNode.insertBefore(btn, cont);
        }
    }

    // ========== CREAR PEDIDO ==========
    const btnCrearPedido = document.getElementById('btnCrearPedido');
    if (btnCrearPedido) {
        btnCrearPedido.addEventListener('click', function() {
            llenarSelectClientes('crear_cliente');
            // Limpiar productos
            const cont = document.getElementById('crear_productos_container');
            cont.innerHTML = '';
            // Agregar primer producto por defecto
            agregarProductoFormulario('crear_productos_container', 0);
            // Asegurar botón para agregar más productos
            asegurarBotonAgregarProducto('crear_productos_container', 'crear');
            // Reset total y fecha
            document.getElementById('crear_total').value = '';
            document.getElementById('crear_fecha').value = new Date().toISOString().slice(0,16);
        });
    }

    // ========== EDITAR PEDIDO ==========
    function llenarModalEditarPedido(pedido) {
        // Asignar el ID del pedido al campo oculto
        document.getElementById('editar_pedido_id').value = pedido.id;
        // Llenar select de clientes
        llenarSelectClientes('editar_cliente');
        document.getElementById('editar_cliente').value = pedido.cliente_id;
        // Llenar estado
        document.getElementById('editar_estado').value = pedido.estado;
        // Llenar productos
        const cont = document.getElementById('editar_productos_container');
        cont.innerHTML = '';
        pedido.detalles.forEach((detalle, idx) => {
            agregarProductoFormulario('editar_productos_container', idx, detalle);
        });
        // Asegurar botón para agregar más productos
        asegurarBotonAgregarProducto('editar_productos_container', 'editar');
        // Total y fecha
        document.getElementById('editar_total').value = pedido.total;
        // Formatear la fecha para asegurar que sea compatible con el input datetime-local
        const fechaFormateada = pedido.fecha ? new Date(pedido.fecha).toISOString().slice(0, 16) : '';
        document.getElementById('editar_fecha').value = fechaFormateada;
        // Calcular total al abrir el modal
        calcularTotalFormulario('editar_productos_container');
    }

    // Validar y enviar formulario editar
    const formEditar = document.getElementById('formEditarPedido');
    if (formEditar) {
        formEditar.addEventListener('submit', function(e) {
            e.preventDefault();
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
                const descuento = divProd.querySelector("[name^='descuento_']").value || 0;

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
                    adics.push({ id: chk.value });
                });
                if (adics.length > 0) {
                    adicionales[producto_id] = adics;
                }
            });

            // Construir el objeto a enviar
            const data = {
                pedido_id: pedido_id,
                cliente_id: cliente_id,
                productos: productos,
                adicionales: adicionales,
                estado: parseInt(estado),
                fecha: document.getElementById('editar_fecha').value,
                total: document.getElementById('editar_total').value
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Stock insuficiente',
                        html: `No hay suficiente stock para <b>${resp.nombre}</b>.<br>
                               Intentaste pedir <b>${productos.find(p => p.id == resp.producto_id)?.cantidad}</b> y solo hay <b>${resp.stock_disponible}</b> unidades disponibles.`,
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

    // Validar y enviar formulario crear
    const formCrear = document.getElementById('formCrearPedido');
    if (formCrear) {
        formCrear.addEventListener('submit', function(e) {
            e.preventDefault();

            // Recopilar datos del formulario
            const cliente_id = document.getElementById('crear_cliente').value;
            const productos = [];
            const adicionales = {};

            document.querySelectorAll('#crear_productos_container > .producto-item-modal').forEach((divProd, idx) => {
                const producto_id = divProd.querySelector("[name^='producto_']").value;
                const cantidad = divProd.querySelector("[name^='cantidad_']").value;
                const precio_unitario = divProd.querySelector("[name^='precio_']").value;
                const descuento = divProd.querySelector("[name^='descuento_']").value || 0;

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
                    adics.push({ id: chk.value });
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

            // Construir el objeto a enviar
            const data = {
                cliente_id: cliente_id,
                productos: productos,
                adicionales: adicionales,
                fecha: fechaFormateada // Enviar la fecha en formato correcto
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
                    console.log('Error de stock:', resp);
                    Swal.fire({
                        icon: 'error',
                        title: 'Stock insuficiente',
                        html: `No hay suficiente stock para <b>${resp.nombre}</b>.<br>
                               Intentaste pedir <b>${productos.find(p => p.id == resp.producto_id)?.cantidad}</b> y solo hay <b>${resp.stock_disponible}</b> unidades disponibles.`,
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
});
