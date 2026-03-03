/**
 * Main JavaScript File for 70K Savings & Loans System
 * Handles client-side validation, interactions, and utilities
 * Author: Development Team
 * Last Updated: March 2026
 */

// ============================================
// DOCUMENT READY - Initialize on page load
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('70K Savings & Loans System loaded');
    
    // Initialize tooltips and popovers
    initializeBootstrapElements();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Setup event listeners
    setupEventListeners();
});

// ============================================
// BOOTSTRAP ELEMENTS INITIALIZATION
// ============================================

/**
 * Initialize Bootstrap tooltips and popovers
 * Makes interactive elements more user-friendly
 */
function initializeBootstrapElements() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// ============================================
// FORM VALIDATION
// ============================================

/**
 * Initialize HTML5 form validation with Bootstrap styling
 * Prevents form submission if validation fails
 */
function initializeFormValidation() {
    // Get all forms that need validation
    const forms = document.querySelectorAll('.needs-validation');
    
    // Loop through each form
    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            // Check if form is valid
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Add Bootstrap validation class
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Setup custom event listeners for forms
 */
function setupEventListeners() {
    // Add event listeners for numeric input fields
    const numericInputs = document.querySelectorAll('input[type="number"]');
    numericInputs.forEach(input => {
        input.addEventListener('input', function() {
            validateNumericInput(this);
        });
    });

    // Add event listeners for email fields
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateEmail(this);
        });
    });

    // Add event listeners for required fields
    const requiredInputs = document.querySelectorAll('input[required], textarea[required], select[required]');
    requiredInputs.forEach(input => {
        input.addEventListener('change', function() {
            validateRequired(this);
        });
    });
}

// ============================================
// VALIDATION FUNCTIONS
// ============================================

/**
 * Validate numeric input
 * Ensures only positive numbers are entered
 * @param {HTMLElement} input - Input element to validate
 */
function validateNumericInput(input) {
    const value = parseFloat(input.value);
    
    // Check if value is a valid number and positive
    if (isNaN(value) || value < 0) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return false;
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        return true;
    }
}

/**
 * Validate email format
 * @param {HTMLElement} input - Email input element
 */
function validateEmail(input) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const email = input.value.trim();
    
    if (!emailRegex.test(email)) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return false;
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        return true;
    }
}

/**
 * Validate required field is not empty
 * @param {HTMLElement} input - Input element to validate
 */
function validateRequired(input) {
    const value = input.value.trim();
    
    if (value === '') {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return false;
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        return true;
    }
}

/**
 * Validate phone number format
 * Accepts various phone formats
 * @param {string} phone - Phone number to validate
 */
function validatePhoneNumber(phone) {
    const phoneRegex = /^[\d\s\-\+\(\)]+$/;
    return phoneRegex.test(phone) && phone.replace(/\D/g, '').length >= 10;
}

// ============================================
// CURRENCY FORMATTING
// ============================================

/**
 * Format number as currency (Ugandan Shillings)
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return '₤ ' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Parse currency string to number
 * @param {string} currencyString - Currency formatted string
 * @returns {number} Parsed number value
 */
function parseCurrency(currencyString) {
    return parseFloat(currencyString.replace(/[^0-9.-]+/g, ''));
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Show success notification/alert
 * @param {string} message - Message to display
 * @param {number} duration - Duration in milliseconds (default 5000)
 */
function showSuccess(message, duration = 5000) {
    showAlert('success', message, duration);
}

/**
 * Show error notification/alert
 * @param {string} message - Error message to display
 * @param {number} duration - Duration in milliseconds (default 5000)
 */
function showError(message, duration = 5000) {
    showAlert('danger', message, duration);
}

/**
 * Show warning notification/alert
 * @param {string} message - Warning message to display
 * @param {number} duration - Duration in milliseconds (default 5000)
 */
function showWarning(message, duration = 5000) {
    showAlert('warning', message, duration);
}

/**
 * Show info notification/alert
 * @param {string} message - Info message to display
 * @param {number} duration - Duration in milliseconds (default 5000)
 */
function showInfo(message, duration = 5000) {
    showAlert('info', message, duration);
}

/**
 * Generic alert/notification function
 * @param {string} type - Alert type (success, danger, warning, info)
 * @param {string} message - Message to display
 * @param {number} duration - Duration in milliseconds
 */
function showAlert(type, message, duration = 5000) {
    // Create alert element
    const alertId = 'alert-' + Date.now();
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    // Create container if it doesn't exist
    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container';
        alertContainer.style.position = 'fixed';
        alertContainer.style.top = '80px';
        alertContainer.style.right = '20px';
        alertContainer.style.zIndex = '9999';
        alertContainer.style.maxWidth = '400px';
        document.body.appendChild(alertContainer);
    }

    // Add alert to container
    alertContainer.insertAdjacentHTML('beforeend', alertHTML);

    // Auto-remove alert after duration
    if (duration > 0) {
        setTimeout(function() {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                alertElement.remove();
            }
        }, duration);
    }
}

