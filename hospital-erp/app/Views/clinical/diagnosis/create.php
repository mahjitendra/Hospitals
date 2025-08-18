<h2>Add Diagnosis for <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
<hr>

<form action="/patients/<?= $patient['id'] ?>/diagnoses/create" method="POST">
    <div class="form-group">
        <label for="icd10_code">ICD-10 Code</label>
        <input type="text" id="icd10_code" name="icd10_code" class="form-control">
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
    </div>
    <div class="form-check">
        <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" checked>
        <label for="is_active" class="form-check-label">Is Active</label>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Save Diagnosis</button>
        <a href="/patients/view/<?= $patient['id'] ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
    /* Using styles from previous views */
    .form-group { margin-bottom: 1rem; }
    .form-check { margin-bottom: 1rem; }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; box-sizing: border-box; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; cursor: pointer; }
    .btn-primary { background-color: #007bff; color: #fff; border: none; }
    .btn-secondary { background-color: #6c757d; color: #fff; border: none; }
    .mt-3 { margin-top: 1.5rem; }
</style>
