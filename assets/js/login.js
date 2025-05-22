// Espera a que el DOM esté cargado
// Este script maneja el login por AJAX y muestra mensajes con SweetAlert

document.addEventListener('DOMContentLoaded', function() {
    // Selecciona el formulario de login y el div de error
    const form = document.querySelector('.login');
    const errorDiv = document.querySelector('.error-message');

    // Escucha el evento submit del formulario
    form.addEventListener('submit', async function(e) {
        e.preventDefault(); // Previene el envío tradicional del formulario
        if (errorDiv) errorDiv.textContent = '';

        // Obtiene los valores de los campos
        const usuario = form.usuario.value;
        const password = form.password.value;

        try {
            // Realiza la petición AJAX al controlador de login
            const response = await fetch('http://localhost/TORTASYTORTAS/controllers/loginController.php?action=login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ usuario, password })
            });
            const data = await response.json();
            if (data.success) {
                // Si el login es exitoso, redirige a la vista de pedidos
                window.location.href = data.redirect;
            } else {
                // Si hay error, muestra SweetAlert con el mensaje
                mostrarErrorSwal(data.error || 'Error desconocido');
            }
        } catch (err) {
            // Si hay error de conexión, muestra SweetAlert
            mostrarErrorSwal('Error de conexión con el servidor');
        }
    });

    // Función para mostrar errores usando SweetAlert
    function mostrarErrorSwal(msg) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: msg,
                confirmButtonColor: '#5D54A4'
            });
        } else if (errorDiv) {
            errorDiv.textContent = msg;
            errorDiv.style.display = 'block';
        } else {
            alert(msg);
        }
    }
}); 