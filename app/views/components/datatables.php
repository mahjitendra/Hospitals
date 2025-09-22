<div class="datatable-wrapper">
    <div class="datatable-header">
        <div class="datatable-title">
            <h5 class="mb-0"><?= $table_title ?? 'Data Table' ?></h5>
            <?php if (!empty($table_subtitle)): ?>
                <small class="text-muted"><?= $table_subtitle ?></small>
            <?php endif; ?>
        </div>
        
        <div class="datatable-actions">
            <?php if (!empty($table_actions)): ?>
                <?php foreach ($table_actions as $action): ?>
                    <a href="<?= $action['url'] ?>" 
                       class="btn btn-sm <?= $action['class'] ?? 'btn-primary' ?> me-2">
                        <?php if (!empty($action['icon'])): ?>
                            <i class="<?= $action['icon'] ?> me-1"></i>
                        <?php endif; ?>
                        <?= $action['title'] ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Export Options -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportTable('excel')">
                        <i class="fas fa-file-excel text-success me-2"></i>Excel
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportTable('pdf')">
                        <i class="fas fa-file-pdf text-danger me-2"></i>PDF
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportTable('csv')">
                        <i class="fas fa-file-csv text-info me-2"></i>CSV
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Advanced Filters -->
    <?php if (!empty($filters)): ?>
    <div class="datatable-filters">
        <form method="GET" class="filter-form">
            <div class="row g-3">
                <?php foreach ($filters as $filter): ?>
                    <div class="col-md-<?= $filter['col'] ?? '3' ?>">
                        <label class="form-label"><?= $filter['label'] ?></label>
                        <?php if ($filter['type'] === 'select'): ?>
                            <select name="<?= $filter['name'] ?>" class="form-select form-select-sm">
                                <option value="">All <?= $filter['label'] ?></option>
                                <?php foreach ($filter['options'] as $value => $text): ?>
                                    <option value="<?= $value ?>" <?= ($filter['value'] ?? '') == $value ? 'selected' : '' ?>>
                                        <?= $text ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($filter['type'] === 'date'): ?>
                            <input type="date" name="<?= $filter['name'] ?>" 
                                   class="form-control form-control-sm" 
                                   value="<?= $filter['value'] ?? '' ?>">
                        <?php elseif ($filter['type'] === 'daterange'): ?>
                            <div class="input-group input-group-sm">
                                <input type="date" name="<?= $filter['name'] ?>_start" 
                                       class="form-control" placeholder="Start Date"
                                       value="<?= $filter['start_value'] ?? '' ?>">
                                <span class="input-group-text">to</span>
                                <input type="date" name="<?= $filter['name'] ?>_end" 
                                       class="form-control" placeholder="End Date"
                                       value="<?= $filter['end_value'] ?? '' ?>">
                            </div>
                        <?php else: ?>
                            <input type="<?= $filter['type'] ?? 'text' ?>" 
                                   name="<?= $filter['name'] ?>" 
                                   class="form-control form-control-sm" 
                                   placeholder="<?= $filter['placeholder'] ?? $filter['label'] ?>"
                                   value="<?= $filter['value'] ?? '' ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <div class="col-md-auto">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="<?= strtok($_SERVER['REQUEST_URI'], '?') ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <!-- Bulk Actions -->
    <?php if (!empty($bulk_actions)): ?>
    <div class="datatable-bulk-actions d-none" id="bulkActions">
        <div class="bulk-actions-bar">
            <div class="bulk-selection-info">
                <span id="selectedCount">0</span> items selected
            </div>
            <div class="bulk-action-buttons">
                <?php foreach ($bulk_actions as $action): ?>
                    <button class="btn btn-sm <?= $action['class'] ?? 'btn-secondary' ?> me-2"
                            onclick="performBulkAction('<?= $action['action'] ?>', '<?= $action['title'] ?>')">
                        <?php if (!empty($action['icon'])): ?>
                            <i class="<?= $action['icon'] ?> me-1"></i>
                        <?php endif; ?>
                        <?= $action['title'] ?>
                    </button>
                <?php endforeach; ?>
                
                <button class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                    <i class="fas fa-times me-1"></i>Clear
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table table-hover data-table" id="<?= $table_id ?? 'dataTable' ?>">
            <thead class="table-light">
                <tr>
                    <?php if (!empty($bulk_actions)): ?>
                        <th width="30">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                    <?php endif; ?>
                    
                    <?php foreach ($columns as $column): ?>
                        <th <?= !empty($column['width']) ? 'width="' . $column['width'] . '"' : '' ?>
                            <?= !empty($column['class']) ? 'class="' . $column['class'] . '"' : '' ?>>
                            <?php if (!empty($column['sortable'])): ?>
                                <a href="#" class="sort-link" data-column="<?= $column['key'] ?>">
                                    <?= $column['title'] ?>
                                    <i class="fas fa-sort ms-1"></i>
                                </a>
                            <?php else: ?>
                                <?= $column['title'] ?>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                    
                    <?php if (!empty($row_actions)): ?>
                        <th width="120" class="text-center">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="<?= count($columns) + (empty($bulk_actions) ? 0 : 1) + (empty($row_actions) ? 0 : 1) ?>" 
                            class="text-center py-4">
                            <div class="empty-state">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No data found</h6>
                                <p class="text-muted">There are no records to display.</p>
                                <?php if (!empty($empty_action)): ?>
                                    <a href="<?= $empty_action['url'] ?>" class="btn btn-primary">
                                        <?= $empty_action['title'] ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php if (!empty($bulk_actions)): ?>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input row-select" type="checkbox" 
                                               value="<?= $row['id'] ?>">
                                    </div>
                                </td>
                            <?php endif; ?>
                            
                            <?php foreach ($columns as $column): ?>
                                <td <?= !empty($column['class']) ? 'class="' . $column['class'] . '"' : '' ?>>
                                    <?php
                                    $value = $row[$column['key']] ?? '';
                                    
                                    if (!empty($column['format'])) {
                                        switch ($column['format']) {
                                            case 'date':
                                                $value = $value ? date('M d, Y', strtotime($value)) : '-';
                                                break;
                                            case 'datetime':
                                                $value = $value ? date('M d, Y H:i', strtotime($value)) : '-';
                                                break;
                                            case 'currency':
                                                $value = $value ? '₹' . number_format($value, 2) : '-';
                                                break;
                                            case 'percentage':
                                                $value = $value ? $value . '%' : '-';
                                                break;
                                            case 'badge':
                                                $badgeClass = $column['badge_map'][$value] ?? 'secondary';
                                                $value = '<span class="badge bg-' . $badgeClass . '">' . ucfirst($value) . '</span>';
                                                break;
                                            case 'boolean':
                                                $value = $value ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>';
                                                break;
                                            case 'avatar':
                                                if ($value) {
                                                    $value = '<img src="' . asset('uploads/avatars/' . $value) . '" class="avatar-sm rounded-circle" alt="Avatar">';
                                                } else {
                                                    $initial = strtoupper(substr($row['name'] ?? $row['first_name'] ?? 'U', 0, 1));
                                                    $value = '<div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">' . $initial . '</div>';
                                                }
                                                break;
                                        }
                                    }
                                    
                                    if (!empty($column['link'])) {
                                        $url = str_replace('{id}', $row['id'], $column['link']);
                                        echo '<a href="' . $url . '" class="text-decoration-none">' . $value . '</a>';
                                    } else {
                                        echo $value;
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            
                            <?php if (!empty($row_actions)): ?>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <?php foreach ($row_actions as $action): ?>
                                            <?php
                                            $url = str_replace('{id}', $row['id'], $action['url']);
                                            $show = true;
                                            
                                            if (!empty($action['condition'])) {
                                                $show = eval('return ' . str_replace('{row}', '$row', $action['condition']) . ';');
                                            }
                                            
                                            if ($show):
                                            ?>
                                                <a href="<?= $url ?>" 
                                                   class="btn btn-sm <?= $action['class'] ?? 'btn-outline-primary' ?>"
                                                   <?= !empty($action['confirm']) ? 'data-confirm="' . $action['confirm'] . '"' : '' ?>
                                                   <?= !empty($action['target']) ? 'target="' . $action['target'] . '"' : '' ?>
                                                   title="<?= $action['title'] ?>">
                                                    <?php if (!empty($action['icon'])): ?>
                                                        <i class="<?= $action['icon'] ?>"></i>
                                                    <?php else: ?>
                                                        <?= $action['title'] ?>
                                                    <?php endif; ?>
                                                </a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Table Footer with Pagination -->
    <?php if (!empty($pagination)): ?>
        <div class="datatable-footer">
            <?php $this->component('pagination', ['pagination' => $pagination]) ?>
        </div>
    <?php endif; ?>
</div>

<style>
.datatable-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.datatable-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e5e7eb;
}

