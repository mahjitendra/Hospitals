<h2>Quality Incidents</h2>
<a href="/quality/incidents/create" class="btn btn-danger mb-3">Report New Incident</a>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Incident Date</th>
            <th>Department</th>
            <th>Severity</th>
            <th>Status</th>
            <th>Reported By</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($incidents)): ?>
            <tr>
                <td colspan="7" class="text-center">No incidents found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($incidents as $incident): ?>
                <tr>
                    <td><?= htmlspecialchars($incident['id']) ?></td>
                    <td><?= htmlspecialchars($incident['incident_date']) ?></td>
                    <td><?= htmlspecialchars($incident['department']) ?></td>
                    <td><span class="severity-<?= strtolower($incident['severity']) ?>"><?= htmlspecialchars($incident['severity']) ?></span></td>
                    <td><?= htmlspecialchars($incident['status']) ?></td>
                    <td><?= htmlspecialchars($incident['first_name'] . ' ' . $incident['last_name']) ?></td>
                    <td><?= htmlspecialchars($incident['description']) ?></td>
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
    .btn-danger { background-color: #dc3545; color: #fff; }
    .mb-3 { margin-bottom: 1.5rem; }
    .text-center { text-align: center; }
    .severity-critical { color: #dc3545; font-weight: bold; }
    .severity-high { color: #fd7e14; }
    .severity-medium { color: #ffc107; }
    .severity-low { color: #17a2b8; }
</style>