/**
 * Format date to readable format
 * @param {string} dateString - Date string (YYYY-MM-DD)
 * @returns {string} Formatted date (DD Month YYYY)
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString + 'T00:00:00').toLocaleDateString('en-US', options);
}

/**
 * Calculate days between two dates
 * @param {string} date1 - First date (YYYY-MM-DD)
 * @param {string} date2 - Second date (YYYY-MM-DD)
 * @returns {number} Number of days between dates
 */
function daysBetween(date1, date2) {
    const d1 = new Date(date1);
    const d2 = new Date(date2);
    const diffTime = Math.abs(d2 - d1);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
}

/**
 * Check if date is in the past
 * @param {string} dateString - Date to check (YYYY-MM-DD)
 * @returns {boolean} True if date is in the past
 */
function isPastDate(dateString) {
    const date = new Date(dateString);
    return date < new Date();
}

/**
 * Sanitize HTML to prevent XSS
 * @param {string} text - Text to sanitize
 * @returns {string} Sanitized text
 */
function sanitizeHTML(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Show loading spinner
 * Useful for async operations
 */
function showLoading() {
    const loader = document.getElementById('loading-spinner');
    if (loader) {
        loader.style.display = 'block';
    }
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    const loader = document.getElementById('loading-spinner');
    if (loader) {
        loader.style.display = 'none';
    }
}

/**
 * Enable/disable form buttons
 * @param {boolean} disabled - True to disable, false to enable
 */
function toggleFormButtons(disabled) {
    const buttons = document.querySelectorAll('button[type="submit"]');
    buttons.forEach(button => {
        button.disabled = disabled;
        if (disabled) {
            button.classList.add('disabled');
        } else {
            button.classList.remove('disabled');
        }
    });
}

// ============================================
// TABLE UTILITIES
// ============================================

/**
 * Sort table by column
 * @param {string} columnIndex - Index of column to sort
 * @param {string} tableId - ID of table element
 */
function sortTable(columnIndex, tableId = 'dataTable') {
    const table = document.getElementById(tableId);
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const ascending = !table.dataset.ascending;

    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();

        // Try to sort as numbers first
        if (!isNaN(aValue) && !isNaN(bValue)) {
            return ascending ? parseFloat(aValue) - parseFloat(bValue) : parseFloat(bValue) - parseFloat(aValue);
        }

        // Otherwise sort as strings
        return ascending ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
    });

    // Update table data attribute
    table.dataset.ascending = ascending;

    // Reinsert rows in sorted order
    const tbody = table.querySelector('tbody');
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Filter table rows based on search input
 * @param {string} inputId - ID of search input element
 * @param {string} tableId - ID of table element
 * @param {number} columnIndex - Column index to search (optional)
 */
function filterTable(inputId, tableId = 'dataTable', columnIndex = null) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    const filter = input.value.toLowerCase();
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        let match = false;

        if (columnIndex !== null) {
            // Search specific column
            const cell = row.cells[columnIndex];
            if (cell && cell.textContent.toLowerCase().includes(filter)) {
                match = true;
            }
        } else {
            // Search all columns
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(filter)) {
                    match = true;
                }
            });
        }

        row.style.display = match ? '' : 'none';
    });
}

/**
 * Export table data to CSV
 * @param {string} tableId - ID of table to export
 * @param {string} filename - Filename for downloaded CSV
 */
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    let csv = [];

    // Get table rows
    const rows = table.querySelectorAll('tr');
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        let rowData = [];

        cols.forEach(col => {
            rowData.push('"' + col.textContent.trim().replace(/"/g, '""') + '"');
        });

        csv.push(rowData.join(','));
    });

    // Create download link
    const csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
    const link = document.createElement('a');
    link.setAttribute('href', csvContent);
    link.setAttribute('download', filename);
    link.click();
}

