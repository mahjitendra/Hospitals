<div class="faculty-dashboard">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="welcome-title">Good <?= getTimeGreeting() ?>, <?= $data['faculty']['first_name'] ?>!</h2>
                <p class="welcome-subtitle">
                    <?= $data['faculty']['designation_name'] ?? 'Faculty' ?> • 
                    <?= $data['faculty']['department_name'] ?? 'Department' ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="today-schedule">
                    <h6 class="mb-2">Today's Schedule</h6>
                    <?php if (!empty($data['todayClasses'])): ?>
                        <div class="schedule-count">
                            <span class="count"><?= count($data['todayClasses']) ?></span>
                            <span class="label">Classes</span>
                        </div>
                    <?php else: ?>
                        <p class="mb-0">No classes today</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="dashboard-grid">
        <!-- Today's Classes -->
        <div class="dashboard-card">
            <div class="card-header-custom">
                <h6 class="card-title-custom">Today's Classes</h6>
                <div class="card-icon" style="background: #7c3aed;">
                    <i class="fas fa-chalkboard"></i>
                </div>
            </div>
            <div class="card-content">
                <?php if (!empty($data['todayClasses'])): ?>
                    <div class="class-list">
                        <?php foreach ($data['todayClasses'] as $class): ?>
                            <div class="class-item">
                                <div class="class-time">
                                    <?= date('H:i', strtotime($class['start_time'])) ?>
                                </div>
                                <div class="class-details">
                                    <div class="subject"><?= $class['subject_name'] ?></div>
                                    <div class="class-info"><?= $class['class_name'] ?> <?= $class['section_name'] ? '(' . $class['section_name'] . ')' : '' ?></div>
                                </div>
                                <div class="class-actions">
                                    <a href="/attendance/mark/<?= $class['id'] ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="/faculty/timetable" class="btn btn-sm btn-outline-primary w-100 mt-2">
                        View Full Timetable
                    </a>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No classes scheduled</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Tasks -->
        <div class="dashboard-card">
            <div class="card-header-custom">
                <h6 class="card-title-custom">Pending Tasks</h6>
                <div class="card-icon" style="background: #f59e0b;">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
            <div class="card-content">
                <?php if (!empty($data['pendingTasks'])): ?>
                    <div class="task-list">
                        <?php foreach ($data['pendingTasks'] as $task): ?>
                            <div class="task-item">
                                <div class="task-priority">
                                    <span class="priority-indicator priority-<?= $task['priority'] ?>"></span>
                                </div>
                                <div class="task-details">
                                    <div class="task-title"><?= $task['title'] ?></div>
                                    <div class="task-due">Due: <?= date('M d', strtotime($task['due_date'])) ?></div>
                                </div>
                                <div class="task-action">
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

        <!-- Upcoming Exams -->
        <div class="dashboard-card">
            <div class="card-header-custom">
                <h6 class="card-title-custom">Upcoming Exams</h6>
                <div class="card-icon" style="background: #ef4444;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="card-content">
                <?php if (!empty($data['upcomingExams'])): ?>
                    <div class="exam-list">
                        <?php foreach ($data['upcomingExams'] as $exam): ?>
                            <div class="exam-item">
                                <div class="exam-date">
                                    <div class="date"><?= date('d', strtotime($exam['exam_date'])) ?></div>
                                    <div class="month"><?= date('M', strtotime($exam['exam_date'])) ?></div>
                                </div>
                                <div class="exam-details">
                                    <div class="exam-name"><?= $exam['exam_name'] ?></div>
                                    <div class="exam-subject"><?= $exam['subject_name'] ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="/faculty/exams" class="btn btn-sm btn-outline-danger w-100 mt-2">
                        View All Exams
                    </a>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No upcoming exams</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="dashboard-card">
            <div class="card-header-custom">
                <h6 class="card-title-custom">Recent Attendance</h6>
                <div class="card-icon" style="background: #10b981;">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div class="card-content">
                <?php if (!empty($data['recentAttendance'])): ?>
                    <div class="attendance-list">
                        <?php foreach ($data['recentAttendance'] as $attendance): ?>
                            <div class="attendance-item">
                                <div class="attendance-date">
                                    <?= date('M d', strtotime($attendance['attendance_date'])) ?>
                                </div>
                                <div class="attendance-details">
                                    <div class="class-name"><?= $attendance['class_name'] ?></div>
                                    <div class="attendance-stats">
                                        <span class="text-success"><?= $attendance['present'] ?> Present</span>
                                        <span class="text-danger ms-2"><?= $attendance['absent'] ?> Absent</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="/faculty/attendance" class="btn btn-sm btn-outline-success w-100 mt-2">
                        Mark Attendance
                    </a>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-calendar-check fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No attendance records</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Additional Sections -->
    <div class="row mt-4">
        <!-- Performance Overview -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Performance Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="performanceChart" height="200"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="performance-metrics">
                                <div class="metric-item">
                                    <div class="metric-label">Student Success Rate</div>
                                    <div class="metric-value text-success">
                                        <?= $data['performance']['success_rate'] ?? 0 ?>%
                                    </div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-label">Average Marks</div>
                                    <div class="metric-value text-info">
                                        <?= round($data['performance']['average_marks'] ?? 0, 1) ?>
                                    </div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-label">Classes Taught</div>
                                    <div class="metric-value text-primary">
                                        <?= $data['performance']['classes_taught'] ?? 0 ?>
                                    </div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-label">Students Taught</div>
                                    <div class="metric-value text-warning">
                                        <?= $data['performance']['students_taught'] ?? 0 ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/attendance/mark" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Mark Attendance
                        </a>
                        <a href="/faculty/students" class="btn btn-info">
                            <i class="fas fa-users me-2"></i>View Students
                        </a>
                        <a href="/examinations/create" class="btn btn-warning">
                            <i class="fas fa-plus me-2"></i>Create Exam
                        </a>
                        <a href="/faculty/leave/apply" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-times me-2"></i>Apply Leave
                        </a>
                        <a href="/reports/faculty" class="btn btn-outline-secondary">
                            <i class="fas fa-chart-bar me-2"></i>View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.faculty-dashboard {
    animation: fadeInUp 0.5s ease-out;
}