.datatable-title h5 {
    color: #1f2937;
    font-weight: 600;
}

.datatable-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.datatable-filters {
    padding: 1.25rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e5e7eb;
}

.datatable-bulk-actions {
    padding: 1rem 1.5rem;
    background: #eff6ff;
    border-bottom: 1px solid #bfdbfe;
}

.bulk-actions-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.bulk-selection-info {
    font-weight: 500;
    color: #1e40af;
}

.bulk-action-buttons {
    display: flex;
    gap: 0.5rem;
}

.table-responsive {
    border-radius: 0;
}

.data-table {
    margin-bottom: 0;
}

.data-table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #374151;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem 0.75rem;
    vertical-align: middle;
}

.data-table td {
    padding: 0.875rem 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
}

.data-table tbody tr {
    transition: background-color 0.2s ease;
}

.data-table tbody tr:hover {
    background-color: #f9fafb;
}

.sort-link {
    color: inherit;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.sort-link:hover {
    color: #3b82f6;
}

.sort-link.asc i::before {
    content: '\f0de'; /* fa-sort-up */
}

.sort-link.desc i::before {
    content: '\f0dd'; /* fa-sort-down */
}

.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 0.8rem;
}

.empty-state {
    padding: 3rem 2rem;
}

.datatable-footer {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #e5e7eb;
}