/**
 * Print table
 * @param {string} tableId - ID of table to print
 */
function printTable(tableId) {
    const table = document.getElementById(tableId);
    const printWindow = window.open('', '', 'height=600,width=800');

    printWindow.document.write('<html><head><title>Print Table</title>');
    printWindow.document.write('<style>table { border-collapse: collapse; width: 100%; }');
    printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
    printWindow.document.write('th { background-color: #f2f2f2; }');
    printWindow.document.write('</style></head><body>');
    printWindow.document.write(table.outerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// ============================================
// AJAX UTILITIES
// ============================================

/**
 * Make AJAX request
 * @param {string} method - HTTP method (GET, POST, etc.)
 * @param {string} url - Request URL
 * @param {object} data - Request data
 * @param {function} callback - Callback function
 */
function ajaxRequest(method, url, data, callback) {
    const xhr = new XMLHttpRequest();

    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const response = JSON.parse(xhr.responseText);
                callback(null, response);
            } catch (e) {
                callback(null, xhr.responseText);
            }
        } else {
            callback('Error: ' + xhr.status);
        }
    };

    xhr.onerror = function() {
        callback('Request failed');
    };

    // Format data if it's an object
    let body = '';
    if (method === 'POST' && typeof data === 'object') {
        body = new URLSearchParams(data).toString();
    }

    xhr.send(body);
}

/**
 * Fetch API wrapper for GET requests
 * @param {string} url - Request URL
 * @returns {Promise}
 */
async function fetchData(url) {
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        showError('Failed to load data');
    }
}

/**
 * Fetch API wrapper for POST requests
 * @param {string} url - Request URL
 * @param {object} data - Data to send
 * @returns {Promise}
 */
async function postData(url, data) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        return await response.json();
    } catch (error) {
        console.error('Post error:', error);
        showError('Failed to process request');
    }
}

// ============================================
// CONFIRMATION DIALOGS
// ============================================

/**
 * Confirm delete action with user
 * @param {string} message - Confirmation message
 * @returns {boolean} True if user confirms
 */
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

/**
 * Bootstrap Modal Confirmation
 * @param {string} title - Modal title
 * @param {string} message - Confirmation message
 * @param {function} onConfirm - Callback if confirmed
 */
function showConfirmModal(title, message, onConfirm) {
    const modalId = 'confirmModal';
    let modal = document.getElementById(modalId);

    if (!modal) {
        // Create modal if it doesn't exist
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="modalMessage"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmBtn">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        modal = document.getElementById(modalId);
    }

    // Set content
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;

    // Remove old event listener and add new one
    const confirmBtn = document.getElementById('confirmBtn');
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

    newConfirmBtn.addEventListener('click', function() {
        bootstrap.Modal.getInstance(modal).hide();
        onConfirm();
    });

    // Show modal
    new bootstrap.Modal(modal).show();
}

// ============================================
// LOCAL STORAGE UTILITIES
// ============================================

/**
 * Save data to localStorage
 * @param {string} key - Storage key
 * @param {any} value - Value to store
 */
function saveToStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (error) {
        console.error('Storage error:', error);
    }
}

/**
 * Get data from localStorage
 * @param {string} key - Storage key
 * @returns {any} Stored value or null
 */
function getFromStorage(key) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : null;
    } catch (error) {
        console.error('Storage error:', error);
        return null;
    }
}

/**
 * Remove data from localStorage
 * @param {string} key - Storage key
 */
function removeFromStorage(key) {
    try {
        localStorage.removeItem(key);
    } catch (error) {
        console.error('Storage error:', error);
    }
}

// ============================================
// LOGGER UTILITY
// ============================================

/**
 * Log message to console with timestamp
 * @param {string} level - Log level (log, info, warn, error)
 * @param {string} message - Message to log
 */
function log(level, message) {
    const timestamp = new Date().toLocaleTimeString();
    console.log(`[${timestamp}] [${level.toUpperCase()}] ${message}`);
}

// Export functions for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        formatCurrency,
        showSuccess,
        showError,
        validateEmail,
        filterTable,
        exportTableToCSV
    };
}
