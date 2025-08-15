<?php

namespace App\Models\Patient;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Insurance
 *
 * Handles database operations for the `insurance_details` table.
 */
class Insurance
{
    private PDO $db;

    /**
     * Insurance constructor.
     * Gets the database connection instance.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all insurance policies for a specific patient.
     *
     * @param int $patientId The ID of the patient.
     * @return array A list of insurance policies.
     */
    public function findByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM insurance_details WHERE patient_id = :patient_id ORDER BY is_primary DESC");
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new insurance detail record for a patient.
     *
     * @param array $data An associative array of insurance data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO insurance_details (patient_id, provider_name, policy_number, group_number, subscriber_name, is_primary)
                VALUES (:patient_id, :provider_name, :policy_number, :group_number, :subscriber_name, :is_primary)";

        try {
            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                'patient_id' => $data['patient_id'],
                'provider_name' => $data['provider_name'],
                'policy_number' => $data['policy_number'],
                'group_number' => $data['group_number'] ?? null,
                'subscriber_name' => $data['subscriber_name'],
                'is_primary' => $data['is_primary'] ?? true,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating insurance detail: " . $e->getMessage());
            return false;
        }
    }
}
