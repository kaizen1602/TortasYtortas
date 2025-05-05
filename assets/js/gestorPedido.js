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
                let listaPedidos = document.getElementById("listaPedidos");
                listaPedidos.innerHTML = ''; // Limpiar lista de pedidos
                mostrarBotonEditar(false); // Ocultar botón Editar
                // Verifica si hay pedidos
                if (pedidos && pedidos.length > 0) {
                    pedidos.forEach(pedido => {
                        let listItem = document.createElement("li");
                        listItem.className = "list-group-item";
                        listItem.dataset.pedidoId = pedido.id;
                        listItem.innerHTML = `
                            <strong>Cliente:</strong> ${pedido.cliente_nombre} <br>
                            <strong>Pedido:</strong> #${pedido.id} <br>
                            <strong>Fecha:</strong> ${pedido.fecha_pedido ? pedido.fecha_pedido : 'No especificada'} <br>
                            <strong>Estado:</strong> ${pedido.estado}
                        `;
                        // Agregar evento de clic para mostrar los detalles
                        listItem.addEventListener("click", function () {
                            mostrarDetallesPedido(pedido.id);
                            mostrarBotonEditar(true); // Mostrar botón Editar
                        });
                        listaPedidos.appendChild(listItem);
                    });
                } else {
                    listaPedidos.innerHTML = '<li class="list-group-item">No hay pedidos disponibles.</li>';
                }
            })
            .catch(error => {
                console.error("Error al cargar los pedidos:", error);
                let listaPedidos = document.getElementById("listaPedidos");
                listaPedidos.innerHTML = '<li class="list-group-item text-danger">Error al cargar los pedidos. Por favor, intente nuevamente.</li>';
                mostrarBotonEditar(false);
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

                detalleDiv.innerHTML = `
                    <h4>Pedido #${detalles.id}</h4>
                    <p><strong>Cliente:</strong> ${detalles.cliente_nombre}</p>
                    <p><strong>Cédula:</strong> ${detalles.cedula || 'No especificada'}</p>
                    <p><strong>Dirección:</strong> ${detalles.direccion || 'No especificada'}</p>
                    <p><strong>Teléfono:</strong> ${detalles.telefono || 'No especificado'}</p>
                    <p><strong>Total:</strong> $${detalles.total || '0.00'}</p>
                    <p><strong>Fecha:</strong> ${detalles.fecha || 'No especificada'}</p>
                    <p><strong>Estado:</strong> ${detalles.estado == 1 ? 'Activo' : 'Inactivo'}</p>
                    <h5>Productos:</h5>
                    ${detalles.detalles.length > 0 ? `
                        <ul class="list-group">
                            ${detalles.detalles.map(detalle => `
                                <li class="list-group-item">
                                    <strong>Producto:</strong> ${detalle.producto_nombre} <br>
                                    <strong>Cantidad:</strong> ${detalle.cantidad} <br>
                                    <strong>Precio Unitario:</strong> $${detalle.precio_unitario} <br>
                                    <strong>Subtotal:</strong> $${detalle.subtotal}
                                    ${detalle.adicionales && detalle.adicionales.length > 0 ? `
                                        <br><strong>Adicionales:</strong>
                                        <ul class="list-group mt-2">
                                            ${detalle.adicionales.map(adicional => `
                                                <li class="list-group-item">
                                                    ${adicional.adicional_nombre} ($${adicional.precio})
                                                </li>
                                            `).join('')}
                                        </ul>
                                    ` : ''}
                                </li>
                            `).join('')}
                        </ul>
                    ` : '<p>No hay productos en este pedido.</p>'}
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
            // Obtener el ID del pedido seleccionado (de la variable global o del DOM)
            const detalleDiv = document.getElementById('pedidoDetalle');
            const pedidoId = detalleDiv.querySelector('h4')?.textContent?.split('#')[1]?.trim();
            if (!pedidoId) return;
            // Traer los datos del pedido
            fetch(`../controllers/pedidoController.php?action=getDetallesPedido&pedido_id=${pedidoId}`)
                .then(res => res.json())
                .then(pedido => {
                    pedidoActual = pedido;
                    llenarModalEditarPedido(pedido);
                    const modal = new bootstrap.Modal(document.getElementById('modalEditarPedido'));
                    modal.show();
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
        const divProd = document.createElement('div');
        divProd.className = 'mb-3 border p-2';
        divProd.innerHTML = `
            <label>Producto</label>
            <select class='form-control mb-1' name='producto_${idx}' data-idx='${idx}' required></select>
            <label>Cantidad</label>
            <input type='number' class='form-control mb-1' name='cantidad_${idx}' value='${detalle ? detalle.cantidad : 1}' min='1' required>
            <label>Precio Unitario</label>
            <input type='number' class='form-control mb-1' name='precio_${idx}' value='${detalle ? detalle.precio_unitario : ''}' min='0' step='0.01' required>
            <label>Descuento</label>
            <input type='number' class='form-control mb-1' name='descuento_${idx}' value='${detalle ? (detalle.descuento || 0) : 0}' min='0' step='0.01'>
            <button type='button' class='btn btn-danger btn-sm mb-2 btnQuitarProducto'>Quitar</button>
            <div class='adicionales_container'></div>
        `;
        // Llenar select de productos
        const selectProd = divProd.querySelector('select');
        catalogoProductos.forEach(prod => {
            const option = document.createElement('option');
            option.value = prod.id;
            option.textContent = prod.nombre;
            if (detalle && prod.id == detalle.producto_id) option.selected = true;
            selectProd.appendChild(option);
        });
        // Llenar adicionales
        function renderAdicionales(productoId) {
            const adicCont = divProd.querySelector('.adicionales_container');
            adicCont.innerHTML = '';
            const adicionalesDelProducto = catalogoAdicionales.filter(a => a.producto_id == productoId);
            if (adicionalesDelProducto.length > 0) {
                adicCont.innerHTML = '<label>Adicionales</label>';
                adicionalesDelProducto.forEach(adic => {
                    const checked = detalle && detalle.adicionales && detalle.adicionales.some(a => a.adicional_id == adic.id) ? 'checked' : '';
                    adicCont.innerHTML += `<div class='form-check'><input class='form-check-input' type='checkbox' name='adicional_${idx}_${adic.id}' value='${adic.id}' ${checked}> ${adic.nombre} ($${adic.precio})</div>`;
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
                divProd.querySelector(`[name='precio_${idx}']`).value = prod.precio_base;
            }
            calcularTotalFormulario(contenedorId);
        });
        // Eventos para recalcular total
        divProd.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('change', function() {
                calcularTotalFormulario(contenedorId);
            });
        });
        // Botón quitar producto
        divProd.querySelector('.btnQuitarProducto').addEventListener('click', function() {
            divProd.remove();
            calcularTotalFormulario(contenedorId);
        });
        contenedor.appendChild(divProd);
    }

    // Función para calcular el total del formulario (crear o editar)
    function calcularTotalFormulario(contenedorId) {
        const contenedor = document.getElementById(contenedorId);
        let total = 0;
        contenedor.querySelectorAll('div.mb-3').forEach(divProd => {
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
        document.getElementById('editar_fecha').value = pedido.fecha ? pedido.fecha.replace(' ', 'T').slice(0,16) : '';
    }

    // Validar y enviar formulario editar
    const formEditar = document.getElementById('formEditarPedido');
    if (formEditar) {
        formEditar.addEventListener('submit', function(e) {
            e.preventDefault();
            // Aquí puedes agregar validaciones adicionales si lo deseas
            // ...
            // Enviar datos al backend (puedes usar fetch o AJAX)
            alert('Pedido actualizado (simulado). Implementa el envío al backend aquí.');
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

            document.querySelectorAll('#crear_productos_container > div.mb-3').forEach((divProd, idx) => {
                const producto_id = divProd.querySelector("[name^='producto_']").value;
                const cantidad = divProd.querySelector("[name^='cantidad_']").value;
                const precio_unitario = divProd.querySelector("[name^='precio_']").value;
                const descuento = divProd.querySelector("[name^='descuento_']").value || 0;

                productos.push({
                    id: producto_id,
                    cantidad: cantidad,
                    precio_unitario: precio_unitario,
                    descuento: descuento
                });

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
                cliente_id: cliente_id,
                productos: productos,
                adicionales: adicionales
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
                    alert('¡Pedido creado correctamente!');
                    // Cerrar modal y recargar pedidos
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCrearPedido'));
                    modal.hide();
                    cargarPedidos();
                } else {
                    alert('Error al crear pedido: ' + (resp.error || ''));
                }
            })
            .catch(err => {
                alert('Error de red o servidor: ' + err);
            });
        });
    }

    // ================= FIN NUEVA LÓGICA =================

    // Llamar la función para cargar los pedidos al cargar la página
    cargarPedidos();
});
