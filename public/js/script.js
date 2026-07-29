// Place for future JavaScript enhancements
console.log('NIA Asset Monitoring System loaded.');

// Automatically show any toasts that are present
document.addEventListener('DOMContentLoaded', function() {
    var toastEls = document.querySelectorAll('.toast');
    toastEls.forEach(function(el) {
        var toast = new bootstrap.Toast(el);
        toast.show();
    });
});