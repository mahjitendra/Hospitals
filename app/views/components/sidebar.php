<nav class="main-sidebar" id="mainSidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fas fa-graduation-cap"></i>
            <span class="brand-text">College ERP</span>
        </div>
        
        <?php if (auth()->check()): ?>
            <div class="user-panel">
                <div class="user-avatar">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= asset('uploads/avatars/' . $user['avatar']) ?>" alt="Avatar">
                    <?php else: ?>
                        <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?= $user['name'] ?? 'User' ?></div>
                    <div class="user-role"><?= ucfirst($user['primary_role'] ?? 'User') ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="sidebar-menu">
        <ul class="nav nav-pills nav-sidebar flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="/dashboard" class="nav-link <?= is_active('/dashboard') ?>">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                </a>
            </li>
            
            <?php if (hasAnyRole([ROLE_ADMIN, ROLE_PRINCIPAL])): ?>
            <!-- Admin Section -->
            <li class="nav-item has-treeview <?= is_active('/admin') ?>">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-shield-alt"></i>
                    <p>
                        Administration
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="/admin/users" class="nav-link <?= is_active('/admin/users') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>User Management</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/roles" class="nav-link <?= is_active('/admin/roles') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Roles & Permissions</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/settings" class="nav-link <?= is_active('/admin/settings') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>System Settings</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/backups" class="nav-link <?= is_active('/admin/backups') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Backups</p>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            
            <?php if (hasAnyRole([ROLE_ADMIN, ROLE_TEACHER, ROLE_HOD])): ?>
            <!-- Student Management -->
            <li class="nav-item has-treeview <?= is_active('/students') ?>">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-user-graduate"></i>
                    <p>
                        Students
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="/students" class="nav-link <?= is_active('/students', 'exact') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>All Students</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/students/admissions" class="nav-link <?= is_active('/students/admissions') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Admissions</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/students/registrations" class="nav-link <?= is_active('/students/registrations') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Registrations</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/students/transfers" class="nav-link <?= is_active('/students/transfers') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Transfers</p>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            
            <?php if (hasAnyRole([ROLE_ADMIN, ROLE_HOD, ROLE_PRINCIPAL])): ?>
            <!-- Faculty Management -->
            <li class="nav-item has-treeview <?= is_active('/faculty') ?>">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-chalkboard-teacher"></i>
                    <p>
                        Faculty
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="/faculty" class="nav-link <?= is_active('/faculty', 'exact') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>All Faculty</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/faculty/departments" class="nav-link <?= is_active('/faculty/departments') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Departments</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/faculty/designations" class="nav-link <?= is_active('/faculty/designations') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Designations</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/faculty/salary" class="nav-link <?= is_active('/faculty/salary') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Salary Management</p>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
            
            <!-- Academic Section -->
            <li class="nav-item has-treeview <?= is_active('/academic') ?>">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-book"></i>
                    <p>
                        Academic
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="/academic/courses" class="nav-link <?= is_active('/academic/courses') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Courses</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/academic/subjects" class="nav-link <?= is_active('/academic/subjects') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Subjects</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/academic/classes" class="nav-link <?= is_active('/academic/classes') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Classes</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/academic/timetable" class="nav-link <?= is_active('/academic/timetable') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Timetable</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/academic/calendar" class="nav-link <?= is_active('/academic/calendar') ?>">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Academic Calendar</p>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Examinations -->
            <li class="nav-item">
                <a href="/examinations" class="nav-link <?= is_active('/examinations') ?>">
                    <i class="nav-icon fas fa-clipboard-list"></i>
                    <p>Examinations</p>
                </a>
            </li>
            
            <!-- Attendance -->
            <li class="nav-item">
                <a href="/attendance" class="nav-link <?= is_active('/attendance') ?>">
                    <i class="nav-icon fas fa-calendar-check"></i>
                    <p>Attendance</p>
                </a>
            </li>
            
            <!-- Fee Management -->
            <li class="nav-item">
                <a href="/fees" class="nav-link <?= is_active('/fees') ?>">
                    <i class="nav-icon fas fa-credit-card"></i>
                    <p>Fee Management</p>
                </a>
            </li>
            
            <!-- Library -->
            <li class="nav-item">
                <a href="/library" class="nav-link <?= is_active('/library') ?>">
                    <i class="nav-icon fas fa-book-reader"></i>
                    <p>Library</p>
                </a>
            </li>
            
            <!-- Reports -->
            <li class="nav-item">
                <a href="/reports" class="nav-link <?= is_active('/reports') ?>">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <p>Reports</p>
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.main-sidebar {
    width: 250px;
    background: #343a40;
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
}

.sidebar-brand {
    display: flex;
    align-items: center;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.sidebar-brand i {
    margin-right: 0.5rem;
    color: #3b82f6;
}

.user-panel {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-panel .user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}

.user-info {
    flex: 1;
}

.user-name {
    font-weight: 500;
    font-size: 0.9rem;
}

.user-role {
    font-size: 0.8rem;
    opacity: 0.7;
}

.sidebar-menu {
    padding: 1rem 0;
}

.nav-sidebar .nav-item {
    margin-bottom: 0.25rem;
}

.nav-sidebar .nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.nav-sidebar .nav-link:hover {
    background-color: rgba(255,255,255,0.1);
    color: white;
    border-left-color: #3b82f6;
}

.nav-sidebar .nav-link.active {
    background-color: rgba(59, 130, 246, 0.2);
    color: white;
    border-left-color: #3b82f6;
}

.nav-icon {
    width: 20px;
    margin-right: 0.75rem;
    text-align: center;
}

.nav-treeview {
    background-color: rgba(0,0,0,0.2);
    padding: 0.5rem 0;
}

.nav-treeview .nav-link {
    padding-left: 3rem;
    font-size: 0.9rem;
}

.has-treeview > .nav-link::after {
    content: '\f105';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    margin-left: auto;
    transition: transform 0.3s ease;
}

.has-treeview.menu-open > .nav-link::after {
    transform: rotate(90deg);
}

@media (max-width: 768px) {
    .main-sidebar {
        transform: translateX(-100%);
    }
    
    .main-sidebar.show {
        transform: translateX(0);
    }
}
</style>

<script>
// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle treeview menus
    document.querySelectorAll('.has-treeview > .nav-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            const submenu = parent.querySelector('.nav-treeview');
            
            if (submenu) {
                parent.classList.toggle('menu-open');
                if (parent.classList.contains('menu-open')) {
                    submenu.style.display = 'block';
                } else {
                    submenu.style.display = 'none';
                }
            }
        });
    });
    
    // Auto-expand active menu
    document.querySelectorAll('.nav-treeview .nav-link.active').forEach(function(activeLink) {
        const treeview = activeLink.closest('.has-treeview');
        if (treeview) {
            treeview.classList.add('menu-open');
            treeview.querySelector('.nav-treeview').style.display = 'block';
        }
    });
});
</script>