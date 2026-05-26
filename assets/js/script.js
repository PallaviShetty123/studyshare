/* ===== StudyShare JavaScript ===== */

/**
 * Toggle like/unlike for a note
 * @param {HTMLElement} button - The like button element
 * @param {number} noteId - The note ID
 */
function toggleLike(button, noteId) {
    // Prevent double clicks
    if (button.disabled) return;
    button.disabled = true;

    fetch('../user/like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'note_id=' + encodeURIComponent(noteId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update like count
            const likeCount = button.querySelector('.like-count');
            likeCount.textContent = data.likes;
            
            // Toggle liked state
            if (data.liked) {
                button.classList.add('liked');
            } else {
                button.classList.remove('liked');
            }
            
            // Show success feedback
            showNotification('Like updated!', 'success');
        } else {
            showNotification(data.error || 'Failed to update like', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    })
    .finally(() => {
        button.disabled = false;
    });
}

/**
 * Show a notification message
 * @param {string} message - The message to show
 * @param {string} type - The notification type (success, error, info)
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        background-color: ${getNotificationColor(type)};
        color: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        animation: slideInRight 0.3s ease;
    `;

    document.body.appendChild(notification);

    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/**
 * Get notification color based on type
 */
function getNotificationColor(type) {
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#6366f1',
        warning: '#f59e0b'
    };
    return colors[type] || colors.info;
}

/**
 * Add animation styles
 */
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(30px);
        }
    }

    /* Like button animation */
    .like-btn {
        position: relative;
    }

    .like-btn .heart {
        display: inline-block;
        transition: transform 0.3s ease;
    }

    .like-btn.liked .heart {
        animation: heartBeat 0.4s ease;
    }

    @keyframes heartBeat {
        0%, 100% {
            transform: scale(1);
        }
        25% {
            transform: scale(1.3);
        }
        50% {
            transform: scale(1.1);
        }
    }

    /* Smooth transitions */
    .nav-item {
        transition: all 0.3s ease;
    }

    .btn-primary, .btn-secondary, .btn-danger, .download-btn {
        transition: all 0.3s ease;
    }

    /* Focus states for accessibility */
    button:focus, a:focus {
        outline: 2px solid #6366f1;
        outline-offset: 2px;
    }
`;
document.head.appendChild(style);

/**
 * Debounce function for search
 */
function debounce(func, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

/**
 * Format date in readable format
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Initialize tooltips (if needed)
 */
function initTooltips() {
    const elements = document.querySelectorAll('[data-tooltip]');
    elements.forEach(el => {
        el.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            tooltip.style.cssText = `
                position: absolute;
                background-color: #1f2937;
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 0.5rem;
                font-size: 0.85rem;
                white-space: nowrap;
                z-index: 1000;
                pointer-events: none;
            `;
            document.body.appendChild(tooltip);
            
            this.addEventListener('mouseleave', () => tooltip.remove());
        });
    });
}

/**
 * Initialize page on load
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('StudyShare loaded');
    
    // Initialize tooltips if any exist
    initTooltips();
    
    // Initialize form validation if needed
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Add any custom form validation here if needed
        });
    });
});

/**
 * Smooth scroll for anchor links
 */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

/**
 * Handle export to CSV (for admin)
 */
function exportToCSV(data, filename = 'export.csv') {
    let csv = '';
    
    // Add headers
    const headers = Object.keys(data[0]);
    csv += headers.join(',') + '\n';
    
    // Add data rows
    data.forEach(row => {
        csv += headers.map(header => {
            const value = row[header];
            // Escape quotes and wrap in quotes if contains comma
            return typeof value === 'string' && value.includes(',') 
                ? `"${value.replace(/"/g, '""')}"` 
                : value;
        }).join(',') + '\n';
    });
    
    // Create blob and download
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Confirm before delete
 */
function confirmDelete(message = 'Are you sure you want to delete this?') {
    return confirm(message);
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Copied to clipboard!', 'success');
        }).catch(() => {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    showNotification('Copied to clipboard!', 'success');
}

// Export functions for use in modules
window.toggleLike = toggleLike;
window.showNotification = showNotification;
window.exportToCSV = exportToCSV;
window.confirmDelete = confirmDelete;
window.copyToClipboard = copyToClipboard;
window.formatDate = formatDate;
window.isValidEmail = isValidEmail;
window.debounce = debounce;
