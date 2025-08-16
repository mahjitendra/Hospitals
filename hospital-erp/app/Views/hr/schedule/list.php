<h2>Schedules for <?= htmlspecialchars($staffMember['first_name'] . ' ' . $staffMember['last_name']) ?></h2>
<a href="/hr/staff/<?= $staffMember['id'] ?>/schedules/create" class="btn btn-primary mb-3">Add New Schedule</a>

<table class="table">
    <thead>
        <tr>
            <th>Shift Start</th>
            <th>Shift End</th>
            <th>Notes</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($schedules)): ?>
            <tr>
                <td colspan="4" class="text-center">No schedules found for this staff member.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($schedules as $schedule): ?>
                <tr>
                    <td><?= htmlspecialchars($schedule['shift_start']) ?></td>
                    <td><?= htmlspecialchars($schedule['shift_end']) ?></td>
                    <td><?= htmlspecialchars($schedule['notes'] ?? 'N/A') ?></td>
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
    .btn { text-decoration: none; display: inline-block; padding: .375rem .75rem; border-radius: .25rem; }
    .btn-primary { background-color: #007bff; color: #fff; }
    .mb-3 { margin-bottom: 1.5rem; }
    .text-center { text-align: center; }
</style>
