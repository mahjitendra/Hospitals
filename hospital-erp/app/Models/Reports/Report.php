<?php

namespace App\Models\Reports;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Report
 *
 * A service-like model to generate various reports by querying the database.
 */
class Report
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generates a report on patient demographics.
     *
     * @return array The report data.
     */
    public function getPatientDemographicsReport(): array
    {
        $stmt = $this->db->query("SELECT gender, COUNT(*) as count FROM patients GROUP BY gender");
        return $stmt->fetchAll();
    }

    /**
     * Generates a financial summary report based on invoice status.
     *
     * @return array The report data.
     */
    public function getFinancialSummaryReport(): array
    {
        $stmt = $this->db->query("SELECT status, SUM(total_amount) as total, COUNT(*) as count FROM invoices GROUP BY status");
        return $stmt->fetchAll();
    }

    /**
     * Generates a report on the status of inventory items.
     *
     * @return array The report data.
     */
    public function getInventoryStatusReport(): array
    {
        $stmt = $this->db->query("SELECT category, COUNT(*) as item_count, SUM(quantity) as total_quantity FROM inventory_items GROUP BY category");
        return $stmt->fetchAll();
    }
}
