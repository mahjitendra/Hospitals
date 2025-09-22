<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Faculty Portal' ?> - College ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7c3aed;
            --secondary-color: #64748b;
            --success-color: #059669;
            --danger-color: #dc2626;
            --warning-color: #d97706;
            --info-color: #0891b2;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
            --sidebar-width: 270px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-color);
            font-size: 14px;
            line-height: 1.6;
        }

        .faculty-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .faculty-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, #6d28d9 100%);
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

        .faculty-avatar {
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

        .faculty-info h5 {
            margin: 0;
            font-weight: 600;
        }

        .faculty-info small {
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

        .faculty-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }

        .faculty-header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .today-schedule {
            background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .schedule-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .schedule-item:last-child {
            border-bottom: none;
        }

        .faculty-main {
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
        }

        @media (max-width: 768px) {
            .faculty-sidebar {
                transform: translateX(-100%);
            }

            .faculty-sidebar.show {
                transform: translateX(0);
            }

            .faculty-content {
                margin-left: 0;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="faculty-wrapper">
        <!-- Sidebar -->
        <nav class="faculty-sidebar" id="facultySidebar">
            <div class="sidebar-header">
                <div class="faculty-avatar">
                    <?= strtoupper(substr($user['name'] ?? 'F', 0, 1)) ?>
                </div>
                <div class="faculty-info">
                    <h5><?= $user['name'] ?? 'Faculty' ?></h5>
                    <small><?= $faculty['employee_id'] ?? 'Employee ID' ?></small>
                </div>
            </div>
            
            <ul class="sidebar-nav list-unstyled">
                <li class="nav-item">
                    <a href="/faculty/dashboard" class="nav-link <?= is_active('/faculty/dashboard') ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/faculty/profile" class="nav-link <?= is_active('/faculty/profile') ?>">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/faculty/classes" class="nav-link <?= is_active('/faculty/classes') ?>">
                        <i class="fas fa-chalkboard"></i>
                        <span>My Classes</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/faculty/subjects" class="nav-link <?= is_active('/faculty/subjects') ?>">
                        <i class="fas fa-book"></i>
                        <span>My Subjects</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/faculty/timetable" class="nav-link <?= is_active('/faculty/timetable') ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Timetable</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/faculty/attendance" class="nav-link <?= is_active('/faculty/attendance') ?>">
                        <i class="fas fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/faculty/students" class="nav-link <?= is_active('/faculty/students') ?>">
                        <i class="fas fa-users"></i>
                        <span>Students</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/faculty/exams" class="nav-link <?= is_active('/faculty/exams') ?>">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Examinations</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/faculty/leave" class="nav-link <?= is_active('/faculty/leave') ?>">
                        <i class="fas fa-calendar-times"></i>
                        <span>Leave</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/faculty/salary" class="nav-link <?= is_active('/faculty/salary') ?>">
                        <i class="fas fa-money-bill"></i>
                        <span>Salary</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="faculty-content">
            <!-- Header -->
            <header class="faculty-header">
                <div class="header-title">
                    <h4><?= $title ?? 'Faculty Portal' ?></h4>
                </div>
                
                <div class="header-actions">
                    <button class="btn btn-link text-dark d-md-none" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="dropdown">
                        <button class="btn btn-link text-dark" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger">2</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <h6 class="dropdown-header">Notifications</h6>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-info-circle text-info me-2"></i>
                                New assignment submitted
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                Attendance pending
                            </a>
                        </div>
                    </div>
                    
                    <div class="dropdown">
                        <button class="btn btn-link text-dark" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="/profile">Profile</a>
                            <a class="dropdown-item" href="/settings">Settings</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/logout">Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="faculty-main">
                <?php $this->component('alert') ?>
                <?= $content ?>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        function toggleSidebar() {
            document.getElementById('facultySidebar').classList.toggle('show');
        }

        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    </script>
</body>
</html>