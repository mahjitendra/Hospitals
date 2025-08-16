<?php

namespace App\Models\Quality;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Incident
 *
 * Handles database operations for the `quality_incidents` table.
 */
class Incident
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all quality incidents from the database.
     *
     * @return array A list of all incidents.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT qi.*, s.first_name, s.last_name
                                 FROM quality_incidents qi
                                 LEFT JOIN staff s ON qi.reported_by = s.id
                                 ORDER BY qi.incident_date DESC");
        return $stmt->fetchAll();
    }

    /**
     * Creates a new quality incident.
     *
     * @param array $data An associative array of incident data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO quality_incidents (incident_date, description, department, reported_by, severity, status)
                VALUES (:incident_date, :description, :department, :reported_by, :severity, :status)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'incident_date' => $data['incident_date'],
                'description' => $data['description'],
                'department' => $data['department'] ?? null,
                'reported_by' => $data['reported_by'] ?? null,
                'severity' => $data['severity'],
                'status' => $data['status'] ?? 'Open',
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating quality incident: " . $e->getMessage());
            return false;
        }
    }
}
