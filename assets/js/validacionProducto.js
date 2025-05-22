function validarNumeroPositivo(input) {
    // Remover caracteres no numéricos excepto punto decimal
    input.value = input.value.replace(/[^0-9.]/g, '');
    
    // Asegurar que solo haya un punto decimal
    const parts = input.value.split('.');
    if (parts.length > 2) {
        input.value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Convertir a número y validar
    const valor = parseFloat(input.value);
    if (isNaN(valor) || valor < 0) {
        input.value = '';
        Swal.fire({
            icon: 'error',
            title: 'Valor inválido',
            text: 'Por favor ingrese un número positivo'
        });
    }
}

// Funciones de validación para productos
function validarPrecio(precio) {
    // Validar que el precio sea un número positivo
    return !isNaN(precio) && parseFloat(precio) > 0;
}

function validarStock(stock) {
    // Validar que el stock sea un número entero no negativo
    return !isNaN(stock) && Number.isInteger(parseFloat(stock)) && parseFloat(stock) >= 0;
}

function validarDescuento(descuento) {
    // Validar que el descuento esté entre 0 y 100
    return !isNaN(descuento) && parseFloat(descuento) >= 0 && parseFloat(descuento) <= 100;
}

function validarNombreProducto(nombre) {
    // Validar que el nombre no esté vacío y tenga una longitud razonable
    return nombre.trim().length > 0 && nombre.trim().length <= 100;
}

// Maneja el envío del formulario para crear producto
$('#formCrearProducto').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Validaciones
    if (!validarNombreProducto(data.nombre)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el nombre',
            text: 'El nombre del producto no puede estar vacío y debe tener máximo 100 caracteres',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    if (!validarPrecio(data.precio_base)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el precio base',
            text: 'El precio base debe ser un número positivo',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    if (!validarPrecio(data.precio_venta)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el precio de venta',
            text: 'El precio de venta debe ser un número positivo',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    if (!validarDescuento(data.descuento)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el descuento',
            text: 'El descuento debe ser un número entre 0 y 100',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }
    
    if (!validarStock(data.stock)) {
        Swal.fire({
            icon: 'error',
            title: 'Error en el stock',
            text: 'El stock debe ser un número entero no negativo',
            confirmButtonColor: '#5D54A4'
        });
        return;
    }

    // Si todas las validaciones pasan, enviar el formulario
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
                text: resp.error || 'Error al registrar el producto',
                confirmButtonColor: '#5D54A4'
            });
        }
    })
    .catch(err => {
        console.error('Error:', err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al procesar la solicitud',
            confirmButtonColor: '#5D54A4'
        });
    });
});

// Validación en tiempo real
$('#nombre').on('input', function() {
    if (!validarNombreProducto(this.value)) {
        this.setCustomValidity('El nombre del producto no puede estar vacío y debe tener máximo 100 caracteres');
    } else {
        this.setCustomValidity('');
    }
});

$('#precio_base').on('input', function() {
    if (!validarPrecio(this.value)) {
        this.setCustomValidity('El precio base debe ser un número positivo');
    } else {
        this.setCustomValidity('');
    }
});

$('#precio_venta').on('input', function() {
    if (!validarPrecio(this.value)) {
        this.setCustomValidity('El precio de venta debe ser un número positivo');
    } else {
        this.setCustomValidity('');
    }
});

$('#descuento').on('input', function() {
    if (!validarDescuento(this.value)) {
        this.setCustomValidity('El descuento debe ser un número entre 0 y 100');
    } else {
        this.setCustomValidity('');
    }
});

$('#stock').on('input', function() {
    if (!validarStock(this.value)) {
        this.setCustomValidity('El stock debe ser un número entero no negativo');
    } else {
        this.setCustomValidity('');
    }
});