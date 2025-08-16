<h2>Create Invoice for <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h2>
<hr>

<form action="/patients/<?= $patient['id'] ?>/invoices/create" method="POST">
    <h4>Invoice Items</h4>
    <div id="invoice-items">
        <div class="item">
            <input type="text" name="items[0][description]" placeholder="Item Description" required>
            <input type="number" name="items[0][quantity]" placeholder="Qty" value="1" required>
            <input type="number" step="0.01" name="items[0][unit_price]" placeholder="Unit Price" required>
        </div>
    </div>
    <button type="button" id="add-item" class="btn btn-sm btn-secondary">Add Another Item</button>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Create Invoice</button>
        <a href="/patients/<?= $patient['id'] ?>/invoices" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
    document.getElementById('add-item').addEventListener('click', function() {
        const itemsContainer = document.getElementById('invoice-items');
        const index = itemsContainer.getElementsByClassName('item').length;
        const newItem = document.createElement('div');
        newItem.classList.add('item');
        newItem.innerHTML = `
            <input type="text" name="items[${index}][description]" placeholder="Item Description" required>
            <input type="number" name="items[${index}][quantity]" placeholder="Qty" value="1" required>
            <input type="number" step="0.01" name="items[${index}][unit_price]" placeholder="Unit Price" required>
            <button type="button" class="remove-item">Remove</button>
        `;
        itemsContainer.appendChild(newItem);
    });

    document.getElementById('invoice-items').addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-item')) {
            e.target.parentNode.remove();
        }
    });
</script>

<style>
    .item { display: flex; gap: 10px; margin-bottom: 10px; }
    .item input { padding: .5rem; border: 1px solid #ccc; border-radius: 4px; }
    .item input[type="text"] { flex: 3; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; cursor: pointer; }
    .btn-primary { background-color: #007bff; color: #fff; border: none; }
    .btn-secondary { background-color: #6c757d; color: #fff; border: none; }
    .btn-sm { padding: .25rem .5rem; }
    .mt-3 { margin-top: 1.5rem; }
</style>
