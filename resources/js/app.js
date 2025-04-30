// resources/js/app.js
import './bootstrap';

// Initialize Bootstrap properly
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Initialize all dropdowns
document.addEventListener('DOMContentLoaded', function() {
    const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    const dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
});