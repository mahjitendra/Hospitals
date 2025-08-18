<?php

namespace App\Controllers\Clinical;

use App\Controllers\BaseController;
use App\Models\Clinical\ClinicalNote as ClinicalNoteModel;
use App\Models\Patient\Patient as PatientModel;
use App\Libraries\Core\Session;

/**
 * Class ClinicalNotesController
 *
 * Handles the creation and management of clinical notes.
 */
class ClinicalNotesController extends BaseController
{
    private ClinicalNoteModel $noteModel;
    private PatientModel $patientModel;
    private Session $session;

    public function __construct()
    {
        $this->noteModel = new ClinicalNoteModel();
        $this->patientModel = new PatientModel();
        $this->session = new Session();
    }

    /**
     * Shows the form to create a new clinical note for a patient.
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

        $this->render('clinical.note.create', [
            'title' => 'Add Clinical Note for ' . htmlspecialchars($patient['first_name']),
            'patient' => $patient
        ]);
    }

    /**
     * Processes the creation of a new clinical note.
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
            'note_date' => date('Y-m-d H:i:s'),
            'note_type' => $_POST['note_type'],
            'note' => trim($_POST['note']),
        ];

        if (empty($data['note_type']) || empty($data['note'])) {
            $this->session->flash('error', 'Note Type and Note are required.');
            header("Location: /patients/{$patient_id}/notes/create");
            exit;
        }

        if ($this->noteModel->create($data)) {
            $this->session->flash('success', 'Clinical note added successfully.');
            header("Location: /patients/view/{$patient_id}");
            exit;
        } else {
            $this->session->flash('error', 'Failed to add clinical note.');
            header("Location: /patients/{$patient_id}/notes/create");
            exit;
        }
    }
}
