<?php
$session = Session::getInstance();
$alerts = [
    'success' => $session->getFlash('success'),
    'error' => $session->getFlash('error'),
    'warning' => $session->getFlash('warning'),
    'info' => $session->getFlash('info')
];
?>

<div class="alert-container">
    <?php foreach ($alerts as $type => $message): ?>
        <?php if ($message): ?>
            <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show alert-custom" role="alert">
                <div class="alert-content">
                    <div class="alert-icon">
                        <?php
                        $icons = [
                            'success' => 'fas fa-check-circle',
                            'error' => 'fas fa-exclamation-circle',
                            'warning' => 'fas fa-exclamation-triangle',
                            'info' => 'fas fa-info-circle'
                        ];
                        ?>
                        <i class="<?= $icons[$type] ?? 'fas fa-info-circle' ?>"></i>
                    </div>
                    <div class="alert-message">
                        <?php if (is_array($message)): ?>
                            <ul class="mb-0">
                                <?php foreach ($message as $msg): ?>
                                    <li><?= htmlspecialchars($msg) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <?= htmlspecialchars($message) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!-- Toast Container for Dynamic Alerts -->
<div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer">
    <!-- Toasts will be dynamically added here -->
</div>

<style>
.alert-container {
    margin-bottom: 1.5rem;
}

.alert-custom {
    border: none;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    animation: slideInDown 0.3s ease-out;
}

.alert-content {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.alert-icon {
    font-size: 1.2rem;
    margin-top: 0.1rem;
    flex-shrink: 0;
}

.alert-message {
    flex: 1;
    font-weight: 500;
}

.alert-success {
    background-color: #f0fdf4;
    color: #166534;
    border-left: 4px solid #22c55e;
}

.alert-success .alert-icon {
    color: #22c55e;
}

.alert-danger {
    background-color: #fef2f2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

.alert-danger .alert-icon {
    color: #ef4444;
}

.alert-warning {
    background-color: #fffbeb;
    color: #92400e;
    border-left: 4px solid #f59e0b;
}

.alert-warning .alert-icon {
    color: #f59e0b;
}

.alert-info {
    background-color: #eff6ff;
    color: #1e40af;
    border-left: 4px solid #3b82f6;
}

.alert-info .alert-icon {
    color: #3b82f6;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1rem;
    opacity: 0.6;
    transition: opacity 0.3s ease;
    padding: 0.5rem;
}

.btn-close:hover {
    opacity: 1;
}

/* Toast styles */
.toast-custom {
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 300px;
}

.toast-header-custom {
    background: transparent;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    padding: 0.75rem 1rem;
}

.toast-body-custom {
    padding: 1rem;
}

/* Animations */
@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideOutUp {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-20px);
    }
}

.alert-dismissing {
    animation: slideOutUp 0.3s ease-out forwards;
}

/* Progress alerts */
.alert-progress {
    position: relative;
    overflow: hidden;
}

.alert-progress::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: currentColor;
    opacity: 0.3;
    animation: progressBar 5s linear forwards;
}

@keyframes progressBar {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .alert-custom {
        margin-left: 1rem;
        margin-right: 1rem;
    }
    
    .toast-container {
        left: 1rem;
        right: 1rem;
        top: 1rem !important;
    }
    
    .toast-custom {
        min-width: auto;
        width: 100%;
    }
}
</style>

<script>
// Alert utility functions
const AlertUtils = {
    // Show dynamic toast
    toast: function(message, type = 'info', duration = 5000) {
        const container = document.getElementById('toastContainer');
        const toastId = 'toast-' + Date.now();
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        
        const colors = {
            success: 'text-success',
            error: 'text-danger',
            warning: 'text-warning',
            info: 'text-info'
        };
        
        const toastHtml = `
            <div id="${toastId}" class="toast toast-custom" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header toast-header-custom">
                    <i class="${icons[type]} ${colors[type]} me-2"></i>
                    <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                    <small class="text-muted">just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body toast-body-custom">
                    ${message}
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            delay: duration
        });
        
        toast.show();
        
        // Remove from DOM after hiding
        toastElement.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    },
    
    // Show success toast
    success: function(message, duration = 5000) {
        this.toast(message, 'success', duration);
    },
    
    // Show error toast
    error: function(message, duration = 7000) {
        this.toast(message, 'error', duration);
    },
    
    // Show warning toast
    warning: function(message, duration = 6000) {
        this.toast(message, 'warning', duration);
    },
    
    // Show info toast
    info: function(message, duration = 5000) {
        this.toast(message, 'info', duration);
    },
    
    // Show progress alert
    progress: function(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-progress alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <div class="alert-icon">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="alert-message">
                        ${message}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        const container = document.querySelector('.alert-container');
        container.insertAdjacentHTML('afterbegin', alertHtml);
    }
};

// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        document.querySelectorAll('.alert:not(.alert-permanent)').forEach(function(alert) {
            alert.classList.add('alert-dismissing');
            setTimeout(function() {
                const alertInstance = bootstrap.Alert.getInstance(alert);
                if (alertInstance) {
                    alertInstance.close();
                }
            }, 300);
        });
    }, 5000);
    
    // Handle alert dismissal animation
    document.querySelectorAll('.alert .btn-close').forEach(function(closeBtn) {
        closeBtn.addEventListener('click', function() {
            const alert = this.closest('.alert');
            alert.classList.add('alert-dismissing');
        });
    });
});

// Global error handler for AJAX requests
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    AlertUtils.error('An unexpected error occurred. Please refresh the page and try again.');
});

// Network status alerts
window.addEventListener('online', function() {
    AlertUtils.success('Connection restored!', 3000);
});

window.addEventListener('offline', function() {
    AlertUtils.warning('You are currently offline. Some features may not work properly.', 10000);
});
</script>