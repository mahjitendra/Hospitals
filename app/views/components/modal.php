<!-- Generic Modal Component -->
<div class="modal fade" id="<?= $modal_id ?? 'genericModal' ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog <?= $modal_size ?? 'modal-lg' ?> modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="<?= $modal_id ?? 'genericModal' ?>Label">
                    <?php if (!empty($modal_icon)): ?>
                        <i class="<?= $modal_icon ?> me-2"></i>
                    <?php endif; ?>
                    <?= $modal_title ?? 'Modal Title' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <?php if (!empty($modal_content)): ?>
                    <?= $modal_content ?>
                <?php else: ?>
                    <div class="modal-loading text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading content...</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($modal_footer) || !empty($modal_actions)): ?>
            <div class="modal-footer">
                <?php if (!empty($modal_actions)): ?>
                    <?php foreach ($modal_actions as $action): ?>
                        <button type="<?= $action['type'] ?? 'button' ?>" 
                                class="btn <?= $action['class'] ?? 'btn-secondary' ?>"
                                <?php if (!empty($action['onclick'])): ?>onclick="<?= $action['onclick'] ?>"<?php endif; ?>
                                <?php if (!empty($action['data'])): ?>
                                    <?php foreach ($action['data'] as $key => $value): ?>
                                        data-<?= $key ?>="<?= $value ?>"
                                    <?php endforeach; ?>
                                <?php endif; ?>>
                            <?php if (!empty($action['icon'])): ?>
                                <i class="<?= $action['icon'] ?> me-1"></i>
                            <?php endif; ?>
                            <?= $action['text'] ?>
                        </button>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= $modal_footer ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Confirm Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <p class="confirm-message">Are you sure you want to perform this action?</p>
                <div class="confirm-details d-none">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmAction">
                    <i class="fas fa-check me-1"></i>Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0 loading-text">Processing...</p>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Success
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <p class="success-message">Operation completed successfully!</p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i>OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-circle text-danger me-2"></i>
                    Error
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <p class="error-message">An error occurred. Please try again.</p>
                <div class="error-details d-none">
                    <div class="alert alert-danger">
                        <strong>Error Details:</strong>
                        <div class="error-detail-text"></div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.modal-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    border-radius: 12px 12px 0 0;
    padding: 1.25rem 1.5rem;
}

.modal-title {
    font-weight: 600;
    color: #1f2937;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    border-radius: 0 0 12px 12px;
    padding: 1rem 1.5rem;
}

.modal-loading {
    min-height: 150px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    opacity: 0.6;
    transition: opacity 0.3s ease;
}

.btn-close:hover {
    opacity: 1;
}

/* Modal animations */
.modal.fade .modal-dialog {
    transform: scale(0.8) translateY(-50px);
    transition: all 0.3s ease;
}

.modal.show .modal-dialog {
    transform: scale(1) translateY(0);
}

/* Custom modal sizes */
.modal-xs .modal-dialog {
    max-width: 300px;
}

.modal-xl .modal-dialog {
    max-width: 90%;
}

.modal-fullscreen-custom {
    max-width: 95%;
    max-height: 95%;
}

/* Form modals */
.modal-form .form-group {
    margin-bottom: 1rem;
}

.modal-form .form-label {
    font-weight: 500;
    color: #374151;
}

.modal-form .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}

/* Responsive */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    .modal-header,
    .modal-body,
    .modal-footer {
        padding: 1rem;
    }
}
</style>

<script>
// Modal utility functions
const ModalUtils = {
    // Show confirmation modal
    confirm: function(message, callback, options = {}) {
        const modal = document.getElementById('confirmModal');
        const messageEl = modal.querySelector('.confirm-message');
        const confirmBtn = modal.querySelector('#confirmAction');
        
        messageEl.textContent = message;
        
        if (options.details) {
            const detailsEl = modal.querySelector('.confirm-details');
            detailsEl.classList.remove('d-none');
            detailsEl.querySelector('.alert').innerHTML = '<strong>Warning:</strong> ' + options.details;
        }
        
        if (options.danger) {
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete';
        } else {
            confirmBtn.className = 'btn btn-primary';
            confirmBtn.innerHTML = '<i class="fas fa-check me-1"></i>Confirm';
        }
        
        confirmBtn.onclick = function() {
            bootstrap.Modal.getInstance(modal).hide();
            if (callback) callback();
        };
        
        new bootstrap.Modal(modal).show();
    },
    
    // Show success modal
    success: function(message, callback = null) {
        const modal = document.getElementById('successModal');
        const messageEl = modal.querySelector('.success-message');
        
        messageEl.textContent = message;
        
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
        
        if (callback) {
            modal.addEventListener('hidden.bs.modal', callback, { once: true });
        }
    },
    
    // Show error modal
    error: function(message, details = null) {
        const modal = document.getElementById('errorModal');
        const messageEl = modal.querySelector('.error-message');
        
        messageEl.textContent = message;
        
        if (details) {
            const detailsEl = modal.querySelector('.error-details');
            const detailTextEl = modal.querySelector('.error-detail-text');
            detailsEl.classList.remove('d-none');
            detailTextEl.textContent = details;
        }
        
        new bootstrap.Modal(modal).show();
    },
    
    // Show loading modal
    loading: function(message = 'Processing...') {
        const modal = document.getElementById('loadingModal');
        const textEl = modal.querySelector('.loading-text');
        
        textEl.textContent = message;
        new bootstrap.Modal(modal).show();
    },
    
    // Hide loading modal
    hideLoading: function() {
        const modal = document.getElementById('loadingModal');
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    },
    
    // Load content into modal
    loadContent: function(modalId, url, data = {}) {
        const modal = document.getElementById(modalId);
        const body = modal.querySelector('.modal-body');
        
        body.innerHTML = '<div class="modal-loading text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading content...</p></div>';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.text())
        .then(html => {
            body.innerHTML = html;
        })
        .catch(error => {
            body.innerHTML = '<div class="alert alert-danger">Failed to load content. Please try again.</div>';
        });
    }
};

