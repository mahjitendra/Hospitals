<header class="main-header">
    <div class="container-fluid">
        <div class="header-content">
            <div class="header-left">
                <button class="sidebar-toggle btn btn-link" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="header-title">
                    <h4 class="mb-0"><?= $title ?? 'Dashboard' ?></h4>
                    <?php if (isset($subtitle)): ?>
                        <small class="text-muted"><?= $subtitle ?></small>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="header-right">
                <!-- Search -->
                <div class="header-search d-none d-md-block">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search..." id="globalSearch">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-actions d-none d-lg-flex">
                    <?php if (hasRole(ROLE_ADMIN)): ?>
                        <a href="/admin/users/create" class="btn btn-sm btn-primary me-2">
                            <i class="fas fa-user-plus"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (hasRole(ROLE_TEACHER)): ?>
                        <a href="/attendance/mark" class="btn btn-sm btn-success me-2">
                            <i class="fas fa-check"></i>
                        </a>
                    <?php endif; ?>
                    
                    <a href="/notifications" class="btn btn-sm btn-info me-2">
                        <i class="fas fa-bell"></i>
                        <?php if ($unread_notifications > 0): ?>
                            <span class="badge bg-danger"><?= $unread_notifications ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <!-- Notifications -->
                <div class="notification-dropdown dropdown">
                    <button class="btn btn-link text-dark position-relative" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <?php if ($unread_notifications > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $unread_notifications ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    
                    <div class="dropdown-menu dropdown-menu-end notification-menu">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <span>Notifications</span>
                            <a href="/notifications/mark-all-read" class="btn btn-sm btn-link p-0">Mark all read</a>
                        </div>
                        
                        <?php if (!empty($recent_notifications)): ?>
                            <?php foreach ($recent_notifications as $notification): ?>
                                <a class="dropdown-item notification-item <?= $notification['is_read'] ? '' : 'unread' ?>" 
                                   href="/notifications/<?= $notification['id'] ?>">
                                    <div class="notification-icon">
                                        <i class="fas fa-<?= $notification['icon'] ?? 'info-circle' ?> text-<?= $notification['type'] ?>"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-title"><?= $notification['title'] ?></div>
                                        <div class="notification-time"><?= timeAgo($notification['created_at']) ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dropdown-item text-center text-muted">
                                No new notifications
                            </div>
                        <?php endif; ?>
                        
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="/notifications">
                            View All Notifications
                        </a>
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="user-dropdown dropdown">
                    <button class="btn btn-link text-dark dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="<?= asset('uploads/avatars/' . $user['avatar']) ?>" alt="Avatar">
                            <?php else: ?>
                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <span class="d-none d-md-inline"><?= $user['name'] ?? 'User' ?></span>
                    </button>
                    
                    <div class="dropdown-menu dropdown-menu-end user-menu">
                        <div class="dropdown-header">
                            <strong><?= $user['name'] ?? 'User' ?></strong>
                            <small class="d-block text-muted"><?= $user['email'] ?? '' ?></small>
                        </div>
                        
                        <div class="dropdown-divider"></div>
                        
                        <a class="dropdown-item" href="/profile">
                            <i class="fas fa-user me-2"></i>
                            My Profile
                        </a>
                        
                        <a class="dropdown-item" href="/settings">
                            <i class="fas fa-cog me-2"></i>
                            Settings
                        </a>
                        
                        <?php if (hasRole(ROLE_ADMIN)): ?>
                            <a class="dropdown-item" href="/admin">
                                <i class="fas fa-shield-alt me-2"></i>
                                Admin Panel
                            </a>
                        <?php endif; ?>
                        
                        <div class="dropdown-divider"></div>
                        
                        <a class="dropdown-item" href="/help">
                            <i class="fas fa-question-circle me-2"></i>
                            Help & Support
                        </a>
                        
                        <a class="dropdown-item" href="/logout">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
.main-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1020;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sidebar-toggle {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #6b7280;
    padding: 0.5rem;
}

.header-title h4 {
    color: #1f2937;
    font-weight: 600;
    margin: 0;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-search .input-group {
    width: 250px;
}

.quick-actions {
    display: flex;
    gap: 0.5rem;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.notification-menu {
    width: 320px;
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    text-decoration: none;
    color: inherit;
}

.notification-item:hover {
    background-color: #f9fafb;
}

.notification-item.unread {
    background-color: #eff6ff;
}

.notification-icon {
    margin-right: 0.75rem;
    margin-top: 0.25rem;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 500;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.notification-time {
    font-size: 0.8rem;
    color: #6b7280;
}

.user-menu {
    width: 220px;
}

@media (max-width: 768px) {
    .header-search {
        display: none !important;
    }
    
    .quick-actions {
        display: none !important;
    }
    
    .header-title h4 {
        font-size: 1.1rem;
    }
}
</style>