<div class="student-dashboard">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="welcome-title">Welcome back, <?= $data['student']['first_name'] ?>!</h2>
                <p class="welcome-subtitle">Here's your academic overview for today</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="current-date">
                    <div class="date-display">
                        <div class="day"><?= date('d') ?></div>
                        <div class="month-year">
                            <div class="month"><?= date('M') ?></div>
                            <div class="year"><?= date('Y') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="dashboard-grid">
        <!-- Attendance Card -->
        <div class="dashboard-card attendance-card">
            <div class="card-header-custom">
                <h6 class="card-title-custom">Attendance</h6>
                <div class="card-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div class="card-content">
                <div class="attendance-percentage">
                    <div class="percentage-circle">
                        <svg viewBox="0 0 36 36" class="circular-chart">
                            <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path class="circle" stroke-dasharray="<?= $data['attendanceStats']['attendance_percentage'] ?? 0 ?>, 100" 
                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <text x="18" y="20.35" class="percentage"><?= round($data['attendanceStats']['attendance_percentage'] ?? 0) ?>%</text>
                        </svg>
                    </div>
                </div>
                <div class="attendance-details">
                    <div class="detail-item">
                        <span class="detail-label">Present:</span>
                        <span class="detail-value text-success"><?= $data['attendanceStats']['present_days'] ?? 0 ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Absent:</span>
                        <span class="detail-value text-danger"><?= $data['attendanceStats']['absent_days'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fee Status Card -->
        <div class="dashboard-card fees-card">
            <div class="card-header-custom">
                <h6 class="card-title-custom">Fee Status</h6>
                <div class="card-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
            </div>
            <div class="card-content">
                <div class="fee-summary">
                    <div class="fee-item">
                        <div class="fee-label">Total Fees</div>
                        <div class="fee-amount">₹<?= number_format($data['feeStatus']['total'] ?? 0) ?></div>
                    </div>
                    <div class="fee-item">
                        <div class="fee-label">Paid</div>
                        <div class="fee-amount text-success">₹<?= number_format($data['feeStatus']['paid'] ?? 0) ?></div>
                    </div>
                    <div class="fee-item">
                        <div class="fee-label">Pending</div>
                        <div class="fee-amount text-warning">₹<?= number_format($data['feeStatus']['pending'] ?? 0) ?></div>
                    </div>
                </div>
                <?php if (($data['feeStatus']['pending'] ?? 0) > 0): ?>
                    <a href="/student/fees" class="btn btn-sm btn-warning w-100 mt-2">
                        <i class="fas fa-credit-card me-1"></i>Pay Now
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Results Card -->
        <div class="dashboard-card results-card">
            <div class="card-header-custom">
                <h6 class="card-title-custom">Recent Results</h6>
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="card-content">
                <?php if (!empty($data['recentResults'])): ?>
                    <div class="results-list">
                        <?php foreach (array_slice($data['recentResults'], 0, 3) as $result): ?>
                            <div class="result-item">
                                <div class="result-subject"><?= $result['subject_name'] ?></div>
                                <div class="result-score">
                                    <span class="score"><?= $result['marks_obtained'] ?>/<?= $result['total_marks'] ?></span>
                                    <span class="grade badge bg-<?= $result['grade'] === 'A+' || $result['grade'] === 'A' ? 'success' : ($result['grade'] === 'F' ? 'danger' : 'warning') ?>">
                                        <?= $result['grade'] ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="/student/results" class="btn btn-sm btn-success w-100 mt-2">
                        <i class="fas fa-chart-bar me-1"></i>View All Results
                    </a>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No results available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Today's Timetable Card -->
        <div class="dashboard-card timetable-card">
            <div class="card-header-custom">
                <h6 class="card-title-custom">Today's Classes</h6>
                <div class="card-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="card-content">
                <?php if (!empty($data['todayTimetable'])): ?>
                    <div class="timetable-list">
                        <?php foreach ($data['todayTimetable'] as $class): ?>
                            <div class="timetable-item">
                                <div class="time-slot">
                                    <div class="start-time"><?= date('H:i', strtotime($class['start_time'])) ?></div>
                                    <div class="end-time"><?= date('H:i', strtotime($class['end_time'])) ?></div>
                                </div>
                                <div class="class-info">
                                    <div class="subject-name"><?= $class['subject_name'] ?></div>
                                    <div class="faculty-name"><?= $class['faculty_name'] ?></div>
                                    <div class="room-number">Room: <?= $class['room_number'] ?? 'TBA' ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No classes today</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Upcoming Events & Announcements -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Upcoming Exams</h5>
                        <a href="/student/exams" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
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
                                        <div class="exam-time">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('H:i', strtotime($exam['start_time'])) ?> - <?= date('H:i', strtotime($exam['end_time'])) ?>
                                        </div>
                                    </div>
                                    <div class="exam-status">
                                        <span class="badge bg-info"><?= ucfirst($exam['status']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No upcoming exams</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Announcements</h5>
                        <a href="/student/announcements" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['announcements'])): ?>
                        <div class="announcement-list">
                            <?php foreach ($data['announcements'] as $announcement): ?>
                                <div class="announcement-item">
                                    <div class="announcement-icon">
                                        <i class="fas fa-bullhorn text-primary"></i>
                                    </div>
                                    <div class="announcement-content">
                                        <div class="announcement-title"><?= $announcement['title'] ?></div>
                                        <div class="announcement-excerpt"><?= str_limit($announcement['content'], 100) ?></div>
                                        <div class="announcement-meta">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= timeAgo($announcement['created_at']) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-bullhorn fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No announcements</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.student-dashboard {
    animation: fadeInUp 0.5s ease-out;
}

