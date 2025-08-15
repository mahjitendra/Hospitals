<h2>Patient Directory</h2>

<a href="/patients/register" class="btn btn-primary mb-3">Register New Patient</a>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Date of Birth</th>
            <th>Gender</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($patients)): ?>
            <tr>
                <td colspan="6" class="text-center">No patients found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($patients as $patient): ?>
                <tr>
                    <td><?= htmlspecialchars($patient['id']) ?></td>
                    <td><?= htmlspecialchars($patient['first_name']) ?></td>
                    <td><?= htmlspecialchars($patient['last_name']) ?></td>
                    <td><?= htmlspecialchars($patient['date_of_birth']) ?></td>
                    <td><?= htmlspecialchars($patient['gender']) ?></td>
                    <td>
                        <a href="/patients/view/<?= htmlspecialchars($patient['id']) ?>" class="btn btn-sm btn-info">View</a>
                        <!-- Add Edit/Delete buttons later -->
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<style>
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    .table th, .table td {
        border: 1px solid #ddd;
        padding: .75rem;
        text-align: left;
    }
    .table thead th {
        background-color: #f2f2f2;
    }
    .btn { text-decoration: none; display: inline-block; padding: .375rem .75rem; border-radius: .25rem; }
    .btn-primary { background-color: #007bff; color: #fff; }
    .btn-info { background-color: #17a2b8; color: #fff; }
    .btn-sm { font-size: .875rem; padding: .25rem .5rem; }
    .mb-3 { margin-bottom: 1.5rem; }
    .text-center { text-align: center; }
</style>
