<?php

namespace App\Models\Clinical;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Allergy
 *
 * Handles database operations for the `allergies` table.
 */
class Allergy
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all allergies for a specific patient.
     *
     * @param int $patientId The ID of the patient.
     * @return array A list of allergies.
     */
    public function findByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM allergies WHERE patient_id = :patient_id ORDER BY substance");
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new allergy record.
     *
     * @param array $data An associative array of allergy data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO allergies (patient_id, substance, reaction, severity)
                VALUES (:patient_id, :substance, :reaction, :severity)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'patient_id' => $data['patient_id'],
                'substance' => $data['substance'],
                'reaction' => $data['reaction'] ?? null,
                'severity' => $data['severity'],
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating allergy: " . $e->getMessage());
            return false;
        }
    }
}
