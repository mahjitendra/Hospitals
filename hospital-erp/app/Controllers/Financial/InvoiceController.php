<?php

namespace App\Controllers\Financial;

use App\Controllers\BaseController;
use App\Models\Financial\Invoice as InvoiceModel;
use App\Models\Financial\InvoiceItem as InvoiceItemModel;
use App\Models\Financial\Payment as PaymentModel;
use App\Models\Patient\Patient as PatientModel;
use App\Libraries\Core\Session;

/**
 * Class InvoiceController
 *
 * Handles the creation and management of patient invoices.
 */
class InvoiceController extends BaseController
{
    private InvoiceModel $invoiceModel;
    private InvoiceItemModel $itemModel;
    private PaymentModel $paymentModel;
    private PatientModel $patientModel;
    private Session $session;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
        $this->itemModel = new InvoiceItemModel();
        $this->paymentModel = new PaymentModel();
        $this->patientModel = new PatientModel();
        $this->session = new Session();
    }

    /**
     * Lists all invoices for a specific patient.
     * @param int $patient_id
     */
    public function listByPatient(int $patient_id)
    {
        $patient = $this->patientModel->findById($patient_id);
        if (!$patient) { echo "Patient not found"; exit; }

        $invoices = $this->invoiceModel->findByPatientId($patient_id);

        $this->render('financial.invoice.list', [
            'title' => 'Invoices for ' . htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']),
            'patient' => $patient,
            'invoices' => $invoices
        ]);
    }

    /**
     * Displays a single invoice with its details.
     * @param int $invoice_id
     */
    public function view(int $invoice_id)
    {
        $invoice = $this->invoiceModel->findById($invoice_id);
        if (!$invoice) { echo "Invoice not found"; exit; }

        $items = $this->itemModel->findByInvoiceId($invoice_id);
        $payments = $this->paymentModel->findByInvoiceId($invoice_id);
        $patient = $this->patientModel->findById($invoice['patient_id']);

        $this->render('financial.invoice.view', [
            'title' => 'Invoice #' . $invoice['id'],
            'invoice' => $invoice,
            'items' => $items,
            'payments' => $payments,
            'patient' => $patient
        ]);
    }

    /**
     * Shows the form to create a new invoice.
     * @param int $patient_id
     */
    public function showCreateForm(int $patient_id)
    {
        $patient = $this->patientModel->findById($patient_id);
        if (!$patient) { echo "Patient not found"; exit; }

        $this->render('financial.invoice.create', [
            'title' => 'Create Invoice for ' . htmlspecialchars($patient['first_name']),
            'patient' => $patient
        ]);
    }

    /**
     * Processes the creation of a new invoice.
     * @param int $patient_id
     */
    public function create(int $patient_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /patients/view/{$patient_id}");
            exit;
        }

        // A real implementation would be more complex, handling dynamic item additions via JS.
        // This is a simplified version for demonstration.
        $total = 0;
        $items = [];
        if (isset($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                $item_total = (int)$item['quantity'] * (float)$item['unit_price'];
                $total += $item_total;
                $items[] = [
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item_total,
                ];
            }
        }

        $invoiceData = [
            'patient_id' => $patient_id,
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'total_amount' => $total,
            'status' => 'Draft'
        ];

        $invoiceId = $this->invoiceModel->create($invoiceData);

        if ($invoiceId) {
            foreach ($items as $item) {
                $item['invoice_id'] = $invoiceId;
                $this->itemModel->create($item);
            }
            $this->session->flash('success', 'Invoice created successfully.');
            header("Location: /invoices/view/" . $invoiceId);
            exit();
        } else {
            $this->session->flash('error', 'Failed to create invoice.');
            header("Location: /patients/{$patient_id}/invoices/create");
            exit();
        }
    }
}
