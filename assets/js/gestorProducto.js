// Inicialización de DataTable y registro de productos vía AJAX
// Este código sigue la lógica de pedidos, pero para productos


$(document).ready(function() {
    // Inicializa el DataTable con AJAX
    var tabla = $('#tablaProductos').DataTable({
        ajax: {
            url: '../controllers/productoController.php?action=obtener',
            dataSrc: 'productos'
        },
        columns: [
            { data: 'id' },
            { data: 'nombre' },
            { data: 'precio_base' },
            { data: 'precio_venta' },
            { data: 'descuento' },
            { data: 'stock' },
            { 
                data: 'estado',
                render: function(data, type, row) {
                    return data == 1 ? 'Activo' : 'Inactivo';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-primary btn-sm btn-editar" data-id="${row.id}">
                            <i class="bi bi-pencil-square"></i> 
                        </button>
                    `;
                }
            }
        ],
        language: {
            url: '../assets/DataTables/es-ES.json'
        }
    });

    // ================= VALIDACIONES PERSONALIZADAS =====================
    function validarNombreProducto(nombre) {
        // Solo letras, espacios y acentos, entre 2 y 50 caracteres
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/;
        return regex.test(nombre);
    }
    function validarNumeroPositivo(input) {
        // Si es un input HTML
        if (input instanceof HTMLElement && typeof input.value !== 'undefined') {
            if (isNaN(input.value) || Number(input.value) < 0) {
                input.setCustomValidity('Debe ser un número positivo');
            } else {
                input.setCustomValidity('');
            }
        } else {
            // Si es un valor (por ejemplo, para validaciones en JS)
            return !isNaN(input) && Number(input) >= 0;
        }
    }

    // Maneja el envío del formulario para crear producto
    $('#formCrearProducto').on('submit', function(e) {
        e.preventDefault();
        
        // Validación básica de datos
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Validar campos requeridos
        if (!data.nombre || !data.precio_base ||!data.precio_venta|| !data.stock) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor, complete todos los campos requeridos'
            });
            return;
        }

        // Validaciones personalizadas
        if (!validarNombreProducto(data.nombre)) {
            Swal.fire({
                icon: 'error',
                title: 'Nombre inválido',
                text: 'El nombre solo debe contener letras y espacios (2-50 caracteres, sin números ni caracteres especiales)'
            });
            return;
        }
        if (!validarNumeroPositivo(data.precio_base) || data.precio_base <= 0 || !validarNumeroPositivo(data.precio_venta) || data.precio_venta <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Precio inválido',
                text: 'El precio base y el precio de venta deben ser números positivos'
            });
            return;
        }
        if (!validarNumeroPositivo(data.descuento)) {
            Swal.fire({
                icon: 'error',
                title: 'Descuento inválido',
                text: 'El descuento debe ser un número mayor o igual a 0'
            });
            return;
        }
        if (!validarNumeroPositivo(data.stock)) {
            Swal.fire({
                icon: 'error',
                title: 'Stock inválido',
                text: 'El stock debe ser un número mayor o igual a 0'
            });
            return;
        }

        console.log('Voy a enviar el pedido al backend:', data);

        fetch('../controllers/productoController.php?action=crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Producto registrado correctamente!',
                    showConfirmButton: false,
                    timer: 1500
                });
                this.reset();
                tabla.ajax.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: resp.error || 'Error al registrar el producto'
                });
            }
        })
        .catch(err => {
            console.error('Error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al procesar la solicitud'
            });
        });
    });

    // Evento para el botón editar
    $('#tablaProductos').on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        
        // Obtener datos del producto
        fetch(`../controllers/productoController.php?action=obtenerPorId&id=${id}`)
            .then(res => res.json())
            .then(resp => {
                if (resp.success) {
                    const producto = resp.producto;
                    
                    // Llenar el formulario de edición
                    $('#editar_id').val(producto.id);
                    $('#editar_nombre').val(producto.nombre);
                    $('#editar_precio_base').val(producto.precio_base);
                    $('#editar_precio_venta').val(producto.precio_venta);
                    $('#editar_descuento').val(producto.descuento);
                    $('#editar_stock').val(producto.stock);
                    
                    // Mostrar la modal
                    const modal = new bootstrap.Modal(document.getElementById('modalEditarProducto'));
                    modal.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: resp.error || 'Error al obtener datos del producto'
                    });
                }
            })
            .catch(err => {
                console.error('Error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la solicitud'
                });
            });
    });

    // Manejar el envío del formulario de edición
    $('#formEditarProducto').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Validaciones
        if (!data.nombre || !data.precio_base || !data.stock) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor, complete todos los campos requeridos'
            });
            return;
        }

        if (!validarNombreProducto(data.nombre)) {
            Swal.fire({
                icon: 'error',
                title: 'Nombre inválido',
                text: 'El nombre solo debe contener letras y espacios (2-50 caracteres, sin números ni caracteres especiales)'
            });
            return;
        }

        if (!validarNumeroPositivo(data.precio_base) || parseFloat(data.precio_base) <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Precio base inválido',
                text: 'El precio base debe ser un número positivo mayor a 0'
            });
            return;
        }
        if (!validarNumeroPositivo(data.precio_venta) || parseFloat(data.precio_venta) <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Precio de venta inválido',
                text: 'El precio de venta debe ser un número positivo mayor a 0'
            });
            return;
        }
        if (parseFloat(data.precio_venta) < parseFloat(data.precio_base)) {
            Swal.fire({
                icon: 'error',
                title: 'Precio de venta menor al base',
                text: 'El precio de venta no puede ser menor que el precio base'
            });
            return;
        }
        if (!validarNumeroPositivo(data.descuento)) {
            Swal.fire({
                icon: 'error',
                title: 'Descuento inválido',
                text: 'El descuento debe ser un número mayor o igual a 0'
            });
            return;
        }
        if (!validarNumeroPositivo(data.stock)) {
            Swal.fire({
                icon: 'error',
                title: 'Stock inválido',
                text: 'El stock debe ser un número mayor o igual a 0'
            });
            return;
        }

        fetch('../controllers/productoController.php?action=actualizar', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Producto actualizado correctamente!',
                    showConfirmButton: false,
                    timer: 1500
                });
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarProducto'));
                modal.hide();
                tabla.ajax.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: resp.error || 'Error al actualizar el producto'
                });
            }
        })
        .catch(err => {
            console.error('Error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al procesar la solicitud'
            });
        });
    });
});
