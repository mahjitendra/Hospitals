<?php

namespace App\Controllers\Quality;

use App\Controllers\BaseController;

/**
 * Class QualityController
 *
 * Serves as the main dashboard for the Quality Management module.
 */
class QualityController extends BaseController
{
    /**
     * Displays the main quality dashboard.
     */
    public function index()
    {
        // This dashboard would typically show key quality metrics,
        // open incidents, and pending risk assessments.
        $this->render('quality.dashboard', [
            'title' => 'Quality Management Dashboard'
        ]);
    }
}
