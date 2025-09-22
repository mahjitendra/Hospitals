<div class="admin-dashboard">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">Admin Dashboard</h1>
                <p class="page-subtitle">Welcome back, <?= $user['name'] ?>. Here's what's happening at your institution.</p>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                    <button class="btn btn-outline-primary" onclick="exportDashboard()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <h6 class="stat-title">Total Students</h6>
                <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
            <h2 class="stat-value"><?= number_format($stats['total_students'] ?? 0) ?></h2>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>
                +<?= $stats['new_students_this_month'] ?? 0 ?> this month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <h6 class="stat-title">Total Faculty</h6>
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
            <h2 class="stat-value"><?= number_format($stats['total_faculty'] ?? 0) ?></h2>
            <div class="stat-change">
                <i class="fas fa-users me-1"></i>
                <?= $stats['active_faculty'] ?? 0 ?> active
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <h6 class="stat-title">Fee Collection</h6>
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <i class="fas fa-rupee-sign"></i>
                </div>
            </div>
            <h2 class="stat-value">₹<?= number_format($stats['total_collection'] ?? 0) ?></h2>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>
                +<?= $stats['collection_growth'] ?? 0 ?>% vs last month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <h6 class="stat-title">Attendance Today</h6>
                <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <h2 class="stat-value"><?= $stats['attendance_percentage'] ?? 0 ?>%</h2>
            <div class="stat-change">
                <i class="fas fa-users me-1"></i>
                <?= $stats['present_today'] ?? 0 ?>/<?= $stats['total_today'] ?? 0 ?> present
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Recent Activities</h5>
                        <a href="/admin/logs" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="activity-timeline">
                        <?php if (!empty($activities)): ?>
                            <?php foreach ($activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-<?= $activity['icon'] ?? 'circle' ?> text-<?= $activity['type'] ?? 'primary' ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title"><?= $activity['title'] ?></div>
                                        <div class="activity-description"><?= $activity['description'] ?></div>
                                        <div class="activity-time">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= timeAgo($activity['created_at']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No recent activities</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats & System Health -->
        <div class="col-lg-4">
            <!-- System Health -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">System Health</h5>
                </div>
                <div class="card-body">
                    <div class="health-metrics">
                        <div class="health-item">
                            <div class="health-label">Database</div>
                            <div class="health-status">
                                <span class="badge bg-<?= $health['database'] === 'healthy' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($health['database'] ?? 'unknown') ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="health-item">
                            <div class="health-label">Storage</div>
                            <div class="health-status">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: <?= $health['storage']['percentage'] ?? 0 ?>%"></div>
                                </div>
                                <small class="text-muted"><?= $health['storage']['used'] ?? '0 MB' ?> used</small>
                            </div>
                        </div>
                        
                        <div class="health-item">
                            <div class="health-label">Cache</div>
                            <div class="health-status">
                                <span class="badge bg-<?= $health['cache'] === 'healthy' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($health['cache'] ?? 'unknown') ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="health-item">
                            <div class="health-label">Last Backup</div>
                            <div class="health-status">
                                <span class="badge bg-<?= $health['backup'] === 'recent' ? 'success' : ($health['backup'] === 'warning' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($health['backup'] ?? 'unknown') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/students/create" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus me-2"></i>Add Student
                        </a>
                        <a href="/faculty/create" class="btn btn-outline-success">
                            <i class="fas fa-user-tie me-2"></i>Add Faculty
                        </a>
                        <a href="/academic/courses/create" class="btn btn-outline-info">
                            <i class="fas fa-graduation-cap me-2"></i>Add Course
                        </a>
                        <a href="/examinations/create" class="btn btn-outline-warning">
                            <i class="fas fa-clipboard-list me-2"></i>Create Exam
                        </a>
                        <a href="/admin/backups/create" class="btn btn-outline-secondary">
                            <i class="fas fa-database me-2"></i>Create Backup
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Monthly Admissions</h5>
                </div>
                <div class="card-body">
                    <canvas id="admissionsChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Fee Collection Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="feeChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Data Tables -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Recent Admissions</h5>
                        <a href="/students/admissions" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Course</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_admissions)): ?>
                                    <?php foreach ($recent_admissions as $admission): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                        <?= strtoupper(substr($admission['first_name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium"><?= $admission['first_name'] ?> <?= $admission['last_name'] ?></div>
                                                        <small class="text-muted"><?= $admission['email'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= $admission['course_name'] ?></td>
                                            <td><?= date('M d, Y', strtotime($admission['application_date'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $admission['status'] === 'approved' ? 'success' : ($admission['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                    <?= ucfirst($admission['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No recent admissions</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Pending Tasks</h5>
                        <span class="badge bg-warning"><?= count($pending_tasks ?? []) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($pending_tasks)): ?>
                        <div class="task-list">
                            <?php foreach ($pending_tasks as $task): ?>
                                <div class="task-item">
                                    <div class="task-icon">
                                        <i class="fas fa-<?= $task['icon'] ?? 'tasks' ?> text-<?= $task['priority'] ?? 'primary' ?>"></i>
                                    </div>
                                    <div class="task-content">
                                        <div class="task-title"><?= $task['title'] ?></div>
                                        <div class="task-description"><?= $task['description'] ?></div>
                                        <div class="task-meta">
                                            <span class="badge bg-<?= $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'info') ?>">
                                                <?= ucfirst($task['priority'] ?? 'normal') ?>
                                            </span>
                                            <small class="text-muted ms-2"><?= timeAgo($task['created_at']) ?></small>
                                        </div>
                                    </div>
                                    <div class="task-actions">
                                        <a href="<?= $task['url'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted mb-0">All tasks completed!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-dashboard {
    animation: fadeIn 0.5s ease-out;
}

.page-header {
    margin-bottom: 2rem;
}

.activity-timeline {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.2s ease;
}

.activity-item:hover {
    background-color: #f9fafb;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.activity-description {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.activity-time {
    font-size: 0.8rem;
    color: #9ca3af;
}

.health-metrics {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.health-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.health-item:last-child {
    border-bottom: none;
}

.health-label {
    font-weight: 500;
    color: #374151;
}

.health-status {
    text-align: right;
}

.task-list {
    max-height: 400px;
    overflow-y: auto;
}

.task-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.task-item:last-child {
    border-bottom: none;
}

.task-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.task-content {
    flex: 1;
}

.task-title {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.task-description {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.task-meta {
    display: flex;
    align-items: center;
}

.task-actions {
    margin-left: 1rem;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .page-header .row {
        text-align: center;
    }
    
    .page-header .col-auto {
        margin-top: 1rem;
    }
    
    .activity-item,
    .task-item {
        flex-direction: column;
        text-align: center;
    }
    
    .activity-icon,
    .task-icon {
        margin: 0 auto 1rem;
    }
    
    .health-item {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
}
</style>

<script>
// Dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeCharts();
    
    // Auto-refresh dashboard every 5 minutes
    setInterval(refreshDashboard, 300000);
    
    // Load real-time updates
    loadRealTimeUpdates();
});

function initializeCharts() {
    // Admissions Chart
    const admissionsCtx = document.getElementById('admissionsChart');
    if (admissionsCtx) {
        new Chart(admissionsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_data['admissions']['labels'] ?? []) ?>,
                datasets: [{
                    label: 'Admissions',
                    data: <?= json_encode($chart_data['admissions']['data'] ?? []) ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Fee Collection Chart
    const feeCtx = document.getElementById('feeChart');
    if (feeCtx) {
        new Chart(feeCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chart_data['fees']['labels'] ?? []) ?>,
                datasets: [{
                    label: 'Collection',
                    data: <?= json_encode($chart_data['fees']['data'] ?? []) ?>,
                    backgroundColor: 'rgba(245, 158, 11, 0.8)',
                    borderColor: '#f59e0b',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
}

function refreshDashboard() {
    const btn = event.target.closest('button');
    const icon = btn.querySelector('i');
    
    icon.classList.add('fa-spin');
    btn.disabled = true;
    
    fetch('/admin/dashboard/refresh', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            AlertUtils.error('Failed to refresh dashboard');
        }
    })
    .catch(error => {
        AlertUtils.error('Network error occurred');
    })
    .finally(() => {
        icon.classList.remove('fa-spin');
        btn.disabled = false;
    });
}

function exportDashboard() {
    const format = prompt('Export format (pdf/excel):', 'pdf');
    if (format && ['pdf', 'excel'].includes(format.toLowerCase())) {
        window.open(`/admin/dashboard/export?format=${format}`, '_blank');
    }
}

function loadRealTimeUpdates() {
    // Load real-time notifications, system status, etc.
    fetch('/admin/dashboard/realtime')
        .then(response => response.json())
        .then(data => {
            if (data.notifications) {
                updateNotificationBadge(data.notifications.count);
            }
            
            if (data.system_alerts) {
                data.system_alerts.forEach(alert => {
                    AlertUtils.warning(alert.message);
                });
            }
        })
        .catch(error => {
            console.log('Real-time updates failed:', error);
        });
}

function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}
</script>