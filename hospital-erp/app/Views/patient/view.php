<div class="patient-profile">
    <div class="profile-header">
        <h2>Patient Profile: <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
        <a href="/patients" class="btn btn-secondary">Back to Patient List</a>
    </div>

    <div class="profile-section">
        <h4>Demographics</h4>
        <table class="table-profile">
            <tr>
                <th>Patient ID</th>
                <td><?= htmlspecialchars($patient['id']) ?></td>
            </tr>
            <tr>
                <th>Date of Birth</th>
                <td><?= htmlspecialchars($patient['date_of_birth']) ?></td>
            </tr>
            <tr>
                <th>Gender</th>
                <td><?= htmlspecialchars($patient['gender']) ?></td>
            </tr>
            <tr>
                <th>Blood Type</th>
                <td><?= htmlspecialchars($patient['blood_type'] ?? 'N/A') ?></td>
            </tr>
        </table>
    </div>

    <div class="profile-section">
        <h4>Contact Information</h4>
        <table class="table-profile">
            <tr>
                <th>Address</th>
                <td><?= htmlspecialchars($patient['address'] ?? 'N/A') ?>, <?= htmlspecialchars($patient['city'] ?? '') ?>, <?= htmlspecialchars($patient['state'] ?? '') ?> <?= htmlspecialchars($patient['zip_code'] ?? '') ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?= htmlspecialchars($patient['phone_number'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?= htmlspecialchars($patient['email'] ?? 'N/A') ?></td>
            </tr>
        </table>
    </div>

    <div class="profile-section">
        <h4>Emergency Contact</h4>
        <table class="table-profile">
            <tr>
                <th>Name</th>
                <td><?= htmlspecialchars($patient['emergency_contact_name'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?= htmlspecialchars($patient['emergency_contact_phone'] ?? 'N/A') ?></td>
            </tr>
        </table>
    </div>

    <div class="profile-section">
        <h4>Insurance Details</h4>
        <?php if (empty($insuranceDetails)): ?>
            <p>No insurance details on file.</p>
        <?php else: ?>
            <?php foreach($insuranceDetails as $insurance): ?>
                <table class="table-profile mb-3">
                    <tr>
                        <th>Provider</th>
                        <td><?= htmlspecialchars($insurance['provider_name']) ?> (<?= $insurance['is_primary'] ? 'Primary' : 'Secondary' ?>)</td>
                    </tr>
                    <tr>
                        <th>Policy Number</th>
                        <td><?= htmlspecialchars($insurance['policy_number']) ?></td>
                    </tr>
                    <tr>
                        <th>Group Number</th>
                        <td><?= htmlspecialchars($insurance['group_number'] ?? 'N/A') ?></td>
                    </tr>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="profile-section">
        <h4>Medical Records</h4>
        <?php if (empty($medicalRecords)): ?>
            <p>No medical records found.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Doctor</th>
                        <th>Diagnosis</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($medicalRecords as $record): ?>
                    <tr>
                        <td><?= htmlspecialchars($record['record_date']) ?></td>
                        <td><?= htmlspecialchars($record['doctor_name']) ?></td>
                        <td><?= nl2br(htmlspecialchars($record['diagnosis'] ?? '')) ?></td>
                        <td><?= nl2br(htmlspecialchars($record['notes'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
    .profile-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
    .profile-section { margin-top: 2rem; }
    .profile-section h4 { margin-top: 0; margin-bottom: 1rem; }
    .table-profile { width: 100%; }
    .table-profile th, .table-profile td { padding: .5rem; border-bottom: 1px solid #eee; }
    .table-profile th { text-align: left; width: 25%; font-weight: 600; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; }
    .btn-secondary { background-color: #6c757d; color: #fff; }
    .mb-3 { margin-bottom: 1.5rem; }
    /* Re-using table styles from previous view */
    .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    .table th, .table td { border: 1px solid #ddd; padding: .75rem; text-align: left; }
    .table thead th { background-color: #f2f2f2; }
</style>
