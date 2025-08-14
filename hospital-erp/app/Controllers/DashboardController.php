<?php

namespace App\Controllers;

/**
 * Class DashboardController
 *
 * Handles the main dashboard page of the application.
 */
class DashboardController extends BaseController
{
    /**
     * Shows the main dashboard page.
     *
     * This method prepares the data for the dashboard and renders the view.
     */
    public function index()
    {
        // Data to be passed to the view
        $data = [
            'title' => 'Hospital ERP Dashboard',
            'pageHeading' => 'Welcome to the Dashboard'
        ];

        // Render the dashboard view using the 'render' method from BaseController
        // The view is located at /app/Views/dashboard/index.php
        $this->render('dashboard.index', $data);
    }
}
