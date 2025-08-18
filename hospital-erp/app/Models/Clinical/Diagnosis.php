<?php

namespace App\Models\Clinical;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Diagnosis
 *
 * Handles database operations for the `diagnoses` table.
 */
class Diagnosis
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all diagnoses for a specific patient.
     *
     * @param int $patientId The ID of the patient.
     * @return array A list of diagnoses.
     */
    public function findByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM diagnoses WHERE patient_id = :patient_id ORDER BY diagnosis_date DESC");
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new diagnosis.
     *
     * @param array $data An associative array of diagnosis data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO diagnoses (patient_id, doctor_id, diagnosis_date, icd10_code, description, is_active)
                VALUES (:patient_id, :doctor_id, :diagnosis_date, :icd10_code, :description, :is_active)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'diagnosis_date' => $data['diagnosis_date'],
                'icd10_code' => $data['icd10_code'] ?? null,
                'description' => $data['description'],
                'is_active' => $data['is_active'] ?? true,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating diagnosis: " . $e->getMessage());
            return false;
        }
    }
}
