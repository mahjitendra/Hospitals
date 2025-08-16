<?php

namespace App\Controllers\Financial;

use App\Controllers\BaseController;
use App\Models\Financial\Payment as PaymentModel;
use App\Models\Financial\Invoice as InvoiceModel;
use App\Libraries\Core\Session;

/**
 * Class PaymentController
 *
 * Handles the recording of payments.
 */
class PaymentController extends BaseController
{
    private PaymentModel $paymentModel;
    private InvoiceModel $invoiceModel;
    private Session $session;

    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
        $this->invoiceModel = new InvoiceModel();
        $this->session = new Session();
    }

    /**
     * Shows the form to add a payment for a specific invoice.
     * @param int $invoice_id
     */
    public function showPaymentForm(int $invoice_id)
    {
        $invoice = $this->invoiceModel->findById($invoice_id);
        if (!$invoice) { echo "Invoice not found"; exit; }

        $this->render('financial.payment.create', [
            'title' => 'Record Payment for Invoice #' . $invoice_id,
            'invoice' => $invoice
        ]);
    }

    /**
     * Processes the creation of a new payment.
     * @param int $invoice_id
     */
    public function create(int $invoice_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /invoices/view/{$invoice_id}");
            exit;
        }

        $invoice = $this->invoiceModel->findById($invoice_id);
        if (!$invoice) { echo "Invoice not found"; exit; }

        $data = [
            'invoice_id' => $invoice_id,
            'patient_id' => $invoice['patient_id'],
            'payment_date' => date('Y-m-d H:i:s'),
            'amount' => $_POST['amount'] ?? 0,
            'payment_method' => $_POST['payment_method'] ?? 'Other',
            'transaction_id' => $_POST['transaction_id'] ?? null
        ];

        if (empty($data['amount']) || $data['amount'] <= 0) {
            $this->session->flash('error', 'Payment amount must be greater than zero.');
            header("Location: /invoices/{$invoice_id}/pay");
            exit;
        }

        if ($this->paymentModel->create($data)) {
            // Here you would also update the invoice status (e.g., to 'Paid' or 'Partial')
            // This logic can be added to the Invoice model later.
            $this->session->flash('success', 'Payment recorded successfully.');
            header("Location: /invoices/view/{$invoice_id}");
            exit;
        } else {
            $this->session->flash('error', 'Failed to record payment.');
            header("Location: /invoices/{$invoice_id}/pay");
            exit;
        }
    }
}
