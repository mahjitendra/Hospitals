<h2>Register New Patient</h2>
<hr>

<?php if (isset($error)): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form action="/patients/register" method="POST" class="patient-form">
    <h4>Patient Demographics</h4>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" class="form-control" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required>
        </div>
        <div class="form-group col-md-6">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" class="form-control" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="date_of_birth">Date of Birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($old['date_of_birth'] ?? '') ?>" required>
        </div>
        <div class="form-group col-md-6">
            <label for="gender">Gender</label>
            <select id="gender" name="gender" class="form-control" required>
                <option value="">Choose...</option>
                <option value="Male" <?= (($old['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= (($old['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= (($old['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
            </select>
        </div>
    </div>

    <h4>Contact Information</h4>
    <div class="form-group">
        <label for="address">Address</label>
        <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($old['address'] ?? '') ?>">
    </div>
    <div class="form-row">
        <div class="form-group col-md-5">
            <label for="city">City</label>
            <input type="text" id="city" name="city" class="form-control" value="<?= htmlspecialchars($old['city'] ?? '') ?>">
        </div>
        <div class="form-group col-md-4">
            <label for="state">State</label>
            <input type="text" id="state" name="state" class="form-control" value="<?= htmlspecialchars($old['state'] ?? '') ?>">
        </div>
        <div class="form-group col-md-3">
            <label for="zip_code">Zip Code</label>
            <input type="text" id="zip_code" name="zip_code" class="form-control" value="<?= htmlspecialchars($old['zip_code'] ?? '') ?>">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="phone_number">Phone Number</label>
            <input type="tel" id="phone_number" name="phone_number" class="form-control" value="<?= htmlspecialchars($old['phone_number'] ?? '') ?>">
        </div>
        <div class="form-group col-md-6">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        </div>
    </div>

    <h4>Emergency & Medical Information</h4>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="emergency_contact_name">Emergency Contact Name</label>
            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" value="<?= htmlspecialchars($old['emergency_contact_name'] ?? '') ?>">
        </div>
        <div class="form-group col-md-6">
            <label for="emergency_contact_phone">Emergency Contact Phone</label>
            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" value="<?= htmlspecialchars($old['emergency_contact_phone'] ?? '') ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="blood_type">Blood Type</label>
        <input type="text" id="blood_type" name="blood_type" class="form-control" value="<?= htmlspecialchars($old['blood_type'] ?? '') ?>">
    </div>

    <button type="submit" class="btn btn-primary mt-3">Register Patient</button>
</form>

<style>
    .patient-form h4 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #eee;
        padding-bottom: .5rem;
    }
    .form-row {
        display: flex;
        margin-left: -5px;
        margin-right: -5px;
    }
    .form-group {
        padding-left: 5px;
        padding-right: 5px;
        margin-bottom: 1rem;
        flex: 1;
    }
    .form-group.col-md-3 { flex: 0 0 25%; }
    .form-group.col-md-4 { flex: 0 0 33.33%; }
    .form-group.col-md-5 { flex: 0 0 41.66%; }
    .form-group.col-md-6 { flex: 0 0 50%; }
    .form-control {
        width: 100%;
        padding: .5rem .75rem;
        border: 1px solid #ced4da;
        border-radius: .25rem;
    }
    .btn.btn-primary {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: .75rem 1.25rem;
        border-radius: .25rem;
        cursor: pointer;
    }
    .mt-3 { margin-top: 1.5rem; }
    .alert-danger { background-color: #f8d7da; color: #721c24; padding: .75rem 1.25rem; border-radius: .25rem; }
</style>
