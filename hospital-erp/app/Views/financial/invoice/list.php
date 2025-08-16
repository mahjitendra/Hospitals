<h2>Invoices for <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
<a href="/patients/<?= $patient['id'] ?>/invoices/create" class="btn btn-primary mb-3">Create New Invoice</a>

<table class="table">
    <thead>
        <tr>
            <th>Invoice ID</th>
            <th>Invoice Date</th>
            <th>Due Date</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($invoices)): ?>
            <tr>
                <td colspan="6" class="text-center">No invoices found for this patient.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td>#<?= htmlspecialchars($invoice['id']) ?></td>
                    <td><?= htmlspecialchars($invoice['invoice_date']) ?></td>
                    <td><?= htmlspecialchars($invoice['due_date']) ?></td>
                    <td>$<?= htmlspecialchars(number_format($invoice['total_amount'], 2)) ?></td>
                    <td><span class="status-<?= strtolower($invoice['status']) ?>"><?= htmlspecialchars($invoice['status']) ?></span></td>
                    <td>
                        <a href="/invoices/view/<?= $invoice['id'] ?>" class="btn btn-sm btn-info">View</a>
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
    .status-draft { color: #6c757d; }
    .status-sent { color: #007bff; }
    .status-paid { color: #28a745; font-weight: bold; }
</style>
