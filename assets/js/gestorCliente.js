// Inicialización de DataTable y registro de clientes vía AJAX
// Este código sigue la lógica de pedidos, pero para clientes

$(document).ready(function() {
    // Inicializa el DataTable con AJAX usando los campos reales de la base de datos
    var tabla = $('#tablaClientes').DataTable({
        ajax: {
            url: '../controllers/clienteController.php?action=obtener',
            dataSrc: 'clientes'
        },
        responsive: true,
        columns: [
            { data: 'id' },
            { data: 'nombre' },
            { data: 'cedula' },
            { data: 'direccion' },
            { data: 'telefono' },
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

    // Maneja el envío del formulario para crear cliente
    $('#formCrearCliente').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Validar campos requeridos
        if (!data.nombre || !data.cedula || !data.direccion || !data.telefono) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor, complete todos los campos requeridos'
            });
            return;
        }

        fetch('../controllers/clienteController.php?action=crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Cliente registrado correctamente!',
                    showConfirmButton: false,
                    timer: 1500
                });
                this.reset();
                tabla.ajax.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: resp.error || 'Error al registrar el cliente'
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
    $('#tablaClientes').on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        
        // Obtener datos del cliente por ID
        fetch(`../controllers/clienteController.php?action=obtenerPorId&id=${id}`)
            .then(res => res.json())
            .then(resp => {
                if (resp.success) {
                    const cliente = resp.cliente;
                    // Llenar el formulario de edición con los datos reales
                    $('#editar_id').val(cliente.id);
                    $('#editar_nombre').val(cliente.nombre);
                    $('#editar_cedula').val(cliente.cedula);
                    $('#editar_direccion').val(cliente.direccion);
                    $('#editar_telefono').val(cliente.telefono);
                    // Mostrar la modal de edición
                    const modal = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
                    modal.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: resp.error || 'Error al obtener datos del cliente'
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
    $('#formEditarCliente').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Validar campos requeridos
        if (!data.nombre || !data.cedula || !data.direccion || !data.telefono) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor, complete todos los campos requeridos'
            });
            return;
        }

        fetch('../controllers/clienteController.php?action=actualizar', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Cliente actualizado correctamente!',
                    showConfirmButton: false,
                    timer: 1500
                });
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente'));
                modal.hide();
                tabla.ajax.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: resp.error || 'Error al actualizar el cliente'
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
  