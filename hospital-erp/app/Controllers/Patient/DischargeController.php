<?php

namespace App\Controllers\Patient;

use App\Controllers\BaseController;
use App\Models\Patient\Patient as PatientModel;
use App\Libraries\Core\Session;

/**
 * Class DischargeController
 *
 * Handles the patient discharge process.
 */
class DischargeController extends BaseController
{
    private PatientModel $patientModel;
    private Session $session;

    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->session = new Session();
    }

    /**
     * Shows the form to confirm patient discharge.
     *
     * @param int $patient_id
     */
    public function showDischargeForm(int $patient_id)
    {
        $patient = $this->patientModel->findById($patient_id);
        if (!$patient) {
            echo "Patient not found";
            exit;
        }

        $this->render('patient.discharge', [
            'title' => 'Discharge Patient: ' . htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']),
            'patient' => $patient
        ]);
    }

    /**
     * Processes the patient discharge.
     *
     * @param int $patient_id
     */
    public function discharge(int $patient_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /patients/view/{$patient_id}");
            exit;
        }

        // Add discharge notes to medical records if needed (future enhancement)
        // $discharge_notes = $_POST['discharge_notes'];

        if ($this->patientModel->updateStatus($patient_id, 'Discharged')) {
            $this->session->flash('success', 'Patient has been successfully discharged.');
            header("Location: /patients/view/{$patient_id}");
            exit;
        } else {
            $this->session->flash('error', 'Failed to discharge patient.');
            header("Location: /patients/view/{$patient_id}");
            exit;
        }
    }
}
