<h2>Discharge Patient: <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
<hr>

<p>You are about to discharge this patient. This action will update their status and should only be done after all clinical and financial procedures are complete.</p>

<div class="patient-summary">
    <strong>Patient:</strong> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?><br>
    <strong>Date of Birth:</strong> <?= htmlspecialchars($patient['date_of_birth']) ?><br>
    <strong>Current Status:</strong> <span class="status-active"><?= htmlspecialchars($patient['status']) ?></span>
</div>

<form action="/patients/<?= $patient['id'] ?>/discharge" method="POST" class="mt-3">
    <div class="form-group">
        <label for="discharge_notes"><strong>Final Discharge Notes (Optional)</strong></label>
        <textarea id="discharge_notes" name="discharge_notes" class="form-control" rows="4"></textarea>
        <small>Note: Saving discharge notes is not yet implemented but is planned for a future update.</small>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-danger">Confirm Discharge</button>
        <a href="/patients/view/<?= $patient['id'] ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
    .patient-summary {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 1rem;
        border-radius: .25rem;
    }
    .status-active {
        color: #28a745;
        font-weight: bold;
    }
    .form-group { margin-bottom: 1rem; }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; box-sizing: border-box; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; cursor: pointer; }
    .btn-danger { background-color: #dc3545; color: #fff; border: none; }
    .btn-secondary { background-color: #6c757d; color: #fff; border: none; }
    .mt-3 { margin-top: 1.5rem; }
</style>
