<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\User as UserModel;

/**
 * Class UserController
 *
 * Handles the management of users in the admin section.
 */
class UserController extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Displays a list of all users.
     */
    public function index()
    {
        $users = $this->userModel->findAll();
        $this->render('admin.user.index', [
            'title' => 'User Management',
            'users' => $users
        ]);
    }
}
