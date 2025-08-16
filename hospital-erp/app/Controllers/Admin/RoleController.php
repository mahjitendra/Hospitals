<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\Role as RoleModel;

/**
 * Class RoleController
 *
 * Handles the management of roles in the admin section.
 */
class RoleController extends BaseController
{
    private RoleModel $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }

    /**
     * Displays a list of all roles.
     */
    public function index()
    {
        $roles = $this->roleModel->findAll();
        $this->render('admin.role.index', [
            'title' => 'Role Management',
            'roles' => $roles
        ]);
    }
}
