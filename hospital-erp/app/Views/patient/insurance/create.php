<h2>Add Insurance for <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
<hr>

<?php if (isset($error)): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form action="/patients/<?= $patient['id'] ?>/insurance/create" method="POST">
    <div class="form-group">
        <label for="provider_name">Provider Name</label>
        <input type="text" id="provider_name" name="provider_name" class="form-control" value="<?= htmlspecialchars($old['provider_name'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label for="policy_number">Policy Number</label>
        <input type="text" id="policy_number" name="policy_number" class="form-control" value="<?= htmlspecialchars($old['policy_number'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label for="group_number">Group Number</label>
        <input type="text" id="group_number" name="group_number" class="form-control" value="<?= htmlspecialchars($old['group_number'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="subscriber_name">Subscriber Name</label>
        <input type="text" id="subscriber_name" name="subscriber_name" class="form-control" value="<?= htmlspecialchars($old['subscriber_name'] ?? '') ?>" required>
    </div>
    <div class="form-check">
        <input type="checkbox" id="is_primary" name="is_primary" class="form-check-input" value="1" checked>
        <label for="is_primary" class="form-check-label">Set as Primary Insurance</label>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Save Insurance Details</button>
        <a href="/patients/view/<?= $patient['id'] ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
    .form-group { margin-bottom: 1rem; }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; box-sizing: border-box; }
    .form-check { margin-top: 1rem; }
    .form-check-input { margin-right: .5rem; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; cursor: pointer; }
    .btn-primary { background-color: #007bff; color: #fff; border: none; }
    .btn-secondary { background-color: #6c757d; color: #fff; border: none; }
    .mt-3 { margin-top: 1.5rem; }
    .alert-danger { background-color: #f8d7da; color: #721c24; padding: .75rem 1.25rem; border-radius: .25rem; }
</style>
