<?php

namespace App\Controllers\Patient;

use App\Controllers\BaseController;
use App\Models\Patient\Insurance as InsuranceModel;
use App\Models\Patient\Patient as PatientModel;
use App\Libraries\Core\Session;

/**
 * Class InsuranceController
 *
 * Handles management of patient insurance details.
 */
class InsuranceController extends BaseController
{
    private InsuranceModel $insuranceModel;
    private PatientModel $patientModel;
    private Session $session;

    public function __construct()
    {
        $this->insuranceModel = new InsuranceModel();
        $this->patientModel = new PatientModel();
        $this->session = new Session();
    }

    /**
     * Shows the form to add new insurance details for a patient.
     *
     * @param int $patient_id
     */
    public function showCreateForm(int $patient_id)
    {
        $patient = $this->patientModel->findById($patient_id);
        if (!$patient) {
            echo "Patient not found";
            exit;
        }

        $this->render('patient.insurance.create', [
            'title' => 'Add Insurance for ' . htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']),
            'patient' => $patient
        ]);
    }

    /**
     * Processes the creation of new insurance details.
     *
     * @param int $patient_id
     */
    public function create(int $patient_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /patients/view/{$patient_id}");
            exit;
        }

        $patient = $this->patientModel->findById($patient_id);
        if (!$patient) {
            echo "Patient not found";
            exit;
        }

        $data = [
            'patient_id' => $patient_id,
            'provider_name' => trim($_POST['provider_name'] ?? ''),
            'policy_number' => trim($_POST['policy_number'] ?? ''),
            'group_number' => trim($_POST['group_number'] ?? null),
            'subscriber_name' => trim($_POST['subscriber_name'] ?? ''),
            'is_primary' => isset($_POST['is_primary']) ? 1 : 0,
        ];

        if (empty($data['provider_name']) || empty($data['policy_number'])) {
            $this->render('patient.insurance.create', [
                'title' => 'Add Insurance',
                'patient' => $patient,
                'error' => 'Provider Name and Policy Number are required.',
                'old' => $data
            ]);
            return;
        }

        if ($this->insuranceModel->create($data)) {
            $this->session->flash('success', 'Insurance details added successfully.');
            header("Location: /patients/view/{$patient_id}");
            exit;
        } else {
            $this->render('patient.insurance.create', [
                'title' => 'Add Insurance',
                'patient' => $patient,
                'error' => 'Failed to add insurance details. Please try again.',
                'old' => $data
            ]);
        }
    }
}
