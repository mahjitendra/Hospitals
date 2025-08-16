<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;

/**
 * Class ReportController
 *
 * Serves as the main dashboard for the Reports module.
 */
class ReportController extends BaseController
{
    /**
     * Displays the main reports dashboard.
     */
    public function index()
    {
        $this->render('reports.dashboard', [
            'title' => 'Reports & Analytics'
        ]);
    }
}
