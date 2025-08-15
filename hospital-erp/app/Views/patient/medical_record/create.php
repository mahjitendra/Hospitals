<h2>Add Medical Record for <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
<hr>

<?php if (isset($error)): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form action="/patients/<?= $patient['id'] ?>/records/create" method="POST">
    <div class="form-group">
        <label for="record_date">Record Date</label>
        <input type="date" id="record_date" name="record_date" class="form-control" value="<?= htmlspecialchars($old['record_date'] ?? date('Y-m-d')) ?>" required>
    </div>
    <div class="form-group">
        <label for="doctor_name">Doctor's Name</label>
        <input type="text" id="doctor_name" name="doctor_name" class="form-control" value="<?= htmlspecialchars($old['doctor_name'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label for="diagnosis">Diagnosis</label>
        <textarea id="diagnosis" name="diagnosis" class="form-control" rows="3"><?= htmlspecialchars($old['diagnosis'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" class="form-control" rows="5"><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Save Record</button>
        <a href="/patients/view/<?= $patient['id'] ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
    .form-group {
        margin-bottom: 1rem;
    }
    .form-control {
        width: 100%;
        padding: .5rem .75rem;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        box-sizing: border-box;
    }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; cursor: pointer; }
    .btn-primary { background-color: #007bff; color: #fff; border: none; }
    .btn-secondary { background-color: #6c757d; color: #fff; border: none; }
    .mt-3 { margin-top: 1.5rem; }
    .alert-danger { background-color: #f8d7da; color: #721c24; padding: .75rem 1.25rem; border-radius: .25rem; }
</style>
