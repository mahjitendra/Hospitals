<h2>Add New Vendor</h2>
<hr>

<form action="/inventory/vendors/create" method="POST">
    <div class="form-group">
        <label for="name">Vendor Name</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="contact_person">Contact Person</label>
        <input type="text" id="contact_person" name="contact_person" class="form-control">
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="phone_number">Phone Number</label>
            <input type="tel" id="phone_number" name="phone_number" class="form-control">
        </div>
        <div class="form-group col-md-6">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label for="address">Address</label>
        <textarea id="address" name="address" class="form-control" rows="3"></textarea>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Save Vendor</button>
        <a href="/inventory/vendors" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
    /* Using styles from previous views */
    .form-row { display: flex; gap: 20px; }
    .form-group { flex: 1; margin-bottom: 1rem; }
    .form-group.col-md-6 { flex: 0 0 calc(50% - 10px); }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; box-sizing: border-box; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; cursor: pointer; }
    .btn-primary { background-color: #007bff; color: #fff; border: none; }
    .btn-secondary { background-color: #6c757d; color: #fff; border: none; }
    .mt-3 { margin-top: 1.5rem; }
</style>
