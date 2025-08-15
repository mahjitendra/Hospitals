<?php

namespace App\Controllers\Laboratory;

use App\Controllers\BaseController;
use App\Models\Laboratory\LabTest as LabTestModel;
use App\Models\Laboratory\LabOrder as LabOrderModel;
use App\Models\Patient\Patient as PatientModel;
use App\Libraries\Core\Session;

/**
 * Class TestOrderController
 *
 * Handles the creation and management of lab test orders.
 */
class TestOrderController extends BaseController
{
    private LabTestModel $labTestModel;
    private LabOrderModel $labOrderModel;
    private PatientModel $patientModel;
    private Session $session;

    public function __construct()
    {
        $this->labTestModel = new LabTestModel();
        $this->labOrderModel = new LabOrderModel();
        $this->patientModel = new PatientModel();
        $this->session = new Session();
    }

    /**
     * Shows the form to order a new lab test for a specific patient.
     *
     * @param int $patient_id
     */
    public function showOrderForm(int $patient_id)
    {
        $patient = $this->patientModel->findById($patient_id);
        if (!$patient) {
            echo "Patient not found";
            exit;
        }

        $availableTests = $this->labTestModel->findAll();

        $this->render('laboratory.order.create', [
            'title' => 'Order Lab Test for ' . htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']),
            'patient' => $patient,
            'availableTests' => $availableTests
        ]);
    }

    /**
     * Processes the creation of a new lab order.
     *
     * @param int $patient_id
     */
    public function createOrder(int $patient_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /patients/view/{$patient_id}");
            exit;
        }

        $data = [
            'patient_id' => $patient_id,
            'doctor_id' => $this->session->get('user')['id'], // Assumes the logged-in user is the doctor
            'lab_test_id' => $_POST['lab_test_id'] ?? null,
            'order_date' => date('Y-m-d H:i:s'),
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        if (empty($data['lab_test_id'])) {
            $this->session->flash('error', 'You must select a test to order.');
            header("Location: /patients/{$patient_id}/lab/order");
            exit;
        }

        if ($this->labOrderModel->create($data)) {
            $this->session->flash('success', 'Lab test ordered successfully.');
            // A better redirect would be to a lab order summary page.
            header("Location: /patients/view/{$patient_id}");
            exit;
        } else {
            $this->session->flash('error', 'Failed to order lab test.');
            header("Location: /patients/{$patient_id}/lab/order");
            exit;
        }
    }
}
