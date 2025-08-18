<h2>Data Mappings</h2>
<hr>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Source System</th>
            <th>Source Field</th>
            <th>Destination System</th>
            <th>Destination Field</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($mappings)): ?>
            <tr>
                <td colspan="5" class="text-center">No data mappings found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($mappings as $mapping): ?>
                <tr>
                    <td><?= htmlspecialchars($mapping['id']) ?></td>
                    <td><?= htmlspecialchars($mapping['source_system']) ?></td>
                    <td><?= htmlspecialchars($mapping['source_field']) ?></td>
                    <td><?= htmlspecialchars($mapping['destination_system']) ?></td>
                    <td><?= htmlspecialchars($mapping['destination_field']) ?></td>
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
