// Inicialización de DataTable y registro de clientes vía AJAX
// Este código sigue la lógica de pedidos, pero para clientes

$(function() {
    // Inicializa el DataTable con AJAX
    var tabla = $('#tablaClientes').DataTable({
        ajax: {
            url: '../controllers/clienteController.php?action=obtener',
            dataSrc: function(json) {
                console.log('Respuesta del backend:', json);
                return json.clientes;
            }
        },
        columns: [
            { data: 'id' },
            { data: 'nombre' },
            { data: 'cedula' },
            { data: 'direccion' },
            { data: 'telefono' }
        ],
        language: {
            url: '../assets/DataTables/es-ES.json' // Archivo de idioma español local
        }
    });

    // Maneja el envío del formulario para crear cliente
    $('#formCrearCliente').on('submit', function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
          fetch('../controllers/clienteController.php?action=crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
          })
          .then(res => res.json())
          .then(resp => {
            if (resp.success) {
              alert('¡Cliente registrado correctamente!');
                this.reset();
                tabla.ajax.reload();
            } else {
              alert('Error al registrar cliente: ' + (resp.error || ''));
            }
          })
          .catch(err => {
            alert('Error de red o servidor: ' + err);
          });
    });
});
  