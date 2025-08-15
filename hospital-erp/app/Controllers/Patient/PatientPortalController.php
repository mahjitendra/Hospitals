<?php

namespace App\Controllers\Patient;

use App\Controllers\BaseController;
use App\Models\Patient\Patient as PatientModel;
use App\Libraries\Core\Session;

/**
 * Class PatientPortalController
 *
 * Handles the patient-facing portal.
 */
class PatientPortalController extends BaseController
{
    private PatientModel $patientModel;
    private Session $session;

    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->session = new Session();
    }

    /**
     * Shows the dashboard for the logged-in patient.
     */
    public function index()
    {
        if (!$this->session->has('user')) {
            $this->session->flash('error', 'You must be logged in to access the patient portal.');
            header('Location: /login');
            exit;
        }

        $userId = $this->session->get('user')['id'];
        $patient = $this->patientModel->findByUserId($userId);

        if (!$patient) {
            // This user is not a patient or has no linked profile.
            // For now, show an error. A better implementation would redirect to a specific error page.
            echo "No patient profile is linked to this user account. Please contact support.";
            exit;
        }

        $this->render('patient.portal.dashboard', [
            'title' => 'My Patient Portal',
            'patient' => $patient
        ], 'portal');
    }
}
