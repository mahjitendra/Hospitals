<?php

namespace App\Models\Quality;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class RiskAssessment
 *
 * Handles database operations for the `risk_assessments` table.
 */
class RiskAssessment
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all risk assessments from the database.
     *
     * @return array A list of all risk assessments.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM risk_assessments ORDER BY assessment_date DESC");
        return $stmt->fetchAll();
    }

    /**
     * Creates a new risk assessment.
     *
     * @param array $data An associative array of risk assessment data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO risk_assessments (assessment_date, area_assessed, risk_description, likelihood, impact, mitigation_plan, status)
                VALUES (:assessment_date, :area_assessed, :risk_description, :likelihood, :impact, :mitigation_plan, :status)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'assessment_date' => $data['assessment_date'],
                'area_assessed' => $data['area_assessed'],
                'risk_description' => $data['risk_description'],
                'likelihood' => $data['likelihood'],
                'impact' => $data['impact'],
                'mitigation_plan' => $data['mitigation_plan'] ?? null,
                'status' => $data['status'] ?? 'Open',
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating risk assessment: " . $e->getMessage());
            return false;
        }
    }
}
