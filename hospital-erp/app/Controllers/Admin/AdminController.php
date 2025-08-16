<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Class AdminController
 *
 * Serves as the main dashboard for the Admin module.
 */
class AdminController extends BaseController
{
    /**
     * Displays the main admin dashboard.
     */
    public function index()
    {
        $this->render('admin.dashboard', [
            'title' => 'Admin Dashboard'
        ]);
    }
}
