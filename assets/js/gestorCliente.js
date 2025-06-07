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

    // ================= VALIDACIONES PERSONALIZADAS =====================
    function validarNombre(nombre) {
        // Solo letras, espacios y acentos, entre 2 y 50 caracteres
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/;
        return regex.test(nombre);
    }
    function validarCedula(cedula) {
        // Solo números, entre 5 y 15 dígitos
        const regex = /^\d{5,15}$/;
        return regex.test(cedula);
    }
    function validarDireccion(direccion) {
        // Letras, números, espacios y algunos signos básicos, mínimo 3 caracteres
        const regex = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.,#-]{3,100}$/;
        return regex.test(direccion);
    }
    function validarTelefono(telefono) {
        // Solo números, entre 7 y 15 dígitos
        const regex = /^\d{7,15}$/;
        return regex.test(telefono);
    }

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
        // Validaciones personalizadas
        if (!validarNombre(data.nombre)) {
            Swal.fire({
                icon: 'error',
                title: 'Nombre inválido',
                text: 'El nombre solo debe contener letras y espacios (2-50 caracteres, sin números ni caracteres especiales)'
            });
            return;
        }
        if (!validarCedula(data.cedula)) {
            Swal.fire({
                icon: 'error',
                title: 'Cédula inválida',
                text: 'La cédula debe contener solo números (5-15 dígitos)'
            });
            return;
        }
        if (!validarDireccion(data.direccion)) {
            Swal.fire({
                icon: 'error',
                title: 'Dirección inválida',
                text: 'La dirección debe tener al menos 3 caracteres y solo puede contener letras, números, espacios y . , # -'
            });
            return;
        }
        if (!validarTelefono(data.telefono)) {
            Swal.fire({
                icon: 'error',
                title: 'Teléfono inválido',
                text: 'El teléfono debe contener solo números (7-15 dígitos)'
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
        // Validaciones personalizadas
        if (!validarNombre(data.nombre)) {
            Swal.fire({
                icon: 'error',
                title: 'Nombre inválido',
                text: 'El nombre solo debe contener letras y espacios (2-50 caracteres, sin números ni caracteres especiales)'
            });
            return;
        }
        if (!validarCedula(data.cedula)) {
            Swal.fire({
                icon: 'error',
                title: 'Cédula inválida',
                text: 'La cédula debe contener solo números (5-15 dígitos)'
            });
            return;
        }
        if (!validarDireccion(data.direccion)) {
            Swal.fire({
                icon: 'error',
                title: 'Dirección inválida',
                text: 'La dirección debe tener al menos 3 caracteres y solo puede contener letras, números, espacios y . , # -'
            });
            return;
        }
        if (!validarTelefono(data.telefono)) {
            Swal.fire({
                icon: 'error',
                title: 'Teléfono inválido',
                text: 'El teléfono debe contener solo números (7-15 dígitos)'
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
  