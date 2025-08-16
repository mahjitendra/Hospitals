<h2>Inventory Items</h2>
<a href="/inventory/items/create" class="btn btn-primary mb-3">Add New Item</a>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Reorder Level</th>
            <th>Unit Price</th>
            <th>Location</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($items)): ?>
            <tr>
                <td colspan="7" class="text-center">No inventory items found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['id']) ?></td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                    <td><?= htmlspecialchars($item['reorder_level']) ?></td>
                    <td>$<?= htmlspecialchars(number_format($item['unit_price'], 2)) ?></td>
                    <td><?= htmlspecialchars($item['location']) ?></td>
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
