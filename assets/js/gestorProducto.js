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
            { data: 'descuento' },
            { data: 'stock' },
            { data: 'estado' }
        ],
        language: {
            url: '../assets/DataTables/es-ES.json'
        }
    });

    // Maneja el envío del formulario para crear producto
    $('#formCrearProducto').on('submit', function(e) {
        e.preventDefault();
        
        // Validación básica de datos
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Validar campos requeridos
        if (!data.nombre || !data.precio_base || !data.stock) {
            alert('Por favor, complete todos los campos requeridos');
            return;
        }

        // Validar que el precio y stock sean números positivos
        if (isNaN(data.precio_base) || data.precio_base <= 0) {
            alert('El precio debe ser un número positivo');
            return;
        }

        if (isNaN(data.stock) || data.stock < 0) {
            alert('El stock debe ser un número no negativo');
            return;
        }

        fetch('../controllers/productoController.php?action=crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`Error HTTP: ${res.status}`);
            }
            return res.json();
        })
        .then(resp => {
            if (resp.success) {
                alert('¡Producto registrado correctamente!');
                this.reset();
                tabla.ajax.reload();
            } else {
                alert('Error al registrar producto: ' + (resp.error || 'Error desconocido'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error al procesar la solicitud: ' + err.message);
        });
    });
});
