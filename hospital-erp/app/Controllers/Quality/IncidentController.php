<?php

namespace App\Controllers\Quality;

use App\Controllers\BaseController;
use App\Models\Quality\Incident as IncidentModel;
use App\Libraries\Core\Session;

/**
 * Class IncidentController
 *
 * Handles the reporting and management of quality incidents.
 */
class IncidentController extends BaseController
{
    private IncidentModel $incidentModel;
    private Session $session;

    public function __construct()
    {
        $this->incidentModel = new IncidentModel();
        $this->session = new Session();
    }

    /**
     * Displays a list of all quality incidents.
     */
    public function index()
    {
        $incidents = $this->incidentModel->findAll();
        $this->render('quality.incident.index', [
            'title' => 'Quality Incidents',
            'incidents' => $incidents
        ]);
    }

    /**
     * Shows the form to report a new incident.
     */
    public function showCreateForm()
    {
        $this->render('quality.incident.create', [
            'title' => 'Report New Quality Incident'
        ]);
    }

    /**
     * Processes the creation of a new incident report.
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /quality/incidents/create');
            exit;
        }

        $data = [
            'incident_date' => $_POST['incident_date'],
            'description' => trim($_POST['description']),
            'department' => trim($_POST['department']),
            'reported_by' => $this->session->get('user')['id'], // Assuming staff user ID
            'severity' => $_POST['severity'],
        ];

        if (empty($data['incident_date']) || empty($data['description']) || empty($data['severity'])) {
            $this->session->flash('error', 'Date, Description, and Severity are required.');
            header('Location: /quality/incidents/create');
            exit;
        }

        if ($this->incidentModel->create($data)) {
            $this->session->flash('success', 'Incident reported successfully.');
            header('Location: /quality/incidents');
            exit;
        } else {
            $this->session->flash('error', 'Failed to report incident.');
            header('Location: /quality/incidents/create');
            exit;
        }
    }
}
