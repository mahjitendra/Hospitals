<div class="invoice-box">
    <div class="invoice-header">
        <h2>Invoice #<?= htmlspecialchars($invoice['id']) ?></h2>
        <div class="invoice-actions">
            <a href="/invoices/<?= $invoice['id'] ?>/pay" class="btn btn-success">Record Payment</a>
            <a href="/patients/<?= $patient['id'] ?>/invoices" class="btn btn-secondary">Back to Invoice List</a>
        </div>
    </div>

    <div class="invoice-details">
        <div>
            <strong>Billed To:</strong><br>
            <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?><br>
            <?= htmlspecialchars($patient['address'] ?? '') ?><br>
            <?= htmlspecialchars($patient['city'] ?? '') ?>, <?= htmlspecialchars($patient['state'] ?? '') ?> <?= htmlspecialchars($patient['zip_code'] ?? '') ?>
        </div>
        <div>
            <strong>Invoice Date:</strong> <?= htmlspecialchars($invoice['invoice_date']) ?><br>
            <strong>Due Date:</strong> <?= htmlspecialchars($invoice['due_date']) ?><br>
            <strong>Status:</strong> <span class="status-<?= strtolower($invoice['status']) ?>"><?= htmlspecialchars($invoice['status']) ?></span>
        </div>
    </div>

    <h4>Invoice Items</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                    <td>$<?= htmlspecialchars(number_format($item['unit_price'], 2)) ?></td>
                    <td>$<?= htmlspecialchars(number_format($item['total_price'], 2)) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>Total Amount</strong></td>
                <td><strong>$<?= htmlspecialchars(number_format($invoice['total_amount'], 2)) ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <h4>Payments Received</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Payment Date</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Transaction ID</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalPaid = 0;
            if (empty($payments)): ?>
                <tr><td colspan="4" class="text-center">No payments recorded.</td></tr>
            <?php else:
                foreach ($payments as $payment):
                    $totalPaid += $payment['amount'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                    <td>$<?= htmlspecialchars(number_format($payment['amount'], 2)) ?></td>
                    <td><?= htmlspecialchars($payment['payment_method']) ?></td>
                    <td><?= htmlspecialchars($payment['transaction_id'] ?? 'N/A') ?></td>
                </tr>
            <?php
                endforeach;
            endif;
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>Total Paid</strong></td>
                <td><strong>$<?= htmlspecialchars(number_format($totalPaid, 2)) ?></strong></td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>Balance Due</strong></td>
                <td><strong>$<?= htmlspecialchars(number_format($invoice['total_amount'] - $totalPaid, 2)) ?></strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<style>
    .invoice-box { background: #fff; padding: 2rem; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .invoice-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 2rem; }
    .invoice-details { display: flex; justify-content: space-between; margin-bottom: 2rem; }
    .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    .table th, .table td { border-bottom: 1px solid #ddd; padding: .75rem; text-align: left; }
    .table thead th { background-color: #f2f2f2; }
    .table tfoot td { border-top: 2px solid #333; font-weight: bold; }
    .text-right { text-align: right; }
    .btn { text-decoration: none; display: inline-block; padding: .5rem 1rem; border-radius: .25rem; }
    .btn-success { background-color: #28a745; color: #fff; }
    .btn-secondary { background-color: #6c757d; color: #fff; }
    .status-draft { color: #6c757d; }
    .status-sent { color: #007bff; }
    .status-paid { color: #28a745; font-weight: bold; }
</style>
