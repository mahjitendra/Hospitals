<?php

namespace App\Controllers\Laboratory;

use App\Controllers\BaseController;

/**
 * Class LaboratoryController
 *
 * Serves as the main dashboard and entry point for the Laboratory module.
 */
class LaboratoryController extends BaseController
{
    /**
     * Displays the main laboratory dashboard.
     */
    public function index()
    {
        // In a real application, you would fetch data for the dashboard here,
        // such as pending orders, recent results, equipment status, etc.

        $this->render('laboratory.dashboard', [
            'title' => 'Laboratory Dashboard'
        ]);
    }
}
