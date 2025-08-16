<?php

namespace App\Controllers\HumanResource;

use App\Controllers\BaseController;
use App\Models\HumanResource\Staff as StaffModel;
use App\Models\User as UserModel;
use App\Libraries\Core\Session;

/**
 * Class StaffController
 *
 * Handles management of staff members.
 */
class StaffController extends BaseController
{
    private StaffModel $staffModel;
    private UserModel $userModel;
    private Session $session;

    public function __construct()
    {
        $this->staffModel = new StaffModel();
        $this->userModel = new UserModel();
        $this->session = new Session();
    }

    /**
     * Displays a list of all staff members.
     */
    public function index()
    {
        $staff = $this->staffModel->findAll();
        $this->render('hr.staff.index', [
            'title' => 'Staff Directory',
            'staff' => $staff
        ]);
    }

    /**
     * Displays the profile for a single staff member.
     * @param int $id
     */
    public function view(int $id)
    {
        $staffMember = $this->staffModel->findById($id);
        if (!$staffMember) { echo "Staff member not found"; exit; }

        $this->render('hr.staff.view', [
            'title' => 'Staff Profile: ' . htmlspecialchars($staffMember['first_name'] . ' ' . $staffMember['last_name']),
            'staffMember' => $staffMember
            // In the future, we would also load schedules, payroll history, etc.
        ]);
    }

    /**
     * Shows the form to add a new staff member.
     */
    public function showCreateForm()
    {
        $this->render('hr.staff.create', [
            'title' => 'Add New Staff Member'
        ]);
    }

    /**
     * Processes the creation of a new staff member.
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /hr/staff/create');
            exit;
        }

        // This is a two-step process: create a user for login, then create the staff profile.
        // In a real app, this should be a transaction.

        // 1. Create User
        $userData = [
            'name' => trim($_POST['first_name']) . ' ' . trim($_POST['last_name']),
            'email' => trim($_POST['email']),
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
        ];

        if ($this->userModel->findByEmail($userData['email'])) {
            $this->session->flash('error', 'A user with this email already exists.');
            header('Location: /hr/staff/create');
            exit;
        }

        $this->userModel->create($userData);
        $user = $this->userModel->findByEmail($userData['email']);

        // 2. Create Staff Profile
        $staffData = [
            'user_id' => $user['id'],
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'job_title' => trim($_POST['job_title']),
            'department' => trim($_POST['department']),
            'hire_date' => trim($_POST['hire_date']),
            'phone_number' => trim($_POST['phone_number']),
            'email' => trim($_POST['email']),
        ];

        if ($this->staffModel->create($staffData)) {
            $this->session->flash('success', 'Staff member created successfully.');
            header('Location: /hr/staff');
            exit;
        } else {
            // Here you might want to delete the created user to avoid orphaned users.
            $this->session->flash('error', 'Failed to create staff profile.');
            header('Location: /hr/staff/create');
            exit;
        }
    }
}
