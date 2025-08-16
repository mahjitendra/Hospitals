<h2>Add New Staff Member</h2>
<hr>

<form action="/hr/staff/create" method="POST">
    <h4>Personal & Contact Details</h4>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" class="form-control" required>
        </div>
        <div class="form-group col-md-6">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" class="form-control" required>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="form-group col-md-6">
            <label for="phone_number">Phone Number</label>
            <input type="tel" id="phone_number" name="phone_number" class="form-control">
        </div>
    </div>

    <h4>Job Information</h4>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="job_title">Job Title</label>
            <input type="text" id="job_title" name="job_title" class="form-control" required>
        </div>
        <div class="form-group col-md-6">
            <label for="department">Department</label>
            <input type="text" id="department" name="department" class="form-control" required>
        </div>
    </div>
    <div class="form-group">
        <label for="hire_date">Hire Date</label>
        <input type="date" id="hire_date" name="hire_date" class="form-control" required>
    </div>

    <h4>Login Credentials</h4>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control" required>
        <small>A user account will be created for the staff member to log in.</small>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Save Staff Member</button>
        <a href="/hr/staff" class="btn btn-secondary">Cancel</a>
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
