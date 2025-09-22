<footer class="main-footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="footer-info">
                    <strong>College ERP v<?= config('app.version', '1.0.0') ?></strong>
                    <span class="text-muted ms-2">Education Management System</span>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="footer-links text-md-end">
                    <a href="/help" class="footer-link">Help</a>
                    <a href="/support" class="footer-link">Support</a>
                    <a href="/privacy" class="footer-link">Privacy</a>
                    <a href="/terms" class="footer-link">Terms</a>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="footer-stats">
                    <div class="row text-center">
                        <div class="col-6 col-md-3">
                            <div class="footer-stat">
                                <div class="stat-value"><?= cache('stats_total_students', 0) ?></div>
                                <div class="stat-label">Students</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="footer-stat">
                                <div class="stat-value"><?= cache('stats_total_faculty', 0) ?></div>
                                <div class="stat-label">Faculty</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="footer-stat">
                                <div class="stat-value"><?= cache('stats_total_courses', 0) ?></div>
                                <div class="stat-label">Courses</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="footer-stat">
                                <div class="stat-value"><?= date('Y') - 2000 ?></div>
                                <div class="stat-label">Years</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <hr class="footer-divider">
        
        <div class="row">
            <div class="col-12 text-center">
                <div class="footer-copyright">
                    <p class="mb-0">
                        &copy; <?= date('Y') ?> College ERP. All rights reserved. 
                        Made with <i class="fas fa-heart text-danger"></i> for education.
                    </p>
                    <p class="mb-0 mt-1">
                        <small class="text-muted">
                            Last updated: <?= date('M d, Y H:i') ?> | 
                            Server time: <?= date('H:i:s') ?> | 
                            <?php if (config('app.debug')): ?>
                                <span class="text-warning">Debug Mode</span>
                            <?php else: ?>
                                <span class="text-success">Production Mode</span>
                            <?php endif; ?>
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.main-footer {
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 2rem 0 1rem;
    margin-top: auto;
}

.footer-info {
    display: flex;
    align-items: center;
}

.footer-links {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    align-items: center;
}

.footer-link {
    color: #6c757d;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.footer-link:hover {
    color: #495057;
}

.footer-stats {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin: 1rem 0;
}

.footer-stat {
    padding: 0.5rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #3b82f6;
}

.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.footer-divider {
    border-color: #dee2e6;
    margin: 1.5rem 0 1rem;
}

.footer-copyright {
    color: #6c757d;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .footer-links {
        justify-content: center;
        margin-top: 1rem;
    }
    
    .footer-stats .row {
        text-align: center;
    }
    
    .stat-value {
        font-size: 1.2rem;
    }
}

/* System Status Indicators */
.system-status {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1050;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 0.5rem;
    animation: pulse 2s infinite;
}

.status-healthy {
    background-color: #10b981;
}

.status-warning {
    background-color: #f59e0b;
}

.status-error {
    background-color: #ef4444;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
    }
}
</style>

<!-- System Status (for admins) -->
<?php if (hasRole(ROLE_ADMIN)): ?>
<div class="system-status d-none d-md-block">
    <div class="card card-sm">
        <div class="card-body p-2">
            <small class="text-muted">
                <span class="status-indicator status-healthy"></span>
                System Status: Online
            </small>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Footer functionality
document.addEventListener('DOMContentLoaded', function() {
    // Update server time every second
    setInterval(function() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        const timeElement = document.querySelector('.footer-copyright small');
        if (timeElement) {
            const text = timeElement.innerHTML;
            const updatedText = text.replace(/\d{2}:\d{2}:\d{2}/, timeString);
            timeElement.innerHTML = updatedText;
        }
    }, 1000);
    
    // Load and update stats periodically
    if (typeof updateFooterStats === 'function') {
        updateFooterStats();
        setInterval(updateFooterStats, 300000); // Update every 5 minutes
    }
});

// Function to update footer statistics
function updateFooterStats() {
    fetch('/api/stats/footer')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('.footer-stats .stat-value:nth-child(1)').textContent = data.students || 0;
                document.querySelector('.footer-stats .stat-value:nth-child(2)').textContent = data.faculty || 0;
                document.querySelector('.footer-stats .stat-value:nth-child(3)').textContent = data.courses || 0;
            }
        })
        .catch(error => console.log('Stats update failed:', error));
}
</script>