document.addEventListener('DOMContentLoaded', function () {
    // Cek semua inputan dan hapus error message ketika user mengetik
    document.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.addEventListener('input', function () {
            // Hapus error class
            el.classList.remove('border-red-400');
            const errorMessage = el.nextElementSibling;
            if (errorMessage && errorMessage.classList.contains('text-red-500')) {
                errorMessage.textContent = '';
            }
        });
    });
});
