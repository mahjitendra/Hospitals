<nav aria-label="breadcrumb" class="breadcrumb-nav">
    <div class="container-fluid">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="/dashboard" class="breadcrumb-link">
                    <i class="fas fa-home"></i>
                    <span class="d-none d-sm-inline ms-1">Dashboard</span>
                </a>
            </li>
            
            <?php if (!empty($breadcrumbs)): ?>
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if ($index === count($breadcrumbs) - 1): ?>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php if (!empty($crumb['icon'])): ?>
                                <i class="<?= $crumb['icon'] ?> me-1"></i>
                            <?php endif; ?>
                            <?= $crumb['title'] ?>
                        </li>
                    <?php else: ?>
                        <li class="breadcrumb-item">
                            <a href="<?= $crumb['url'] ?>" class="breadcrumb-link">
                                <?php if (!empty($crumb['icon'])): ?>
                                    <i class="<?= $crumb['icon'] ?> me-1"></i>
                                <?php endif; ?>
                                <?= $crumb['title'] ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= $title ?? 'Page' ?>
                </li>
            <?php endif; ?>
        </ol>
        
        <!-- Page Actions -->
        <?php if (!empty($page_actions)): ?>
            <div class="breadcrumb-actions">
                <?php foreach ($page_actions as $action): ?>
                    <a href="<?= $action['url'] ?>" 
                       class="btn btn-sm <?= $action['class'] ?? 'btn-primary' ?> me-2">
                        <?php if (!empty($action['icon'])): ?>
                            <i class="<?= $action['icon'] ?> me-1"></i>
                        <?php endif; ?>
                        <?= $action['title'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</nav>

<style>
.breadcrumb-nav {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.75rem 0;
    position: sticky;
    top: 0;
    z-index: 1010;
}

.breadcrumb-nav .container-fluid {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.breadcrumb {
    background: none;
    padding: 0;
    margin: 0;
    font-size: 0.9rem;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-link {
    color: #6b7280;
    text-decoration: none;
    transition: color 0.3s ease;
    display: flex;
    align-items: center;
}

.breadcrumb-link:hover {
    color: #3b82f6;
}

.breadcrumb-item.active {
    color: #1f2937;
    font-weight: 500;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: '/';
    color: #d1d5db;
    margin: 0 0.5rem;
}

.breadcrumb-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.breadcrumb-actions .btn {
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.breadcrumb-actions .btn:hover {
    transform: translateY(-1px);
}

/* Quick filters */
.breadcrumb-filters {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-left: 2rem;
}

.filter-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-item label {
    font-size: 0.8rem;
    color: #6b7280;
    margin: 0;
    white-space: nowrap;
}

.filter-item select,
.filter-item input {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    min-width: 100px;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .breadcrumb-nav .container-fluid {
        flex-direction: column;
        gap: 1rem;
    }
    
    .breadcrumb-actions {
        width: 100%;
        justify-content: center;
    }
    
    .breadcrumb-filters {
        margin-left: 0;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .breadcrumb-item span.d-none.d-sm-inline {
        display: none !important;
    }
}

/* Animation for page transitions */
.breadcrumb-nav {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
// Breadcrumb functionality
document.addEventListener('DOMContentLoaded', function() {
    // Auto-update breadcrumb based on current page
    updateBreadcrumbFromPath();
    
    // Handle filter changes
    document.querySelectorAll('.breadcrumb-filters select, .breadcrumb-filters input').forEach(function(element) {
        element.addEventListener('change', function() {
            updatePageFilters();
        });
    });
});

function updateBreadcrumbFromPath() {
    const path = window.location.pathname;
    const segments = path.split('/').filter(segment => segment);
    
    // Auto-generate breadcrumbs if not provided
    if (!document.querySelector('.breadcrumb-item:not(:first-child)')) {
        const breadcrumb = document.querySelector('.breadcrumb');
        
        segments.forEach((segment, index) => {
            if (index === 0) return; // Skip first segment (usually empty)
            
            const li = document.createElement('li');
            li.className = index === segments.length - 1 ? 'breadcrumb-item active' : 'breadcrumb-item';
            
            const title = segment.charAt(0).toUpperCase() + segment.slice(1).replace(/-/g, ' ');
            
            if (index === segments.length - 1) {
                li.textContent = title;
                li.setAttribute('aria-current', 'page');
            } else {
                const a = document.createElement('a');
                a.href = '/' + segments.slice(0, index + 1).join('/');
                a.className = 'breadcrumb-link';
                a.textContent = title;
                li.appendChild(a);
            }
            
            breadcrumb.appendChild(li);
        });
    }
}

function updatePageFilters() {
    const filters = {};
    
    document.querySelectorAll('.breadcrumb-filters select, .breadcrumb-filters input').forEach(function(element) {
        if (element.value) {
            filters[element.name] = element.value;
        }
    });
    
    // Update URL with filters
    const url = new URL(window.location);
    Object.keys(filters).forEach(key => {
        url.searchParams.set(key, filters[key]);
    });
    
    // Remove empty filters
    Object.keys(Object.fromEntries(url.searchParams)).forEach(key => {
        if (!filters[key]) {
            url.searchParams.delete(key);
        }
    });
    
    window.location.href = url.toString();
}

// Helper function to generate breadcrumbs
function generateBreadcrumbs(items) {
    const breadcrumb = document.querySelector('.breadcrumb');
    breadcrumb.innerHTML = '';
    
    // Add home
    const homeLi = document.createElement('li');
    homeLi.className = 'breadcrumb-item';
    homeLi.innerHTML = '<a href="/dashboard" class="breadcrumb-link"><i class="fas fa-home"></i><span class="d-none d-sm-inline ms-1">Dashboard</span></a>';
    breadcrumb.appendChild(homeLi);
    
    // Add items
    items.forEach((item, index) => {
        const li = document.createElement('li');
        li.className = index === items.length - 1 ? 'breadcrumb-item active' : 'breadcrumb-item';
        
        if (index === items.length - 1) {
            li.innerHTML = (item.icon ? `<i class="${item.icon} me-1"></i>` : '') + item.title;
            li.setAttribute('aria-current', 'page');
        } else {
            li.innerHTML = `<a href="${item.url}" class="breadcrumb-link">${item.icon ? `<i class="${item.icon} me-1"></i>` : ''}${item.title}</a>`;
        }
        
        breadcrumb.appendChild(li);
    });
}
</script>