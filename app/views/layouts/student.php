<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Student Portal' ?> - College ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #059669;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-color);
            font-size: 14px;
            line-height: 1.6;
        }

        .student-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .student-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, #047857 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        .student-info h5 {
            margin: 0;
            font-weight: 600;
        }

        .student-info small {
            opacity: 0.8;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-left-color: white;
        }

        .nav-link.active {
            background-color: rgba(255,255,255,0.15);
            color: white;
            border-left-color: white;
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }

        .student-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }

        .student-header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .header-title h4 {
            margin: 0;
            color: var(--dark-color);
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quick-stats {
            display: flex;
            gap: 1rem;
        }

        .quick-stat {
            text-align: center;
            padding: 0.5rem;
        }

        .quick-stat-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .quick-stat-label {
            font-size: 0.8rem;
            color: var(--secondary-color);
        }

        .student-main {
            padding: 2rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .card-header-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .card-title-custom {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            color: var(--dark-color);
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .attendance-card .card-icon {
            background: var(--info-color);
        }

        .fees-card .card-icon {
            background: var(--warning-color);
        }

        .results-card .card-icon {
            background: var(--success-color);
        }

        .timetable-card .card-icon {
            background: var(--primary-color);
        }

        .progress-bar-custom {
            height: 8px;
            border-radius: 4px;
            background-color: #e5e7eb;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .btn-student {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .btn-student:hover {
            background: #047857;
            border-color: #047857;
            color: white;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .student-sidebar {
                transform: translateX(-100%);
            }

            .student-sidebar.show {
                transform: translateX(0);
            }

            .student-content {
                margin-left: 0;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .quick-stats {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="student-wrapper">
        <!-- Sidebar -->
        <nav class="student-sidebar" id="studentSidebar">
            <div class="sidebar-header">
                <div class="student-avatar">
                    <?= strtoupper(substr($user['name'] ?? 'S', 0, 1)) ?>
                </div>
                <div class="student-info">
                    <h5><?= $user['name'] ?? 'Student' ?></h5>
                    <small><?= $student['student_id'] ?? 'Student ID' ?></small>
                </div>
            </div>
            
            <ul class="sidebar-nav list-unstyled">
                <li class="nav-item">
                    <a href="/student/dashboard" class="nav-link <?= is_active('/student/dashboard') ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/profile" class="nav-link <?= is_active('/profile') ?>">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/student/attendance" class="nav-link <?= is_active('/student/attendance') ?>">
                        <i class="fas fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/student/timetable" class="nav-link <?= is_active('/student/timetable') ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Timetable</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/student/results" class="nav-link <?= is_active('/student/results') ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Results</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/student/fees" class="nav-link <?= is_active('/student/fees') ?>">
                        <i class="fas fa-credit-card"></i>
                        <span>Fee Status</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/student/library" class="nav-link <?= is_active('/student/library') ?>">
                        <i class="fas fa-book"></i>
                        <span>Library</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/student/documents" class="nav-link <?= is_active('/student/documents') ?>">
                        <i class="fas fa-file-alt"></i>
                        <span>Documents</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/student/notifications" class="nav-link <?= is_active('/student/notifications') ?>">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="student-content">
            <!-- Header -->
            <header class="student-header">
                <div class="header-title">
                    <h4><?= $title ?? 'Student Portal' ?></h4>
                </div>
                
                <div class="header-actions">
                    <div class="quick-stats">
                        <div class="quick-stat">
                            <div class="quick-stat-value"><?= $attendance_percentage ?? '0' ?>%</div>
                            <div class="quick-stat-label">Attendance</div>
                        </div>
                        <div class="quick-stat">
                            <div class="quick-stat-value"><?= $pending_fees ?? '0' ?></div>
                            <div class="quick-stat-label">Pending Fees</div>
                        </div>
                    </div>
                    
                    <div class="dropdown">
                        <button class="btn btn-link text-dark" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="/profile">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                            <a class="dropdown-item" href="/student/help">
                                <i class="fas fa-question-circle me-2"></i>Help
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="student-main">
                <?php $this->component('alert') ?>
                <?= $content ?>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.getElementById('studentSidebar').classList.toggle('show');
        }

        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    </script>
</body>
</html>