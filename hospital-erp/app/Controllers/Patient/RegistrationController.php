<?php

namespace App\Controllers\Patient;

use App\Controllers\BaseController;
use App\Models\Patient\Patient as PatientModel;
use App\Libraries\Core\Session;

/**
 * Class RegistrationController
 *
 * Handles the registration of new patients.
 */
class RegistrationController extends BaseController
{
    private PatientModel $patientModel;
    private Session $session;

    /**
     * RegistrationController constructor.
     */
    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->session = new Session();
    }

    /**
     * Displays the new patient registration form.
     */
    public function showForm()
    {
        $this->render('patient.register', [
            'title' => 'Register New Patient'
        ]);
    }

    /**
     * Handles the submission of the new patient form.
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /patients/register');
            exit();
        }

        // --- Data Sanitization & Collection ---
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
            'gender' => trim($_POST['gender'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
            'zip_code' => trim($_POST['zip_code'] ?? ''),
            'phone_number' => trim($_POST['phone_number'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'emergency_contact_name' => trim($_POST['emergency_contact_name'] ?? ''),
            'emergency_contact_phone' => trim($_POST['emergency_contact_phone'] ?? ''),
            'blood_type' => trim($_POST['blood_type'] ?? ''),
        ];

        // --- Basic Validation ---
        // A more robust validation system should be implemented later.
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['date_of_birth']) || empty($data['gender'])) {
            $this->render('patient.register', [
                'title' => 'Register New Patient',
                'error' => 'First Name, Last Name, Date of Birth, and Gender are required.',
                'old' => $data
            ]);
            return;
        }

        // --- Create Patient ---
        $success = $this->patientModel->create($data);

        if ($success) {
            $this->session->flash('success', 'Patient registered successfully!');
            // Ideally, redirect to the new patient's profile page. For now, redirect to dashboard.
            header('Location: /');
            exit();
        } else {
            $this->render('patient.register', [
                'title' => 'Register New Patient',
                'error' => 'Failed to register patient. Please check the data and try again.',
                'old' => $data
            ]);
        }
    }
}
