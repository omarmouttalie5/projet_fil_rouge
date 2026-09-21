document.addEventListener('DOMContentLoaded', () => {
    // Generic Modal Opener/Closer
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'flex';
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'none';
    };

    // Close modal when clicking outside content area
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };
});