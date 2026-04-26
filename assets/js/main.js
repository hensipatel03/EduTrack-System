/**
 * College Management System - Main JavaScript
 * Modern interactive features and utilities
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    initializePopovers();
    initializeDatePickers();
    initializeFormValidation();
    initializeSidebarActive();
    initializeAnimations();
    initializeAutoHideAlerts();
});

/**
 * Initialize Bootstrap Tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize Bootstrap Popovers
 */
function initializePopovers() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

/**
 * Initialize Date Pickers
 */
function initializeDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.classList.add('form-control');
    });
}

/**
 * Initialize Form Validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

/**
 * Highlight active sidebar link
 */
function initializeSidebarActive() {
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
    
    sidebarLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href)) {
            link.classList.add('active', 'fw-bold');
            link.style.backgroundColor = '#f0f0f0';
        }
    });
}

/**
 * Add animation classes to elements on scroll
 */
function initializeAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.5s ease forwards';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.card').forEach(card => {
        observer.observe(card);
    });
}

/**
 * Auto-hide alerts after 5 seconds
 */
function initializeAutoHideAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (el) {
        setTimeout(function () {
            const bsAlert = new bootstrap.Alert(el);
            bsAlert.close();
        }, 5000);
    });
}

/**
 * Simple table search feature
 */
function filterTable(tableId, searchText) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');
    const searchLower = searchText.toLowerCase();

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchLower) ? '' : 'none';
    });
}

/**
 * Logout with confirmation
 */
function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}

/**
 * Delete confirmation dialog
 */
function confirmDelete(itemName = 'this item') {
    return confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`);
}

/**
 * Show success toast notification
 */
function showSuccessToast(message, duration = 3000) {
    showToast(message, 'success', duration);
}

/**
 * Show error toast notification
 */
function showErrorToast(message, duration = 3000) {
    showToast(message, 'danger', duration);
}

/**
 * Show warning toast notification
 */
function showWarningToast(message, duration = 3000) {
    showToast(message, 'warning', duration);
}

/**
 * Generic toast notification
 */
function showToast(message, type = 'info', duration = 3000) {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.maxWidth = '400px';
    toast.style.zIndex = '9999';
    toast.style.animation = 'slideDown 0.3s ease';
    
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    toastContainer.appendChild(toast);
    
    if (duration > 0) {
        setTimeout(() => toast.remove(), duration);
    }
}

/**
 * Create toast container if it doesn't exist
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.style.position = 'fixed';
    container.style.top = '0';
    container.style.right = '0';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

/**
 * Format date to readable format
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Format time to readable format
 */
function formatTime(dateString) {
    const options = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
    return new Date(dateString).toLocaleTimeString('en-US', options);
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showSuccessToast('Copied to clipboard!');
    }).catch(err => {
        showErrorToast('Failed to copy to clipboard');
    });
}

/**
 * Validate email
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate phone number
 */
function isValidPhoneNumber(phone) {
    const re = /^[0-9]{10}$/;
    return re.test(phone.replace(/\D/g, ''));
}

/**
 * Export table to CSV
 */
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = [];
    
    table.querySelectorAll('tr').forEach(row => {
        const rowData = [];
        row.querySelectorAll('td, th').forEach(cell => {
            rowData.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
        });
        rows.push(rowData.join(','));
    });

    const csv = rows.join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
}

/**
 * Print element
 */
function printElement(elementId) {
    const printWindow = window.open('', '', 'height=400,width=800');
    const element = document.getElementById(elementId);
    if (element) {
        printWindow.document.write(element.innerHTML);
        printWindow.document.close();
        printWindow.print();
    }
}

/**
 * Get URL parameter by name
 */
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    const results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Export functions for use in global scope
window.collegeSystem = {
    showSuccessToast,
    showErrorToast,
    showWarningToast,
    confirmDelete,
    confirmLogout,
    copyToClipboard,
    formatDate,
    formatTime,
    getUrlParameter,
    exportTableToCSV,
    printElement,
    isValidEmail,
    isValidPhoneNumber,
    filterTable
};

console.log('College Management System - Script loaded successfully!');