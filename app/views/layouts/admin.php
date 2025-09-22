<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Dashboard' ?> - College ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #059669;
            --danger-color: #dc2626;
            --warning-color: #d97706;
            --info-color: #0891b2;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-color);
            font-size: 14px;
            line-height: 1.6;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--dark-color) 0%, #334155 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .admin-sidebar.collapsed {
            width: 70px;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.2rem;
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
            border-left-color: var(--primary-color);
        }

        .nav-link.active {
            background-color: rgba(37, 99, 235, 0.2);
            color: white;
            border-left-color: var(--primary-color);
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }

        .nav-submenu {
            background-color: rgba(0,0,0,0.2);
            padding: 0.5rem 0;
        }

        .nav-submenu .nav-link {
            padding-left: 3rem;
            font-size: 0.9rem;
        }

        .admin-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }

        .admin-content.expanded {
            margin-left: 70px;
        }

        .admin-header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--secondary-color);
            margin-right: 1rem;
            cursor: pointer;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-dropdown {
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: none;
            color: var(--dark-color);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .admin-main {
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }

        .page-subtitle {
            color: var(--secondary-color);
            margin-top: 0.25rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-title {
            font-size: 0.9rem;
            color: var(--secondary-color);
            font-weight: 500;
            margin: 0;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }

        .stat-change {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }

        .stat-change.positive {
            color: var(--success-color);
        }

        .stat-change.negative {
            color: var(--danger-color);
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            border-radius: 12px 12px 0 0;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            color: var(--dark-color);
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
            transform: translateY(-1px);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem 1.25rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 0.625rem 0.875rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .modal-header {
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 600;
            color: var(--dark-color);
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <nav class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <h4><i class="fas fa-graduation-cap me-2"></i>College ERP</h4>
            </div>
            
            <ul class="sidebar-nav list-unstyled">
                <li class="nav-item">
                    <a href="/admin/dashboard" class="nav-link <?= is_active('/admin/dashboard') ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#userManagement">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse nav-submenu" id="userManagement">
                        <a href="/admin/users" class="nav-link <?= is_active('/admin/users') ?>">
                            <i class="fas fa-user"></i>Users
                        </a>
                        <a href="/admin/roles" class="nav-link <?= is_active('/admin/roles') ?>">
                            <i class="fas fa-user-tag"></i>Roles
                        </a>
                        <a href="/admin/permissions" class="nav-link <?= is_active('/admin/permissions') ?>">
                            <i class="fas fa-key"></i>Permissions
                        </a>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#studentManagement">
                        <i class="fas fa-user-graduate"></i>
                        <span>Students</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse nav-submenu" id="studentManagement">
                        <a href="/students" class="nav-link">
                            <i class="fas fa-list"></i>All Students
                        </a>
                        <a href="/students/admissions" class="nav-link">
                            <i class="fas fa-user-plus"></i>Admissions
                        </a>
                        <a href="/students/registrations" class="nav-link">
                            <i class="fas fa-id-card"></i>Registrations
                        </a>
                        <a href="/students/transfers" class="nav-link">
                            <i class="fas fa-exchange-alt"></i>Transfers
                        </a>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#facultyManagement">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Faculty</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse nav-submenu" id="facultyManagement">
                        <a href="/faculty" class="nav-link">
                            <i class="fas fa-users"></i>All Faculty
                        </a>
                        <a href="/faculty/departments" class="nav-link">
                            <i class="fas fa-building"></i>Departments
                        </a>
                        <a href="/faculty/designations" class="nav-link">
                            <i class="fas fa-sitemap"></i>Designations
                        </a>
                        <a href="/faculty/salary" class="nav-link">
                            <i class="fas fa-money-bill"></i>Salary
                        </a>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#academicManagement">
                        <i class="fas fa-book"></i>
                        <span>Academic</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse nav-submenu" id="academicManagement">
                        <a href="/academic/courses" class="nav-link">
                            <i class="fas fa-graduation-cap"></i>Courses
                        </a>
                        <a href="/academic/subjects" class="nav-link">
                            <i class="fas fa-book-open"></i>Subjects
                        </a>
                        <a href="/academic/classes" class="nav-link">
                            <i class="fas fa-door-open"></i>Classes
                        </a>
                        <a href="/academic/timetable" class="nav-link">
                            <i class="fas fa-calendar-alt"></i>Timetable
                        </a>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a href="/examinations" class="nav-link <?= is_active('/examinations') ?>">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Examinations</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/attendance" class="nav-link <?= is_active('/attendance') ?>">
                        <i class="fas fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/fees" class="nav-link <?= is_active('/fees') ?>">
                        <i class="fas fa-credit-card"></i>
                        <span>Fee Management</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/library" class="nav-link <?= is_active('/library') ?>">
                        <i class="fas fa-book-reader"></i>
                        <span>Library</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/reports" class="nav-link <?= is_active('/reports') ?>">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#systemManagement">
                        <i class="fas fa-cog"></i>
                        <span>System</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse nav-submenu" id="systemManagement">
                        <a href="/admin/settings" class="nav-link">
                            <i class="fas fa-sliders-h"></i>Settings
                        </a>
                        <a href="/admin/backups" class="nav-link">
                            <i class="fas fa-database"></i>Backups
                        </a>
                        <a href="/admin/logs" class="nav-link">
                            <i class="fas fa-file-alt"></i>Logs
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="admin-content" id="adminContent">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <h5 class="mb-0"><?= $title ?? 'Dashboard' ?></h5>
                        <?php if (isset($subtitle)): ?>
                            <small class="text-muted"><?= $subtitle ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="header-right">
                    <!-- Notifications -->
                    <div class="notification-dropdown dropdown">
                        <button class="btn btn-link text-dark" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <h6 class="dropdown-header">Notifications</h6>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-info-circle text-info me-2"></i>
                                New student admission
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                Fee payment overdue
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-center" href="/notifications">View All</a>
                        </div>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="user-dropdown dropdown">
                        <button class="dropdown-toggle" data-bs-toggle="dropdown">
                            <div class="user-avatar">
                                <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
                            </div>
                            <span><?= $user['name'] ?? 'Admin' ?></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="/profile">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                            <a class="dropdown-item" href="/settings">
                                <i class="fas fa-cog me-2"></i>Settings
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
            <main class="admin-main">
                <?php $this->component('alert') ?>
                <?= $content ?>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('adminSidebar');
            const content = document.getElementById('adminContent');
            
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
        });

        // Initialize DataTables
        $(document).ready(function() {
            $('.data-table').DataTable({
                responsive: true,
                pageLength: 25,
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });

        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);

        // AJAX form handling
        $('.ajax-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = form.find('button[type="submit"]');
            const originalText = btn.html();
            
            btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...').prop('disabled', true);
            
            $.ajax({
                url: form.attr('action'),
                method: form.attr('method') || 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || 'An error occurred');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });
    </script>
</body>
</html>