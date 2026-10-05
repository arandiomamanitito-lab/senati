/**
 * PLATAFORMA DE GESTIÓN DE EMPLEO Y POSTULACIONES
 * Script Global del Sistema (main.js)
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Manejo del menú lateral responsive en móviles
    const toggleBtn = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.panel-sidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });

        // Cerrar al hacer clic fuera del sidebar en móvil
        document.addEventListener('click', function (e) {
            if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // 2. Cierre automático de mensajes Flash después de 5 segundos
    const flashAlert = document.getElementById('alertaFlash');
    if (flashAlert) {
        setTimeout(function () {
            flashAlert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            flashAlert.style.opacity = '0';
            flashAlert.style.transform = 'translateY(-10px)';
            setTimeout(() => flashAlert.remove(), 500);
        }, 5000);
    }
});

// Función de confirmación para acciones críticas (eliminar, pausar, desactivar)
function confirmarAccion(mensaje, event) {
    if (!confirm(mensaje || '¿Estás seguro de realizar esta acción?')) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        return false;
    }
    return true;
}

// Control de modales accesible globalmente
function abrirModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}
