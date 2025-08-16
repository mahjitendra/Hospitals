<div class="profile-header">
    <h2>Staff Profile: <?= htmlspecialchars($staffMember['first_name'] . ' ' . $staffMember['last_name']) ?></h2>
    <div>
        <a href="/hr/staff/<?= $staffMember['id'] ?>/schedules" class="btn btn-info">View Schedules</a>
        <a href="/hr/staff" class="btn btn-secondary">Back to Staff List</a>
    </div>
</div>

<div class="profile-section">
    <h4>Employee Details</h4>
    <table class="table-profile">
        <tr>
            <th>Staff ID</th>
            <td><?= htmlspecialchars($staffMember['id']) ?></td>
        </tr>
        <tr>
            <th>Job Title</th>
            <td><?= htmlspecialchars($staffMember['job_title']) ?></td>
        </tr>
        <tr>
            <th>Department</th>
            <td><?= htmlspecialchars($staffMember['department']) ?></td>
        </tr>
        <tr>
            <th>Hire Date</th>
            <td><?= htmlspecialchars($staffMember['hire_date']) ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><span class="status-<?= strtolower($staffMember['status']) ?>"><?= htmlspecialchars($staffMember['status']) ?></span></td>
        </tr>
    </table>
</div>

<div class="profile-section">
    <h4>Contact Information</h4>
    <table class="table-profile">
        <tr>
            <th>Email</th>
            <td><?= htmlspecialchars($staffMember['email']) ?></td>
        </tr>
        <tr>
            <th>Phone</th>
            <td><?= htmlspecialchars($staffMember['phone_number'] ?? 'N/A') ?></td>
        </tr>
    </table>
</div>

<style>
    .profile-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
    .profile-section { margin-top: 2rem; }
    .profile-section h4 { margin-top: 0; margin-bottom: 1rem; }
    .table-profile { width: 100%; }
    .table-profile th, .table-profile td { padding: .5rem; border-bottom: 1px solid #eee; }
    .table-profile th { text-align: left; width: 25%; font-weight: 600; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; }
    .btn-info { background-color: #17a2b8; color: #fff; }
    .btn-secondary { background-color: #6c757d; color: #fff; }
    .status-active { color: #28a745; font-weight: bold; }
    .status-on-leave { color: #ffc107; }
    .status-terminated { color: #dc3545; }
</style>