.welcome-section {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.schedule-count {
    text-align: center;
}

.schedule-count .count {
    display: block;
    font-size: 2rem;
    font-weight: 700;
}

.schedule-count .label {
    font-size: 0.9rem;
    opacity: 0.8;
}

.class-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.class-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #7c3aed;
}

.class-time {
    font-weight: 600;
    color: #7c3aed;
    margin-right: 1rem;
    min-width: 50px;
}

.class-details {
    flex: 1;
}

.subject {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.class-info {
    font-size: 0.8rem;
    color: #6b7280;
}

.class-actions {
    margin-left: 1rem;
}

.task-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.task-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.task-priority {
    margin-right: 0.75rem;
}

.priority-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: block;
}

.priority-high {
    background: #ef4444;
}

.priority-medium {
    background: #f59e0b;
}

.priority-low {
    background: #10b981;
}

.task-details {
    flex: 1;
}

.task-title {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.task-due {
    font-size: 0.8rem;
    color: #6b7280;
}

.exam-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.exam-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #ef4444;
}

.exam-date {
    margin-right: 1rem;
    text-align: center;
    min-width: 50px;
}

.exam-date .date {
    font-size: 1.2rem;
    font-weight: 600;
    color: #1f2937;
}

.exam-date .month {
    font-size: 0.8rem;
    color: #6b7280;
    text-transform: uppercase;
}

.exam-details {
    flex: 1;
}

.exam-name {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.exam-subject {
    font-size: 0.8rem;
    color: #6b7280;
}

.attendance-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.attendance-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.attendance-date {
    font-weight: 600;
    color: #10b981;
    margin-right: 1rem;
    min-width: 60px;
    font-size: 0.9rem;
}

.attendance-details {
    flex: 1;
}

.class-name {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.attendance-stats {
    font-size: 0.8rem;
}

.performance-metrics {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding: 1rem;
}

.metric-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.metric-label {
    font-weight: 500;
    color: #374151;
}

.metric-value {
    font-size: 1.2rem;
    font-weight: 600;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .welcome-section {
        text-align: center;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .class-item,
    .task-item,
    .exam-item,
    .attendance-item {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .class-time,
    .attendance-date {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
// Faculty dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize performance chart
    initializePerformanceChart();
    
    // Load real-time updates
    loadFacultyUpdates();
    
    // Auto-refresh every 10 minutes
    setInterval(refreshFacultyData, 600000);
});

function initializePerformanceChart() {
    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Excellent', 'Good', 'Average', 'Below Average'],
                datasets: [{
                    data: <?= json_encode($data['performance']['grade_distribution'] ?? [25, 35, 30, 10]) ?>,
                    backgroundColor: [
                        '#10b981',
                        '#3b82f6',
                        '#f59e0b',
                        '#ef4444'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}

function loadFacultyUpdates() {
    fetch('/faculty/dashboard/updates')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update pending tasks count
                updateTasksCount(data.tasks_count);
                
                // Show urgent notifications
                if (data.urgent_notifications) {
                    data.urgent_notifications.forEach(notification => {
                        AlertUtils.warning(notification.message);
                    });
                }
            }
        })
        .catch(error => {
            console.log('Failed to load updates:', error);
        });
}

function refreshFacultyData() {
    fetch('/faculty/dashboard/refresh')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update dashboard components
                location.reload();
            }
        })
        .catch(error => {
            console.log('Dashboard refresh failed:', error);
        });
}

function updateTasksCount(count) {
    const badge = document.querySelector('.card-title-custom + .badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline' : 'none';
    }
}

function getTimeGreeting() {
    const hour = new Date().getHours();
    if (hour < 12) return 'morning';
    if (hour < 17) return 'afternoon';
    return 'evening';
}
</script>