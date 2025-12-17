// ✅ Keep lightweight essentials
import './bootstrap';
import './scroll';
import './lga';



// ✅ Lazy load SweetAlert2
window.Swal = {
    fire: async (options) => {
        const Swal = (await import('sweetalert2')).default;
        return Swal.fire(options);
    },
    confirm: async (title, text, confirmText = 'Yes', cancelText = 'No') => {
        const Swal = (await import('sweetalert2')).default;
        return Swal.fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText
        });
    },
    success: async (title, text) => {
        const Swal = (await import('sweetalert2')).default;
        return Swal.fire({
            title,
            text,
            icon: 'success'
        });
    },
    error: async (title, text) => {
        const Swal = (await import('sweetalert2')).default;
        return Swal.fire({
            title,
            text,
            icon: 'error'
        });
    }
};

// ✅ Lazy load features based on what's on the page
document.addEventListener('DOMContentLoaded', () => {
    // Load Trix only if there's a rich text editor
    if (document.querySelector('trix-editor')) {
        import('trix').then(() => {
            console.log('✅ Trix editor loaded');
        });
    }

    // Load message notifications only if user is logged in
    // Check for auth user indicator on page
    if (window.Laravel?.userId) {
        import('./message-notification.js').then(() => {
            console.log('✅ Message notifications initialized');
        });
    }
});
