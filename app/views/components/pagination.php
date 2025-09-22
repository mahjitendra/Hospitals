<?php if ($pagination['total_pages'] > 1): ?>
<nav aria-label="Page navigation" class="pagination-wrapper">
    <div class="pagination-info">
        <span class="text-muted">
            Showing <?= $pagination['from'] ?> to <?= $pagination['to'] ?> of <?= $pagination['total'] ?> results
        </span>
    </div>
    
    <ul class="pagination pagination-custom justify-content-center">
        <!-- First Page -->
        <?php if ($pagination['current_page'] > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pagination['first_page_url'] ?>" aria-label="First">
                    <i class="fas fa-angle-double-left"></i>
                </a>
            </li>
        <?php endif; ?>
        
        <!-- Previous Page -->
        <?php if ($pagination['current_page'] > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pagination['prev_page_url'] ?>" aria-label="Previous">
                    <i class="fas fa-angle-left"></i>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link">
                    <i class="fas fa-angle-left"></i>
                </span>
            </li>
        <?php endif; ?>
        
        <!-- Page Numbers -->
        <?php
        $start = max(1, $pagination['current_page'] - 2);
        $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
        
        // Show first page if not in range
        if ($start > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?= str_replace('{page}', 1, $pagination['page_url_template']) ?>">1</a>
            </li>
            <?php if ($start > 2): ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Current range -->
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i == $pagination['current_page']): ?>
                <li class="page-item active">
                    <span class="page-link"><?= $i ?></span>
                </li>
            <?php else: ?>
                <li class="page-item">
                    <a class="page-link" href="<?= str_replace('{page}', $i, $pagination['page_url_template']) ?>"><?= $i ?></a>
                </li>
            <?php endif; ?>
        <?php endfor; ?>
        
        <!-- Show last page if not in range -->
        <?php if ($end < $pagination['total_pages']): ?>
            <?php if ($end < $pagination['total_pages'] - 1): ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            <?php endif; ?>
            <li class="page-item">
                <a class="page-link" href="<?= str_replace('{page}', $pagination['total_pages'], $pagination['page_url_template']) ?>">
                    <?= $pagination['total_pages'] ?>
                </a>
            </li>
        <?php endif; ?>
        
        <!-- Next Page -->
        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pagination['next_page_url'] ?>" aria-label="Next">
                    <i class="fas fa-angle-right"></i>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link">
                    <i class="fas fa-angle-right"></i>
                </span>
            </li>
        <?php endif; ?>
        
        <!-- Last Page -->
        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pagination['last_page_url'] ?>" aria-label="Last">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    
    <!-- Per Page Selector -->
    <div class="pagination-controls">
        <div class="per-page-selector">
            <label for="perPage" class="form-label">Show:</label>
            <select id="perPage" class="form-select form-select-sm" onchange="changePerPage(this.value)">
                <option value="10" <?= $pagination['per_page'] == 10 ? 'selected' : '' ?>>10</option>
                <option value="25" <?= $pagination['per_page'] == 25 ? 'selected' : '' ?>>25</option>
                <option value="50" <?= $pagination['per_page'] == 50 ? 'selected' : '' ?>>50</option>
                <option value="100" <?= $pagination['per_page'] == 100 ? 'selected' : '' ?>>100</option>
            </select>
            <span class="form-text">per page</span>
        </div>
        
        <!-- Jump to Page -->
        <div class="page-jumper">
            <label for="jumpToPage" class="form-label">Go to:</label>
            <input type="number" id="jumpToPage" class="form-control form-control-sm" 
                   min="1" max="<?= $pagination['total_pages'] ?>" 
                   placeholder="Page" style="width: 80px;">
            <button class="btn btn-sm btn-outline-secondary" onclick="jumpToPage()">
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>
</nav>

<style>
.pagination-wrapper {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    margin: 1.5rem 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
}

.pagination-info {
    text-align: center;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.pagination-custom {
    margin-bottom: 1rem;
}

.pagination-custom .page-link {
    border: 1px solid #d1d5db;
    color: #374151;
    padding: 0.5rem 0.75rem;
    margin: 0 2px;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.pagination-custom .page-link:hover {
    background-color: #f3f4f6;
    border-color: #9ca3af;
    color: #1f2937;
    transform: translateY(-1px);
}

.pagination-custom .page-item.active .page-link {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

.pagination-custom .page-item.disabled .page-link {
    background-color: #f9fafb;
    border-color: #e5e7eb;
    color: #9ca3af;
}

.pagination-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.per-page-selector,
.page-jumper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.per-page-selector label,
.page-jumper label {
    font-size: 0.9rem;
    color: #6b7280;
    margin: 0;
    white-space: nowrap;
}

.per-page-selector select,
.page-jumper input {
    font-size: 0.9rem;
}

.form-text {
    font-size: 0.8rem;
    color: #6b7280;
}

/* Loading state */
.pagination-wrapper.loading {
    opacity: 0.6;
    pointer-events: none;
}

.pagination-wrapper.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #e5e7eb;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .pagination-controls {
        flex-direction: column;
        text-align: center;
    }
    
    .pagination-custom {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .pagination-custom .page-item {
        margin: 2px;
    }
    
    .pagination-info {
        font-size: 0.8rem;
    }
}

/* Compact pagination for small spaces */
.pagination-compact {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 6px;
    margin: 1rem 0;
}

.pagination-compact .pagination-info {
    margin: 0;
    font-size: 0.8rem;
}

.pagination-compact .pagination-nav {
    display: flex;
    gap: 0.5rem;
}

.pagination-compact .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}
</style>

<script>
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', 1); // Reset to first page
    window.location.href = url.toString();
}

function jumpToPage() {
    const pageInput = document.getElementById('jumpToPage');
    const page = parseInt(pageInput.value);
    
    if (page && page >= 1 && page <= <?= $pagination['total_pages'] ?>) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location.href = url.toString();
    } else {
        alert('Please enter a valid page number between 1 and <?= $pagination['total_pages'] ?>');
        pageInput.focus();
    }
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'ArrowLeft':
                e.preventDefault();
                const prevLink = document.querySelector('.pagination .page-item:not(.disabled) .page-link[aria-label="Previous"]');
                if (prevLink) prevLink.click();
                break;
            case 'ArrowRight':
                e.preventDefault();
                const nextLink = document.querySelector('.pagination .page-item:not(.disabled) .page-link[aria-label="Next"]');
                if (nextLink) nextLink.click();
                break;
        }
    }
});

// AJAX pagination (optional)
function loadPage(page, container = null) {
    if (container) {
        const wrapper = document.querySelector(container);
        wrapper.classList.add('loading');
        
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        url.searchParams.set('ajax', '1');
        
        fetch(url.toString())
            .then(response => response.text())
            .then(html => {
                wrapper.innerHTML = html;
                wrapper.classList.remove('loading');
                
                // Update URL without reload
                history.pushState(null, '', url.toString().replace('&ajax=1', ''));
            })
            .catch(error => {
                console.error('Pagination error:', error);
                wrapper.classList.remove('loading');
            });
    }
}
</script>
<?php endif; ?>