// Global modal event handlers
document.addEventListener('DOMContentLoaded', function() {
    // Handle data-confirm attributes
    document.querySelectorAll('[data-confirm]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-confirm');
            const href = this.getAttribute('href') || this.getAttribute('data-url');
            const isDanger = this.classList.contains('btn-danger') || this.getAttribute('data-danger');
            
            ModalUtils.confirm(message, function() {
                if (href) {
                    window.location.href = href;
                } else {
                    element.click();
                }
            }, { danger: isDanger });
        });
    });
    
    // Handle AJAX forms in modals
    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('ajax-form') && e.target.closest('.modal')) {
            e.preventDefault();
            
            const form = e.target;
            const modal = form.closest('.modal');
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            submitBtn.disabled = true;
            
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(modal).hide();
                    ModalUtils.success(data.message || 'Operation completed successfully!', function() {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    ModalUtils.error(data.message || 'An error occurred', data.details);
                }
            })
            .catch(error => {
                ModalUtils.error('Network error occurred. Please try again.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }
    });
    
    // Auto-focus first input in modals
    document.querySelectorAll('.modal').forEach(function(modal) {
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = this.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });
    });
});

// Utility functions for common modal operations
function showDeleteConfirm(url, itemName = 'item') {
    ModalUtils.confirm(
        `Are you sure you want to delete this ${itemName}?`,
        function() {
            ModalUtils.loading('Deleting...');
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            })
            .then(response => response.json())
            .then(data => {
                ModalUtils.hideLoading();
                
                if (data.success) {
                    ModalUtils.success(data.message || 'Item deleted successfully!', function() {
                        location.reload();
                    });
                } else {
                    ModalUtils.error(data.message || 'Failed to delete item');
                }
            })
            .catch(error => {
                ModalUtils.hideLoading();
                ModalUtils.error('Network error occurred. Please try again.');
            });
        },
        { danger: true, details: 'This action cannot be undone.' }
    );
}

function showEditModal(modalId, url, title = 'Edit Item') {
    const modal = document.getElementById(modalId);
    const titleEl = modal.querySelector('.modal-title');
    
    titleEl.innerHTML = `<i class="fas fa-edit me-2"></i>${title}`;
    
    ModalUtils.loadContent(modalId, url);
    new bootstrap.Modal(modal).show();
}

function showViewModal(modalId, url, title = 'View Details') {
    const modal = document.getElementById(modalId);
    const titleEl = modal.querySelector('.modal-title');
    
    titleEl.innerHTML = `<i class="fas fa-eye me-2"></i>${title}`;
    
    ModalUtils.loadContent(modalId, url);
    new bootstrap.Modal(modal).show();
}

// Quick action modals
function quickAddStudent() {
    showEditModal('studentModal', '/students/create', 'Add New Student');
}

function quickAddFaculty() {
    showEditModal('facultyModal', '/faculty/create', 'Add New Faculty');
}

function quickMarkAttendance() {
    showEditModal('attendanceModal', '/attendance/quick-mark', 'Quick Attendance');
}

// Bulk action modal
function showBulkActionModal(selectedIds, actionType) {
    const modal = document.getElementById('bulkActionModal');
    const titleEl = modal.querySelector('.modal-title');
    const bodyEl = modal.querySelector('.modal-body');
    
    titleEl.innerHTML = `<i class="fas fa-tasks me-2"></i>Bulk ${actionType}`;
    bodyEl.innerHTML = `
        <p>You have selected <strong>${selectedIds.length}</strong> items for ${actionType.toLowerCase()}.</p>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            This action will be applied to all selected items.
        </div>
    `;
    
    new bootstrap.Modal(modal).show();
}
</script>