/* Loading overlay */
.datatable-wrapper.loading {
    position: relative;
}

.datatable-wrapper.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.datatable-wrapper.loading::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 40px;
    height: 40px;
    margin: -20px 0 0 -20px;
    border: 3px solid #e5e7eb;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 11;
}

/* Responsive */
@media (max-width: 768px) {
    .datatable-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .datatable-actions {
        width: 100%;
        justify-content: center;
    }
    
    .bulk-actions-bar {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .data-table {
        font-size: 0.8rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.5rem 0.25rem;
    }
}

/* Print styles */
@media print {
    .datatable-header,
    .datatable-filters,
    .datatable-bulk-actions,
    .datatable-footer {
        display: none;
    }
    
    .datatable-wrapper {
        box-shadow: none;
        border: 1px solid #000;
    }
    
    .data-table {
        font-size: 10px;
    }
}
</style>

<script>
// DataTable initialization and functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    const table = $('#<?= $table_id ?? 'dataTable' ?>').DataTable({
        responsive: true,
        pageLength: <?= $page_length ?? 25 ?>,
        order: <?= json_encode($default_order ?? [[0, 'asc']]) ?>,
        columnDefs: [
            <?php if (!empty($bulk_actions)): ?>
            { targets: 0, orderable: false, searchable: false },
            <?php endif; ?>
            <?php if (!empty($row_actions)): ?>
            { targets: -1, orderable: false, searchable: false },
            <?php endif; ?>
        ],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            },
            emptyTable: "No data available in table"
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        <?php if (!empty($ajax_url)): ?>
        ajax: {
            url: '<?= $ajax_url ?>',
            type: 'POST',
            data: function(d) {
                // Add custom filters
                const filters = {};
                $('.filter-form input, .filter-form select').each(function() {
                    if (this.value) {
                        filters[this.name] = this.value;
                    }
                });
                return $.extend({}, d, filters);
            }
        },
        processing: true,
        serverSide: true,
        <?php endif; ?>
    });
    
    // Handle select all checkbox
    $('#selectAll').on('change', function() {
        const isChecked = this.checked;
        $('.row-select').prop('checked', isChecked);
        updateBulkActions();
    });
    
    // Handle individual row selection
    $(document).on('change', '.row-select', function() {
        updateBulkActions();
        
        // Update select all checkbox
        const totalRows = $('.row-select').length;
        const selectedRows = $('.row-select:checked').length;
        
        $('#selectAll').prop('indeterminate', selectedRows > 0 && selectedRows < totalRows);
        $('#selectAll').prop('checked', selectedRows === totalRows);
    });
    
    // Handle sorting
    $('.sort-link').on('click', function(e) {
        e.preventDefault();
        const column = $(this).data('column');
        const currentOrder = $(this).hasClass('asc') ? 'desc' : 'asc';
        
        // Update URL with sort parameters
        const url = new URL(window.location);
        url.searchParams.set('sort', column);
        url.searchParams.set('order', currentOrder);
        window.location.href = url.toString();
    });
    
    // Handle filter form submission
    $('.filter-form').on('submit', function(e) {
        e.preventDefault();
        
        if (table.ajax) {
            table.ajax.reload();
        } else {
            this.submit();
        }
    });
});

