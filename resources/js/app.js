import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    // Set up CSRF token for all AJAX requests
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
});
