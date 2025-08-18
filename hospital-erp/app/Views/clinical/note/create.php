<h2>Add Clinical Note for <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
<hr>

<form action="/patients/<?= $patient['id'] ?>/notes/create" method="POST">
    <div class="form-group">
        <label for="note_type">Note Type</label>
        <input type="text" id="note_type" name="note_type" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="note">Note</label>
        <textarea id="note" name="note" class="form-control" rows="10" required></textarea>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Save Note</button>
        <a href="/patients/view/<?= $patient['id'] ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
    /* Using styles from previous views */
    .form-group { margin-bottom: 1rem; }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; box-sizing: border-box; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; cursor: pointer; }
    .btn-primary { background-color: #007bff; color: #fff; border: none; }
    .btn-secondary { background-color: #6c757d; color: #fff; border: none; }
    .mt-3 { margin-top: 1.5rem; }
</style>
