<?php

namespace App\Controllers\HumanResource;

use App\Controllers\BaseController;
use App\Models\HumanResource\Schedule as ScheduleModel;
use App\Models\HumanResource\Staff as StaffModel;
use App\Libraries\Core\Session;

/**
 * Class ScheduleController
 *
 * Handles management of staff schedules.
 */
class ScheduleController extends BaseController
{
    private ScheduleModel $scheduleModel;
    private StaffModel $staffModel;
    private Session $session;

    public function __construct()
    {
        $this->scheduleModel = new ScheduleModel();
        $this->staffModel = new StaffModel();
        $this->session = new Session();
    }

    /**
     * Shows all schedules for a specific staff member.
     * @param int $staff_id
     */
    public function listByStaff(int $staff_id)
    {
        $staffMember = $this->staffModel->findById($staff_id);
        if (!$staffMember) { echo "Staff member not found"; exit; }

        $schedules = $this->scheduleModel->findByStaffId($staff_id);

        $this->render('hr.schedule.list', [
            'title' => 'Schedules for ' . htmlspecialchars($staffMember['first_name']),
            'staffMember' => $staffMember,
            'schedules' => $schedules
        ]);
    }

    /**
     * Shows the form to create a new schedule.
     * @param int $staff_id
     */
    public function showCreateForm(int $staff_id)
    {
        $staffMember = $this->staffModel->findById($staff_id);
        if (!$staffMember) { echo "Staff member not found"; exit; }

        $this->render('hr.schedule.create', [
            'title' => 'Add Schedule for ' . htmlspecialchars($staffMember['first_name']),
            'staffMember' => $staffMember
        ]);
    }

    /**
     * Processes the creation of a new schedule.
     * @param int $staff_id
     */
    public function create(int $staff_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /hr/staff/{$staff_id}/schedules");
            exit;
        }

        $data = [
            'staff_id' => $staff_id,
            'shift_start' => $_POST['shift_start'],
            'shift_end' => $_POST['shift_end'],
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        if (empty($data['shift_start']) || empty($data['shift_end'])) {
            $this->session->flash('error', 'Shift start and end times are required.');
            header("Location: /hr/staff/{$staff_id}/schedules/create");
            exit;
        }

        if ($this->scheduleModel->create($data)) {
            $this->session->flash('success', 'Schedule added successfully.');
            header("Location: /hr/staff/view/{$staff_id}");
            exit;
        } else {
            $this->session->flash('error', 'Failed to add schedule.');
            header("Location: /hr/staff/{$staff_id}/schedules/create");
            exit;
        }
    }
}
