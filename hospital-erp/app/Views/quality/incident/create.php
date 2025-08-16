<h2>Report New Quality Incident</h2>
<hr>

<form action="/quality/incidents/create" method="POST">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="incident_date">Date and Time of Incident</label>
            <input type="datetime-local" id="incident_date" name="incident_date" class="form-control" required>
        </div>
        <div class="form-group col-md-6">
            <label for="department">Department</label>
            <input type="text" id="department" name="department" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label for="description">Description of Incident</label>
        <textarea id="description" name="description" class="form-control" rows="5" required></textarea>
    </div>
    <div class="form-group">
        <label for="severity">Severity</label>
        <select id="severity" name="severity" class="form-control" required>
            <option value="">Choose...</option>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
            <option value="Critical">Critical</option>
        </select>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-danger">Submit Incident Report</button>
        <a href="/quality/incidents" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
    /* Using styles from previous views */
    .form-row { display: flex; gap: 20px; }
    .form-group { flex: 1; margin-bottom: 1rem; }
    .form-group.col-md-6 { flex: 0 0 calc(50% - 10px); }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; box-sizing: border-box; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; cursor: pointer; }
    .btn-danger { background-color: #dc3545; color: #fff; border: none; }
    .btn-secondary { background-color: #6c757d; color: #fff; border: none; }
    .mt-3 { margin-top: 1.5rem; }
</style>
