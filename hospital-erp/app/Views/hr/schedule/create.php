<h2>Add Schedule for <?= htmlspecialchars($staffMember['first_name'] . ' ' . $staffMember['last_name']) ?></h2>
<hr>

<form action="/hr/staff/<?= $staffMember['id'] ?>/schedules/create" method="POST">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="shift_start">Shift Start</label>
            <input type="datetime-local" id="shift_start" name="shift_start" class="form-control" required>
        </div>
        <div class="form-group col-md-6">
            <label for="shift_end">Shift End</label>
            <input type="datetime-local" id="shift_end" name="shift_end" class="form-control" required>
        </div>
    </div>
    <div class="form-group">
        <label for="notes">Notes (Optional)</label>
        <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Save Schedule</button>
        <a href="/hr/staff/view/<?= $staffMember['id'] ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
    /* Using styles from previous views */
    .form-row { display: flex; gap: 20px; }
    .form-group { flex: 1; margin-bottom: 1rem; }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; box-sizing: border-box; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; cursor: pointer; }
    .btn-primary { background-color: #007bff; color: #fff; border: none; }
    .btn-secondary { background-color: #6c757d; color: #fff; border: none; }
    .mt-3 { margin-top: 1.5rem; }
</style>
