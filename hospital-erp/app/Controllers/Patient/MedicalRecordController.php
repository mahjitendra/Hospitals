<?php

namespace App\Controllers\Patient;

use App\Controllers\BaseController;
use App\Models\Patient\MedicalRecord as MedicalRecordModel;
use App\Models\Patient\Patient as PatientModel;
use App\Libraries\Core\Session;

/**
 * Class MedicalRecordController
 *
 * Handles creation and management of patient medical records.
 */
class MedicalRecordController extends BaseController
{
    private MedicalRecordModel $recordModel;
    private PatientModel $patientModel;
    private Session $session;

    public function __construct()
    {
        $this->recordModel = new MedicalRecordModel();
        $this->patientModel = new PatientModel();
        $this->session = new Session();
    }

    /**
     * Shows the form to create a new medical record for a patient.
     *
     * @param int $patient_id
     */
    public function showCreateForm(int $patient_id)
    {
        $patient = $this->patientModel->findById($patient_id);
        if (!$patient) {
            // A proper 404 handler would be better.
            echo "Patient not found";
            exit;
        }

        $this->render('patient.medical_record.create', [
            'title' => 'Add Medical Record for ' . htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']),
            'patient' => $patient
        ]);
    }

    /**
     * Processes the creation of a new medical record.
     *
     * @param int $patient_id
     */
    public function create(int $patient_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /patients/view/{$patient_id}");
            exit();
        }

        $patient = $this->patientModel->findById($patient_id);
        if (!$patient) {
            echo "Patient not found";
            exit;
        }

        $data = [
            'patient_id' => $patient_id,
            'record_date' => trim($_POST['record_date'] ?? ''),
            'doctor_name' => trim($_POST['doctor_name'] ?? ''),
            'diagnosis' => trim($_POST['diagnosis'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        // Simple validation
        if (empty($data['record_date']) || empty($data['doctor_name'])) {
            $this->render('patient.medical_record.create', [
                'title' => 'Add Medical Record',
                'patient' => $patient,
                'error' => 'Record Date and Doctor Name are required fields.',
                'old' => $data
            ]);
            return;
        }

        if ($this->recordModel->create($data)) {
            $this->session->flash('success', 'Medical record added successfully.');
            header("Location: /patients/view/{$patient_id}");
            exit();
        } else {
            $this->render('patient.medical_record.create', [
                'title' => 'Add Medical Record',
                'patient' => $patient,
                'error' => 'Failed to add medical record. Please try again.',
                'old' => $data
            ]);
        }
    }
}
