function formatoCOP(valor) {
    return valor !== null && valor !== undefined
        ? valor.toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0, maximumFractionDigits: 0 })
        : '$ 0';
}

// Inicialización de DataTable y registro de adicionales vía AJAX

$(document).ready(function() {
    // Inicializa el DataTable con AJAX
    var tabla = $('#tablaAdicionales').DataTable({
        ajax: {
            url: '../controllers/adicionalController.php?action=obtener',
            dataSrc: 'adicionales'
        },
        columns: [
            { data: 'id' },
            { data: 'nombre' },
            { 
                data: 'precio', 
                title: 'Precio Base',
                render: function(data) {
                    return formatoCOP(Number(data));
                }
            },
            { 
                data: 'precio_venta', 
                title: 'Precio Venta',
                render: function(data) {
                    return formatoCOP(Number(data));
                }
            },
            { data: 'stock', title: 'Stock' },
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

    // Maneja el envío del formulario para crear adicional
    $('#formCrearAdicional').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        if (!data.nombre || !data.precio) {
            alert('Por favor, complete todos los campos requeridos');
            return;
        }
        if (isNaN(data.precio) || data.precio <= 0) {
            alert('El precio debe ser un número positivo');
            return;
        }
        fetch('../controllers/adicionalController.php?action=crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Adicional registrado correctamente!',
                    showConfirmButton: false,
                    timer: 1500
                });
                this.reset();
                tabla.ajax.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: resp.error || 'Error al registrar el adicional'
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
    $('#tablaAdicionales').on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        fetch(`../controllers/adicionalController.php?action=obtener`, {
            method: 'GET'
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.adicionales) {
                const adicional = resp.adicionales.find(a => a.id == id);
                if (adicional) {
                    $('#editar_id').val(adicional.id);
                    $('#editar_nombre').val(adicional.nombre);
                    $('#editar_precio_base').val(adicional.precio);
                    $('#editar_precio_venta').val(adicional.precio_venta);
                    $('#editar_stock').val(adicional.stock);
                    const modal = new bootstrap.Modal(document.getElementById('modalEditarProducto'));
                    modal.show();
                }
            }
        });
    });

    // Manejar el envío del formulario de edición
    $('#formEditarProducto').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        if (!data.nombre || !data.precio || !data.precio_venta || data.stock === undefined || data.stock === "") {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor, complete todos los campos requeridos'
            });
            return;
        }
        if (isNaN(data.precio) || data.precio <= 0 || isNaN(data.precio_venta) || data.precio_venta <= 0 || isNaN(data.stock) || data.stock < 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Verifique que los valores sean correctos y positivos'
            });
            return;
        }
        fetch('../controllers/adicionalController.php?action=actualizar', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: data.id,
                nombre: data.nombre,
                precio: data.precio,
                precio_venta: data.precio_venta,
                stock: data.stock,
                producto_id: 1
            })
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Adicional actualizado correctamente!',
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
                    text: resp.error || 'Error al actualizar el adicional'
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