.welcome-section {
    background: linear-gradient(135deg, #059669, #047857);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.welcome-title {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.welcome-subtitle {
    opacity: 0.9;
    margin-bottom: 0;
}

.current-date {
    text-align: center;
}

.date-display {
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    padding: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 1rem;
}

.day {
    font-size: 2rem;
    font-weight: 700;
}

.month {
    font-size: 0.9rem;
    font-weight: 500;
}

.year {
    font-size: 0.8rem;
    opacity: 0.8;
}

.percentage-circle {
    width: 80px;
    height: 80px;
    margin: 0 auto;
}

.circular-chart {
    display: block;
    margin: 0 auto;
    max-width: 80%;
    max-height: 80px;
}

.circle-bg {
    fill: none;
    stroke: #e5e7eb;
    stroke-width: 2.8;
}

.circle {
    fill: none;
    stroke: #3b82f6;
    stroke-width: 2.8;
    stroke-linecap: round;
    animation: progress 1s ease-out forwards;
}

.percentage {
    fill: #1f2937;
    font-family: sans-serif;
    font-size: 0.4em;
    font-weight: 600;
    text-anchor: middle;
}

@keyframes progress {
    0% {
        stroke-dasharray: 0 100;
    }
}

.attendance-details {
    margin-top: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.detail-label {
    font-size: 0.9rem;
    color: #6b7280;
}

.detail-value {
    font-weight: 500;
}

.fee-summary {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.fee-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.fee-label {
    font-size: 0.9rem;
    color: #6b7280;
}

.fee-amount {
    font-weight: 600;
    font-size: 1rem;
}

.results-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.result-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.result-subject {
    font-weight: 500;
    color: #1f2937;
}

.result-score {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.score {
    font-size: 0.9rem;
    color: #6b7280;
}

.timetable-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.timetable-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #059669;
}

.time-slot {
    margin-right: 1rem;
    text-align: center;
    min-width: 60px;
}

.start-time {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.9rem;
}

.end-time {
    font-size: 0.8rem;
    color: #6b7280;
}

.class-info {
    flex: 1;
}

.subject-name {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.faculty-name {
    font-size: 0.8rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.room-number {
    font-size: 0.8rem;
    color: #9ca3af;
}

.exam-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.exam-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #f59e0b;
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
    font-size: 0.9rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.exam-time {
    font-size: 0.8rem;
    color: #9ca3af;
}

.announcement-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.announcement-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.announcement-icon {
    margin-right: 1rem;
    margin-top: 0.25rem;
}

.announcement-content {
    flex: 1;
}

.announcement-title {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.announcement-excerpt {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.announcement-meta {
    font-size: 0.8rem;
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
    
    .welcome-title {
        font-size: 1.5rem;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .exam-item,
    .announcement-item,
    .timetable-item {
        flex-direction: column;
        text-align: center;
    }
    
    .exam-date,
    .announcement-icon,
    .time-slot {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
// Student dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize progress circles
    animateProgressCircles();
    
    // Load notifications
    loadStudentNotifications();
    
    // Auto-refresh data every 10 minutes
    setInterval(refreshStudentData, 600000);
});

function animateProgressCircles() {
    const circles = document.querySelectorAll('.circle');
    circles.forEach(circle => {
        const percentage = circle.getAttribute('stroke-dasharray').split(',')[0];
        circle.style.strokeDasharray = `0, 100`;
        
        setTimeout(() => {
            circle.style.strokeDasharray = `${percentage}, 100`;
        }, 500);
    });
}

function loadStudentNotifications() {
    fetch('/student/notifications/recent')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications.length > 0) {
                data.notifications.forEach(notification => {
                    if (notification.type === 'urgent') {
                        AlertUtils.warning(notification.message);
                    }
                });
            }
        })
        .catch(error => {
            console.log('Failed to load notifications:', error);
        });
}

function refreshStudentData() {
    fetch('/student/dashboard/refresh')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update attendance percentage
                updateAttendanceDisplay(data.attendance);
                
                // Update fee status
                updateFeeDisplay(data.fees);
                
                // Update notifications badge
                updateNotificationBadge(data.notifications_count);
            }
        })
        .catch(error => {
            console.log('Dashboard refresh failed:', error);
        });
}

function updateAttendanceDisplay(attendance) {
    const circle = document.querySelector('.circle');
    const percentageText = document.querySelector('.percentage');
    
    if (circle && percentageText) {
        circle.style.strokeDasharray = `${attendance.percentage}, 100`;
        percentageText.textContent = `${Math.round(attendance.percentage)}%`;
    }
}

function updateFeeDisplay(fees) {
    const pendingAmount = document.querySelector('.fee-amount.text-warning');
    if (pendingAmount) {
        pendingAmount.textContent = `₹${fees.pending.toLocaleString()}`;
    }
}

function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'block' : 'none';
    }
}
</script>