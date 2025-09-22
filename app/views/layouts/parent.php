<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Parent Portal' ?> - College ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc2626;
            --secondary-color: #64748b;
            --success-color: #059669;
            --danger-color: #dc2626;
            --warning-color: #d97706;
            --info-color: #0891b2;
            --dark-color: #1f2937;
            --light-color: #fef2f2;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-color);
            font-size: 14px;
            line-height: 1.6;
        }

        .parent-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .parent-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, #b91c1c 100%);
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

        .parent-avatar {
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

        .parent-info h5 {
            margin: 0;
            font-weight: 600;
        }

        .parent-info small {
            opacity: 0.8;
        }

        .children-selector {
            margin: 1rem 1.5rem;
            padding: 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }

        .child-tab {
            display: block;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            background: rgba(255,255,255,0.1);
            border-radius: 6px;
            color: white;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .child-tab:hover, .child-tab.active {
            background: rgba(255,255,255,0.2);
            color: white;
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

        .parent-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }

        .parent-header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .parent-main {
            padding: 2rem;
        }

        .child-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .child-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            transition: transform 0.2s ease;
        }

        .child-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .child-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .child-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 1rem;
        }

        .child-info h6 {
            margin: 0;
            font-weight: 600;
            color: var(--dark-color);
        }

        .child-info small {
            color: var(--secondary-color);
        }

        .child-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .child-stat {
            text-align: center;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .child-stat-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .child-stat-label {
            font-size: 0.8rem;
            color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .parent-sidebar {
                transform: translateX(-100%);
            }

            .parent-sidebar.show {
                transform: translateX(0);
            }

            .parent-content {
                margin-left: 0;
            }

            .child-overview {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="parent-wrapper">
        <!-- Sidebar -->
        <nav class="parent-sidebar" id="parentSidebar">
            <div class="sidebar-header">
                <div class="parent-avatar">
                    <?= strtoupper(substr($user['name'] ?? 'P', 0, 1)) ?>
                </div>
                <div class="parent-info">
                    <h5><?= $user['name'] ?? 'Parent' ?></h5>
                    <small>Parent Portal</small>
                </div>
            </div>
            
            <?php if (!empty($children)): ?>
            <div class="children-selector">
                <h6 class="mb-2">My Children</h6>
                <?php foreach ($children as $child): ?>
                <a href="/parent/child/<?= $child['id'] ?>" class="child-tab">
                    <strong><?= $child['first_name'] ?> <?= $child['last_name'] ?></strong>
                    <small class="d-block"><?= $child['class_name'] ?? 'Class' ?></small>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <ul class="sidebar-nav list-unstyled">
                <li class="nav-item">
                    <a href="/parent/dashboard" class="nav-link <?= is_active('/parent/dashboard') ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/parent/children" class="nav-link <?= is_active('/parent/children') ?>">
                        <i class="fas fa-child"></i>
                        <span>My Children</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/parent/attendance" class="nav-link <?= is_active('/parent/attendance') ?>">
                        <i class="fas fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/parent/results" class="nav-link <?= is_active('/parent/results') ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Results</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/parent/fees" class="nav-link <?= is_active('/parent/fees') ?>">
                        <i class="fas fa-credit-card"></i>
                        <span>Fee Status</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/parent/communication" class="nav-link <?= is_active('/parent/communication') ?>">
                        <i class="fas fa-comments"></i>
                        <span>Communication</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/parent/events" class="nav-link <?= is_active('/parent/events') ?>">
                        <i class="fas fa-calendar"></i>
                        <span>Events</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="parent-content">
            <!-- Header -->
            <header class="parent-header">
                <div class="header-title">
                    <h4><?= $title ?? 'Parent Portal' ?></h4>
                </div>
                
                <div class="header-actions">
                    <button class="btn btn-link text-dark d-md-none" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    
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
            <main class="parent-main">
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
            document.getElementById('parentSidebar').classList.toggle('show');
        }

        // Auto-hide alerts
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    </script>
</body>
</html>