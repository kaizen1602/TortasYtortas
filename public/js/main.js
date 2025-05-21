// Función para manejar el menú responsive
function toggleMenu() {
    const menu = document.querySelector('.nav-menu');
    menu.classList.toggle('active');
}

// Función para ajustar tablas en dispositivos móviles
function adjustTables() {
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        if (window.innerWidth < 768) {
            table.classList.add('table-responsive');
        } else {
            table.classList.remove('table-responsive');
        }
    });
}

// Función para ajustar imágenes
function adjustImages() {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.style.maxWidth = '100%';
        img.style.height = 'auto';
    });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Ajustar tablas e imágenes al cargar
    adjustTables();
    adjustImages();

    // Ajustar al cambiar el tamaño de la ventana
    window.addEventListener('resize', function() {
        adjustTables();
        adjustImages();
    });

    // Manejar clics en el botón del menú
    const menuButton = document.querySelector('.menu-toggle');
    if (menuButton) {
        menuButton.addEventListener('click', toggleMenu);
    }
}); 