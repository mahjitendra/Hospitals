<?php

namespace App\Controllers\Clinical;

use App\Controllers\BaseController;
use App\Models\Clinical\Diagnosis as DiagnosisModel;
use App\Models\Patient\Patient as PatientModel;
use App\Libraries\Core\Session;

/**
 * Class DiagnosisController
 *
 * Handles the creation and management of patient diagnoses.
 */
class DiagnosisController extends BaseController
{
    private DiagnosisModel $diagnosisModel;
    private PatientModel $patientModel;
    private Session $session;

    public function __construct()
    {
        $this->diagnosisModel = new DiagnosisModel();
        $this->patientModel = new PatientModel();
        $this->session = new Session();
    }

    /**
     * Shows the form to create a new diagnosis for a patient.
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

        $this->render('clinical.diagnosis.create', [
            'title' => 'Add Diagnosis for ' . htmlspecialchars($patient['first_name']),
            'patient' => $patient
        ]);
    }

    /**
     * Processes the creation of a new diagnosis.
     *
     * @param int $patient_id
     */
    public function create(int $patient_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /patients/view/{$patient_id}");
            exit;
        }

        $data = [
            'patient_id' => $patient_id,
            'doctor_id' => $this->session->get('user')['id'],
            'diagnosis_date' => date('Y-m-d'),
            'icd10_code' => trim($_POST['icd10_code']),
            'description' => trim($_POST['description']),
            'is_active' => isset($_POST['is_active']),
        ];

        if (empty($data['description'])) {
            $this->session->flash('error', 'Description is required.');
            header("Location: /patients/{$patient_id}/diagnoses/create");
            exit;
        }

        if ($this->diagnosisModel->create($data)) {
            $this->session->flash('success', 'Diagnosis added successfully.');
            header("Location: /patients/view/{$patient_id}");
            exit;
        } else {
            $this->session->flash('error', 'Failed to add diagnosis.');
            header("Location: /patients/{$patient_id}/diagnoses/create");
            exit;
        }
    }
}
