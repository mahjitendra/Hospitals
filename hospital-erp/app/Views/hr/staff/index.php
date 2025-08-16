<h2>Staff Directory</h2>
<a href="/hr/staff/create" class="btn btn-primary mb-3">Add New Staff Member</a>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Job Title</th>
            <th>Department</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($staff)): ?>
            <tr>
                <td colspan="6" class="text-center">No staff members found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($staff as $staffMember): ?>
                <tr>
                    <td><?= htmlspecialchars($staffMember['id']) ?></td>
                    <td><?= htmlspecialchars($staffMember['first_name'] . ' ' . $staffMember['last_name']) ?></td>
                    <td><?= htmlspecialchars($staffMember['job_title']) ?></td>
                    <td><?= htmlspecialchars($staffMember['department']) ?></td>
                    <td><span class="status-<?= strtolower($staffMember['status']) ?>"><?= htmlspecialchars($staffMember['status']) ?></span></td>
                    <td>
                        <a href="/hr/staff/view/<?= $staffMember['id'] ?>" class="btn btn-sm btn-info">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<style>
    /* Using styles from previous views */
    .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    .table th, .table td { border: 1px solid #ddd; padding: .75rem; text-align: left; }
    .table thead th { background-color: #f2f2f2; }
    .btn { text-decoration: none; display: inline-block; padding: .375rem .75rem; border-radius: .25rem; }
    .btn-primary { background-color: #007bff; color: #fff; }
    .btn-info { background-color: #17a2b8; color: #fff; }
    .btn-sm { font-size: .875rem; padding: .25rem .5rem; }
    .mb-3 { margin-bottom: 1.5rem; }
    .text-center { text-align: center; }
    .status-active { color: #28a745; font-weight: bold; }
    .status-on-leave { color: #ffc107; }
    .status-terminated { color: #dc3545; }
</style>
