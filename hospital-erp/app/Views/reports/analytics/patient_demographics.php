<h2>Patient Demographics Report</h2>
<hr>

<div class="report-container">
    <h4>Patient Count by Gender</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Gender</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['gender']) ?></td>
                    <td><?= htmlspecialchars($row['count']) ?></td>
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
</style>
