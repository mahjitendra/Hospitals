<?php

namespace App\Models\Clinical;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class VitalSign
 *
 * Handles database operations for the `vital_signs` table.
 */
class VitalSign
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all vital signs for a specific patient.
     *
     * @param int $patientId The ID of the patient.
     * @return array A list of vital signs.
     */
    public function findByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM vital_signs WHERE patient_id = :patient_id ORDER BY record_date DESC");
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new vital sign record.
     *
     * @param array $data An associative array of vital sign data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO vital_signs (patient_id, record_date, temperature, heart_rate, blood_pressure, respiratory_rate, oxygen_saturation)
                VALUES (:patient_id, :record_date, :temperature, :heart_rate, :blood_pressure, :respiratory_rate, :oxygen_saturation)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'patient_id' => $data['patient_id'],
                'record_date' => $data['record_date'],
                'temperature' => $data['temperature'] ?? null,
                'heart_rate' => $data['heart_rate'] ?? null,
                'blood_pressure' => $data['blood_pressure'] ?? null,
                'respiratory_rate' => $data['respiratory_rate'] ?? null,
                'oxygen_saturation' => $data['oxygen_saturation'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating vital sign record: " . $e->getMessage());
            return false;
        }
    }
}
