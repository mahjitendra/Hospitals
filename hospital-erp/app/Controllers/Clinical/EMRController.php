<?php

namespace App\Controllers\Clinical;

use App\Controllers\BaseController;

/**
 * Class EMRController
 *
 * Serves as the main dashboard for the Electronic Medical Records (EMR) section.
 */
class EMRController extends BaseController
{
    /**
     * Displays the main EMR dashboard.
     */
    public function index()
    {
        // This dashboard would typically show a list of recent patients,
        // appointments, or other clinical summaries.
        $this->render('clinical.emr.dashboard', [
            'title' => 'EMR Dashboard'
        ]);
    }
}
