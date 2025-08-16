<h2>Role Management</h2>
<hr>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($roles)): ?>
            <tr>
                <td colspan="4" class="text-center">No roles found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?= htmlspecialchars($role['id']) ?></td>
                    <td><?= htmlspecialchars($role['name']) ?></td>
                    <td><?= htmlspecialchars($role['description']) ?></td>
                    <td>
                        <!-- Add Edit/Delete buttons later -->
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
    .text-center { text-align: center; }
</style>
