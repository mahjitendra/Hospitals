<h2>Financial Summary Report</h2>
<hr>

<div class="report-container">
    <h4>Invoice Summary by Status</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Status</th>
                <th>Invoice Count</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><span class="status-<?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td><?= htmlspecialchars($row['count']) ?></td>
                    <td>$<?= htmlspecialchars(number_format($row['total'], 2)) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
    /* Using styles from previous views */
    .report-container { background-color: #fff; padding: 1.5rem; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    .table th, .table td { border: 1px solid #ddd; padding: .75rem; text-align: left; }
    .table thead th { background-color: #f2f2f2; }
    .status-draft { color: #6c757d; }
    .status-sent { color: #007bff; }
    .status-paid { color: #28a745; font-weight: bold; }
</style>
