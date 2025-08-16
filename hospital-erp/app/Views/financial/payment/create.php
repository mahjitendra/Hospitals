<h2>Record Payment for Invoice #<?= htmlspecialchars($invoice['id']) ?></h2>
<hr>

<div class="invoice-summary">
    <strong>Patient:</strong> <?= htmlspecialchars($invoice['patient_name'] ?? 'N/A') // Assuming patient name is passed in ?> <br>
    <strong>Total Amount:</strong> $<?= htmlspecialchars(number_format($invoice['total_amount'], 2)) ?><br>
    <strong>Status:</strong> <?= htmlspecialchars($invoice['status']) ?>
</div>

<form action="/invoices/<?= $invoice['id'] ?>/pay" method="POST" class="mt-3">
    <div class="form-group">
        <label for="amount">Payment Amount</label>
        <input type="number" step="0.01" id="amount" name="amount" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="payment_method">Payment Method</label>
        <select id="payment_method" name="payment_method" class="form-control" required>
            <option value="Cash">Cash</option>
            <option value="Credit Card">Credit Card</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="Insurance">Insurance</option>
            <option value="Other">Other</option>
        </select>
    </div>
    <div class="form-group">
        <label for="transaction_id">Transaction ID (Optional)</label>
        <input type="text" id="transaction_id" name="transaction_id" class="form-control">
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-success">Record Payment</button>
        <a href="/invoices/view/<?= $invoice['id'] ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
    .invoice-summary { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 1rem; border-radius: .25rem; }
    .form-group { margin-bottom: 1rem; }
    .form-control { width: 100%; padding: .5rem .75rem; border: 1px solid #ced4da; border-radius: .25rem; box-sizing: border-box; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; cursor: pointer; }
    .btn-success { background-color: #28a745; color: #fff; border: none; }
    .btn-secondary { background-color: #6c757d; color: #fff; border: none; }
    .mt-3 { margin-top: 1.5rem; }
</style>