function updateBulkActions() {
    const selectedRows = $('.row-select:checked');
    const bulkActions = $('#bulkActions');
    const selectedCount = $('#selectedCount');
    
    if (selectedRows.length > 0) {
        bulkActions.removeClass('d-none');
        selectedCount.text(selectedRows.length);
    } else {
        bulkActions.addClass('d-none');
    }
}

function clearSelection() {
    $('.row-select, #selectAll').prop('checked', false);
    updateBulkActions();
}

function getSelectedIds() {
    const ids = [];
    $('.row-select:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function performBulkAction(action, actionTitle) {
    const selectedIds = getSelectedIds();
    
    if (selectedIds.length === 0) {
        AlertUtils.warning('Please select at least one item.');
        return;
    }
    
    ModalUtils.confirm(
        `Are you sure you want to ${actionTitle.toLowerCase()} ${selectedIds.length} selected items?`,
        function() {
            ModalUtils.loading(`Performing ${actionTitle.toLowerCase()}...`);
            
            fetch('/bulk-action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: action,
                    ids: selectedIds
                })
            })
            .then(response => response.json())
            .then(data => {
                ModalUtils.hideLoading();
                
                if (data.success) {
                    ModalUtils.success(data.message || 'Bulk action completed successfully!', function() {
                        location.reload();
                    });
                } else {
                    ModalUtils.error(data.message || 'Bulk action failed');
                }
            })
            .catch(error => {
                ModalUtils.hideLoading();
                ModalUtils.error('Network error occurred. Please try again.');
            });
        },
        { danger: action.includes('delete') }
    );
}

function exportTable(format) {
    const url = new URL(window.location);
    url.searchParams.set('export', format);
    
    // Create temporary link and click it
    const link = document.createElement('a');
    link.href = url.toString();
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Advanced search functionality
function showAdvancedSearch() {
    const modal = document.getElementById('advancedSearchModal');
    new bootstrap.Modal(modal).show();
}

function resetFilters() {
    $('.filter-form')[0].reset();
    $('.filter-form').submit();
}

// Column visibility toggle
function toggleColumn(columnIndex) {
    const table = $('#<?= $table_id ?? 'dataTable' ?>').DataTable();
    const column = table.column(columnIndex);
    column.visible(!column.visible());
}

// Refresh table data
function refreshTable() {
    const table = $('#<?= $table_id ?? 'dataTable' ?>').DataTable();
    
    if (table.ajax) {
        table.ajax.reload(null, false); // Keep current page
    } else {
        location.reload();
    }
}

// Auto-refresh functionality
let autoRefreshInterval;

function startAutoRefresh(interval = 30000) {
    autoRefreshInterval = setInterval(refreshTable, interval);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
}
</script>