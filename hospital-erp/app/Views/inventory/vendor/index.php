<h2>Vendors</h2>
<a href="/inventory/vendors/create" class="btn btn-primary mb-3">Add New Vendor</a>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Contact Person</th>
            <th>Phone</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($vendors)): ?>
            <tr>
                <td colspan="5" class="text-center">No vendors found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($vendors as $vendor): ?>
                <tr>
                    <td><?= htmlspecialchars($vendor['id']) ?></td>
                    <td><?= htmlspecialchars($vendor['name']) ?></td>
                    <td><?= htmlspecialchars($vendor['contact_person'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($vendor['phone_number'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($vendor['email'] ?? 'N/A') ?></td>
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
    .mb-3 { margin-bottom: 1.5rem; }
    .text-center { text-align: center; }
</style>
