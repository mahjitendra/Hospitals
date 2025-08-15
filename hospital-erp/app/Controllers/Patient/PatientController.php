<?php

namespace App\Controllers\Patient;

use App\Controllers\BaseController;
use App\Models\Patient\Patient as PatientModel;
use App\Models\Patient\MedicalRecord as MedicalRecordModel;
use App\Models\Patient\Insurance as InsuranceModel;

/**
 * Class PatientController
 *
 * Handles the main patient listing and viewing functionalities.
 */
class PatientController extends BaseController
{
    private PatientModel $patientModel;
    private MedicalRecordModel $medicalRecordModel;
    private InsuranceModel $insuranceModel;

    /**
     * PatientController constructor.
     */
    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->medicalRecordModel = new MedicalRecordModel();
        $this->insuranceModel = new InsuranceModel();
    }

    /**
     * Displays a list of all patients.
     */
    public function index()
    {
        $patients = $this->patientModel->findAll();

        $this->render('patient.index', [
            'title' => 'All Patients',
            'patients' => $patients
        ]);
    }

    /**
     * Displays the profile for a single patient.
     *
     * @param int $id The ID of the patient to view.
     */
    public function view(int $id)
    {
        $patient = $this->patientModel->findById($id);

        if (!$patient) {
            // TODO: Create a proper 404 view for this
            echo "Patient not found.";
            return;
        }

        $medicalRecords = $this->medicalRecordModel->findByPatientId($id);
        $insuranceDetails = $this->insuranceModel->findByPatientId($id);

        $this->render('patient.view', [
            'title' => 'Patient Profile: ' . htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']),
            'patient' => $patient,
            'medicalRecords' => $medicalRecords,
            'insuranceDetails' => $insuranceDetails
        ]);
    }
}
