<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\Reports\Report as ReportModel;

/**
 * Class AnalyticsController
 *
 * Handles the generation and display of specific analytical reports.
 */
class AnalyticsController extends BaseController
{
    private ReportModel $reportModel;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
    }

    /**
     * Displays the patient demographics report.
     */
    public function viewPatientDemographics()
    {
        $data = $this->reportModel->getPatientDemographicsReport();
        $this->render('reports.analytics.patient_demographics', [
            'title' => 'Patient Demographics Report',
            'data' => $data
        ]);
    }

    /**
     * Displays the financial summary report.
     */
    public function viewFinancialSummary()
    {
        $data = $this->reportModel->getFinancialSummaryReport();
        $this->render('reports.analytics.financial_summary', [
            'title' => 'Financial Summary Report',
            'data' => $data
        ]);
    }

    /**
     * Displays the inventory status report.
     */
    public function viewInventoryStatus()
    {
        $data = $this->reportModel->getInventoryStatusReport();
        $this->render('reports.analytics.inventory_status', [
            'title' => 'Inventory Status Report',
            'data' => $data
        ]);
    }
}
