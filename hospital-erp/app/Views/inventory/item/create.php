<h2>Add New Inventory Item</h2>
<hr>

<form action="/inventory/items/create" method="POST">
    <div class="form-row">
        <div class="form-group col-md-8">
            <label for="name">Item Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="form-group col-md-4">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" class="form-control" required>
        </div>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="quantity">Initial Quantity</label>
            <input type="number" id="quantity" name="quantity" class="form-control" value="0" required>
        </div>
        <div class="form-group col-md-4">
            <label for="reorder_level">Reorder Level</label>
            <input type="number" id="reorder_level" name="reorder_level" class="form-control" value="0" required>
        </div>
        <div class="form-group col-md-4">
            <label for="unit_price">Unit Price</label>
            <input type="number" step="0.01" id="unit_price" name="unit_price" class="form-control" value="0.00" required>
        </div>
    </div>
    <div class="form-group">
        <label for="location">Location</label>
        <input type="text" id="location" name="location" class="form-control">
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Save Item</button>
        <a href="/inventory/items" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
    /* Using styles from previous views */
    .form-row { display: flex; gap: 20px; }
    .form-group { flex: 1; margin-bottom: 1rem; }
    .form-group.col-md-4 { flex: 0 0 calc(33.333% - 14px); }
    .form-group.col-md-8 { flex: 0 0 calc(66.666% - 7px); }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; box-sizing: border-box; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; cursor: pointer; }
    .btn-primary { background-color: #007bff; color: #fff; border: none; }
    .btn-secondary { background-color: #6c757d; color: #fff; border: none; }
    .mt-3 { margin-top: 1.5rem; }
</style>
