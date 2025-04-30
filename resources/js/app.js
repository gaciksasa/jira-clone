
import './bootstrap';

// Import Bootstrap with Popper (which is required for dropdowns)
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dropdowns using direct instantiation
    document.querySelectorAll('.dropdown-toggle').forEach(function(element) {
        new bootstrap.Dropdown(element);
    });
    
    // Or alternatively, enable all dropdowns at once
    // var dropdownTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'))
    // dropdownTriggerList.map(function (dropdownTriggerEl) {
    //   return new bootstrap.Dropdown(dropdownTriggerEl)
    // });
});